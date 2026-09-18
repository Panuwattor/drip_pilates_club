<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMSMKT — https://developers.smsmkt.com/en/api-reference
 *
 * สำคัญ: OTP ฝั่ง SMSMKT เป็นคนสร้างรหัสและตรวจเอง
 * เราไม่ได้ถือรหัส 6 หลัก มีแค่ token ไว้อ้างอิงตอน validate
 *
 * code '000' = สำเร็จ นอกนั้นคือ error
 */
class SmsmktClient
{
    private const OTP_SEND_URL = 'https://portal-otp.smsmkt.com/api/otp-send';
    private const OTP_VALIDATE_URL = 'https://portal-otp.smsmkt.com/api/otp-validate';
    private const SEND_MESSAGE_URL = 'https://portal-otp.smsmkt.com/api/send-message';

    private const SUCCESS = '000';

    /** ข้อความ error ภาษาไทยตามรหัสที่ SMSMKT ส่งกลับ */
    private const ERRORS = [
        '100' => 'ข้อมูลที่ส่งไปไม่ครบ',
        '107' => 'เบอร์โทรไม่ถูกต้อง',
        '110' => 'เบอร์นี้อยู่ในรายการห้ามส่ง',
        '200' => 'ข้อมูลเข้าสู่ระบบไม่ถูกต้อง',
        '400' => 'เครดิต SMS ไม่พอ กรุณาเติมเครดิต',
        '500' => 'บัญชี SMSMKT หมดอายุ',
        '1004' => 'API key หรือ Secret key ไม่ถูกต้อง',
        '1006' => 'ไม่พบ token กรุณาขอรหัสใหม่',
        '5000' => 'รหัสยืนยันหมดอายุแล้ว กรุณาขอรหัสใหม่',
    ];

    public function isConfigured(): bool
    {
        return filled(config('services.smsmkt.api_key'))
            && filled(config('services.smsmkt.secret_key'))
            && filled(config('services.smsmkt.project_key'));
    }

    /**
     * ขอให้ SMSMKT สร้าง OTP แล้วส่ง SMS ให้ลูกค้า
     * คืน ['ok' => bool, 'token' => ?string, 'error' => ?string]
     *
     * ไม่ยุ่งกับ ref_code เลย — ไม่ส่งไป และ SMSMKT ก็ไม่ได้คืนมา
     * มีแค่ token ตัวเดียวที่ใช้อ้างอิงตอน validate
     */
    public function sendOtp(string $phone): array
    {
        $response = $this->post(self::OTP_SEND_URL, [
            'project_key' => config('services.smsmkt.project_key'),
            'phone' => $phone,
        ]);

        if (! $response['ok']) {
            return $response + ['token' => null];
        }

        return [
            'ok' => true,
            'token' => $response['body']['result']['token'] ?? null,
            'error' => null,
        ];
    }

    /** ให้ SMSMKT ตรวจว่ารหัสที่ลูกค้ากรอกถูกต้องไหม */
    public function validateOtp(string $token, string $otpCode): array
    {
        // ส่งแค่ token กับ otp_code เท่านั้น — ใส่ ref_code เพิ่มจะโดนปฏิเสธ
        $response = $this->post(self::OTP_VALIDATE_URL, [
            'token' => $token,
            'otp_code' => $otpCode,
        ]);

        if (! $response['ok']) {
            return ['ok' => false, 'valid' => false, 'error' => $response['error']];
        }

        // status = true คือรหัสถูก
        $valid = (bool) ($response['body']['result']['status'] ?? false);

        return [
            'ok' => true,
            'valid' => $valid,
            'error' => $valid ? null : 'รหัสยืนยันไม่ถูกต้อง',
        ];
    }

    /** ส่ง SMS ธรรมดา (ไม่ใช่ OTP) เช่นลิงก์ติดตามออเดอร์ */
    public function sendMessage(string $phone, string $message, ?string $campaign = null): array
    {
        return $this->post(self::SEND_MESSAGE_URL, array_filter([
            'message' => $message,
            'phone' => $phone,
            'sender' => config('services.smsmkt.sender'),
            'campaign_name' => $campaign,
            'project_id' => config('services.smsmkt.project_id') ?: null,
        ]));
    }

    private function post(string $url, array $payload): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'error' => 'ยังไม่ได้ตั้งค่า SMSMKT', 'body' => []];
        }

        try {
            $response = Http::withHeaders([
                'api_key' => config('services.smsmkt.api_key'),
                'secret_key' => config('services.smsmkt.secret_key'),
                'Content-Type' => 'application/json',
            ])->timeout(20)->post($url, $payload);
        } catch (\Throwable $e) {
            Log::error('[SMSMKT] ต่อ API ไม่ได้', ['url' => $url, 'message' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'เชื่อมต่อระบบ SMS ไม่ได้ กรุณาลองใหม่', 'body' => []];
        }

        $body = $response->json() ?? [];
        $code = (string) ($body['code'] ?? '');

        if ($code !== self::SUCCESS) {
            // ไม่ log เบอร์เต็มและไม่ log payload กัน key หลุดลง log
            Log::warning('[SMSMKT] เรียก API ไม่สำเร็จ', [
                'url' => $url,
                'code' => $code,
                'detail' => $body['detail'] ?? null,
            ]);

            return [
                'ok' => false,
                'error' => self::ERRORS[$code] ?? ($body['detail'] ?: 'ส่ง SMS ไม่สำเร็จ'),
                'body' => $body,
            ];
        }

        return ['ok' => true, 'error' => null, 'body' => $body];
    }
}
