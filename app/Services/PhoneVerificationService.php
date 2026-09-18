<?php

namespace App\Services;

use App\Models\PhoneVerificationCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * OTP ยืนยันเบอร์โทร ใช้ตอนผูก LINE เข้ากับบัญชีเดิมที่แอดมินสร้างไว้
 *
 * มี 2 โหมด:
 *  - smsmkt : SMSMKT สร้างรหัสและตรวจให้เอง เราเก็บแค่ token ไว้อ้างอิง (ใช้จริง)
 *  - log    : สร้างรหัสเองแล้วเขียนลง log ไม่ส่ง SMS จริง (ใช้ตอน dev/ทดสอบ)
 *
 * ทั้งสองโหมดใช้ตาราง phone_verification_codes เหมือนกัน
 * เพื่อคุม cooldown / จำนวนครั้งที่เดา / เก็บประวัติไว้ตรวจสอบ
 */
class PhoneVerificationService
{
    private const CODE_TTL_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;
    private const RESEND_COOLDOWN_SECONDS = 60;

    public function __construct(
        private readonly SmsmktClient $smsmkt,
    ) {}

    /**
     * ออก OTP ใหม่ ถ้าเพิ่งส่งไปไม่ถึง 1 นาทีจะไม่ส่งซ้ำ
     * คืน ['sent' => bool, 'wait_seconds' => int, 'debug_code' => ?string, 'error' => ?string]
     */
    public function send(string $phone, string $purpose = 'link_line', ?string $lineUserId = null): array
    {
        $phone = $this->normalize($phone);

        if ($wait = $this->cooldownRemaining($phone, $purpose)) {
            return $this->result(false, waitSeconds: $wait);
        }

        // ใบเก่าที่ยังไม่ใช้ ยกเลิกทิ้งให้หมด เหลือใบล่าสุดใบเดียว
        PhoneVerificationCode::where('phone', $phone)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->delete();

        return $this->useSmsmkt()
            ? $this->sendViaSmsmkt($phone, $purpose, $lineUserId)
            : $this->sendViaLog($phone, $purpose, $lineUserId);
    }

    /** SMSMKT สร้างรหัสเอง เราเก็บ token ไว้ใช้ตอน validate */
    private function sendViaSmsmkt(string $phone, string $purpose, ?string $lineUserId): array
    {
        $response = $this->smsmkt->sendOtp($phone);

        if (! $response['ok']) {
            return $this->result(false, error: $response['error']);
        }

        PhoneVerificationCode::create([
            'phone' => $phone,
            'code_hash' => null,
            'purpose' => $purpose,
            'provider' => 'smsmkt',
            'provider_token' => $response['token'],
            'line_user_id' => $lineUserId,
            'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
        ]);

        return $this->result(true);
    }

    /** โหมด dev — สร้างรหัสเอง เขียนลง log ไม่เสียเครดิต */
    private function sendViaLog(string $phone, string $purpose, ?string $lineUserId): array
    {
        // กันตั้งค่าผิดบนเซิร์ฟเวอร์จริง เพราะโหมดนี้เขียนรหัส OTP ลง log ตรงๆ
        if (app()->isProduction()) {
            Log::critical('[OTP] ตั้งค่าผิด: production ยังใช้ driver=log อยู่');

            return $this->result(false, error: 'ระบบส่ง SMS ยังไม่พร้อมใช้งาน กรุณาติดต่อเจ้าหน้าที่');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PhoneVerificationCode::create([
            'phone' => $phone,
            'code_hash' => Hash::make($code),
            'purpose' => $purpose,
            'provider' => 'log',
            'line_user_id' => $lineUserId,
            'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
        ]);

        Log::info('[OTP] ส่งรหัสยืนยัน', ['phone' => $phone, 'code' => $code]);

        return $this->result(true, debugCode: $code);
    }

    /**
     * ตรวจ OTP คืน true เมื่อถูกต้องและยังไม่หมดอายุ
     * นับ attempts กัน brute force เดา 6 หลัก
     */
    public function verify(string $phone, string $code, string $purpose = 'link_line', ?string $lineUserId = null): bool
    {
        $phone = $this->normalize($phone);

        $record = PhoneVerificationCode::where('phone', $phone)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $record) {
            return false;
        }

        // OTP ใบนี้ออกให้ LINE คนละคน ไม่ให้ใช้ข้าม
        if ($record->line_user_id && $lineUserId && $record->line_user_id !== $lineUserId) {
            return false;
        }

        if ($record->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        $record->increment('attempts');

        $passed = $record->provider === 'smsmkt'
            ? $this->verifyViaSmsmkt($record, $code)
            : filled($record->code_hash) && Hash::check($code, $record->code_hash);

        if (! $passed) {
            return false;
        }

        $record->update(['verified_at' => now()]);

        return true;
    }

    private function verifyViaSmsmkt(PhoneVerificationCode $record, string $code): bool
    {
        if (blank($record->provider_token)) {
            return false;
        }

        $response = $this->smsmkt->validateOtp($record->provider_token, $code);

        return $response['ok'] && $response['valid'];
    }

    /** เหลืออีกกี่วินาทีถึงจะขอรหัสใหม่ได้ 0 = ขอได้เลย */
    public function cooldownRemaining(string $phone, string $purpose = 'link_line'): int
    {
        $last = PhoneVerificationCode::where('phone', $this->normalize($phone))
            ->where('purpose', $purpose)
            ->latest('id')
            ->first();

        if (! $last) {
            return 0;
        }

        $elapsed = $last->created_at->diffInSeconds(now());

        return (int) max(0, self::RESEND_COOLDOWN_SECONDS - $elapsed);
    }

    /** เก็บเบอร์เป็นตัวเลขล้วน 0812345678 จะได้เทียบตรงกันเสมอ */
    public function normalize(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        // +66812345678 -> 0812345678
        if (Str::startsWith($digits, '66') && strlen($digits) > 9) {
            $digits = '0' . substr($digits, 2);
        }

        return $digits;
    }

    /** ส่ง SMS ธรรมดา (ไม่ใช่ OTP) เช่นแจ้งยืนยันการจอง */
    public function sendMessage(string $phone, string $message, ?string $campaign = null): bool
    {
        if (! $this->useSmsmkt()) {
            Log::info('[SMS] ' . $message, ['phone' => $this->normalize($phone)]);

            return true;
        }

        return $this->smsmkt->sendMessage($this->normalize($phone), $message, $campaign)['ok'];
    }

    private function useSmsmkt(): bool
    {
        return config('services.sms.driver') === 'smsmkt'
            && config('services.smsmkt.enabled')
            && $this->smsmkt->isConfigured();
    }

    private function result(
        bool $sent,
        int $waitSeconds = 0,
        ?string $debugCode = null,
        ?string $error = null,
    ): array {
        return [
            'sent' => $sent,
            'wait_seconds' => $waitSeconds,
            'debug_code' => $debugCode,
            'error' => $error,
        ];
    }
}
