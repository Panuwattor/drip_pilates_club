<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\LineLoginService;
use App\Services\PhoneVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * ล็อกอิน/สมัคร/ผูกบัญชีด้วย LINE
 *
 * เส้นทางที่เป็นไปได้หลังกดปุ่ม LINE:
 *  1. line_user_id นี้เคยผูกไว้แล้ว        -> ล็อกอินเข้าเลย
 *  2. กำลังล็อกอินอยู่ (กดผูกจากหน้าโปรไฟล์) -> ผูกเข้าบัญชีปัจจุบัน
 *  3. ยังไม่เคยผูก + ไม่ได้ล็อกอิน          -> สร้างบัญชีใหม่ แล้วบังคับกรอกเบอร์
 *  4. กรอกเบอร์แล้วไปตรงกับบัญชีเดิม        -> ต้องยืนยัน OTP ก่อนรวมบัญชี
 */
class LineAuthController extends Controller
{
    private const STATE_KEY = 'line_oauth_state';
    private const NONCE_KEY = 'line_oauth_nonce';
    private const INTENT_KEY = 'line_oauth_intent';
    private const PENDING_KEY = 'line_pending_merge';

    public function __construct(
        private readonly LineLoginService $line,
        private readonly PhoneVerificationService $otp,
    ) {}

    /** เด้งไปหน้าอนุญาตของ LINE */
    public function redirect(Request $request)
    {
        if (! $this->line->isConfigured()) {
            return redirect()->route('customer.login')
                ->with('error', 'ระบบยังไม่ได้เปิดใช้งานการเข้าสู่ระบบด้วย LINE');
        }

        $state = Str::random(40);
        $nonce = Str::random(40);

        $request->session()->put(self::STATE_KEY, $state);
        $request->session()->put(self::NONCE_KEY, $nonce);

        // จำไว้ว่ามาจากหน้าโปรไฟล์ (ผูกบัญชี) หรือหน้าล็อกอิน
        $request->session()->put(
            self::INTENT_KEY,
            Auth::guard('customer')->check() ? 'link' : 'login'
        );

        return redirect()->away($this->line->authorizeUrl($state, $nonce));
    }

    /** LINE ส่งกลับมาที่นี่พร้อม code */
    public function callback(Request $request)
    {
        $intent = $request->session()->pull(self::INTENT_KEY, 'login');
        $expectedState = $request->session()->pull(self::STATE_KEY);
        $nonce = $request->session()->pull(self::NONCE_KEY, '');

        $fallback = $intent === 'link' ? 'customer.profile.edit' : 'customer.login';

        // ผู้ใช้กดยกเลิกที่หน้า LINE
        if ($request->filled('error')) {
            return redirect()->route($fallback)
                ->with('error', 'คุณยกเลิกการเชื่อมต่อกับ LINE');
        }

        // state ไม่ตรง = คำขอไม่ได้มาจากเรา (CSRF)
        if (! $expectedState || $request->input('state') !== $expectedState) {
            return redirect()->route($fallback)
                ->with('error', 'คำขอไม่ถูกต้องหรือหมดอายุ กรุณาลองใหม่อีกครั้ง');
        }

        if (! $request->filled('code')) {
            return redirect()->route($fallback)->with('error', 'ไม่ได้รับข้อมูลจาก LINE');
        }

        try {
            $profile = $this->line->fetchProfile($request->input('code'), $nonce);
        } catch (Throwable $e) {
            Log::error('LINE login failed', ['message' => $e->getMessage()]);

            return redirect()->route($fallback)
                ->with('error', 'เชื่อมต่อกับ LINE ไม่สำเร็จ กรุณาลองใหม่อีกครั้ง');
        }

        return $intent === 'link'
            ? $this->linkToCurrentAccount($request, $profile)
            : $this->loginOrRegister($request, $profile);
    }

    /** กรณีที่ 2 — ล็อกอินอยู่แล้ว กดผูก LINE จากหน้าโปรไฟล์ */
    private function linkToCurrentAccount(Request $request, array $profile)
    {
        $customer = Auth::guard('customer')->user();

        if (! $customer) {
            return redirect()->route('customer.login');
        }

        $owner = Customer::where('line_user_id', $profile['line_user_id'])->first();

        if ($owner && $owner->id !== $customer->id) {
            return redirect()->route('customer.profile.edit')
                ->with('error', 'บัญชี LINE นี้ถูกผูกกับสมาชิกรายอื่นแล้ว');
        }

        $this->attachLine($customer, $profile);

        return redirect()->route('customer.profile.edit')
            ->with('status', 'ผูกบัญชี LINE เรียบร้อยแล้ว ครั้งหน้าเข้าสู่ระบบด้วย LINE ได้เลย');
    }

    /** กรณีที่ 1 และ 3 — มาจากหน้าล็อกอิน */
    private function loginOrRegister(Request $request, array $profile)
    {
        $existing = Customer::where('line_user_id', $profile['line_user_id'])->first();

        // 1. เคยผูกไว้แล้ว เข้าเลย
        if ($existing) {
            if ($existing->status !== 'active') {
                return redirect()->route('customer.login')
                    ->with('error', 'บัญชีนี้ถูกระงับการใช้งาน กรุณาติดต่อเจ้าหน้าที่');
            }

            // ชื่อ/รูปใน LINE อาจเปลี่ยน อัปเดตให้ตรงเสมอ
            $this->attachLine($existing, $profile);
            $this->signIn($request, $existing);

            return $existing->needsProfileCompletion()
                ? redirect()->route('customer.profile.complete')
                : redirect()->intended(route('home'));
        }

        // 3. ยังไม่เคยผูก สร้างบัญชีใหม่ ยังไม่มีเบอร์
        $customer = DB::transaction(function () use ($profile) {
            return Customer::create([
                'code' => $this->nextCode(),
                'first_name' => $profile['display_name'] ?? 'สมาชิกใหม่',
                'email' => $profile['email'] ?? null,
                'line_user_id' => $profile['line_user_id'],
                'line_display_name' => $profile['display_name'] ?? null,
                'line_picture_url' => $profile['picture_url'] ?? null,
                'line_linked_at' => now(),
                'status' => 'active',
                'preferred_locale' => app()->getLocale(),
            ]);
        });

        $this->signIn($request, $customer);

        return redirect()->route('customer.profile.complete')
            ->with('status', 'ยินดีต้อนรับ! กรุณากรอกข้อมูลเพิ่มอีกเล็กน้อยเพื่อเริ่มจองคลาส');
    }

    /** หน้ากรอกเบอร์โทรหลังสมัครด้วย LINE */
    public function showComplete(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        if (! $customer->needsProfileCompletion()) {
            return redirect()->route('home');
        }

        return view('customer.auth.complete-profile', [
            'customer' => $customer,
            'pending' => $request->session()->get(self::PENDING_KEY),
            'branches' => \App\Models\Branch::active()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * รับเบอร์โทร ถ้าเบอร์ไปตรงกับบัญชีเดิมที่แอดมินสร้างไว้
     * จะยังไม่รวมทันที ต้องส่ง OTP ไปยืนยันก่อน
     */
    public function submitPhone(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'home_branch_id' => ['nullable', 'exists:branches,id'],
        ], [], ['phone' => 'เบอร์โทร', 'first_name' => 'ชื่อ']);

        $phone = $this->otp->normalize($data['phone']);

        if (strlen($phone) < 9) {
            throw ValidationException::withMessages(['phone' => 'เบอร์โทรไม่ถูกต้อง']);
        }

        $existing = Customer::where('phone', $phone)
            ->where('id', '!=', $customer->id)
            ->first();

        // 4. เบอร์นี้มีบัญชีอยู่แล้ว ต้องยืนยัน OTP ก่อนรวมบัญชี
        if ($existing) {
            if ($existing->hasLineLinked()) {
                throw ValidationException::withMessages([
                    'phone' => 'เบอร์นี้ถูกใช้กับบัญชีที่ผูก LINE อื่นไว้แล้ว กรุณาติดต่อเจ้าหน้าที่',
                ]);
            }

            $result = $this->otp->send($phone, 'link_line', $customer->line_user_id);

            // ส่ง SMS ไม่ออก (เครดิตหมด/เบอร์ผิด) อย่าเพิ่งพาไปหน้ากรอกรหัส
            if ($result['error']) {
                throw ValidationException::withMessages(['phone' => $result['error']]);
            }

            $request->session()->put(self::PENDING_KEY, [
                'phone' => $phone,
                'target_id' => $existing->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'home_branch_id' => $data['home_branch_id'] ?? null,
            ]);

            return redirect()->route('customer.profile.complete')
                ->with('status', $this->otpSentMessage($result, $phone));
        }

        // เบอร์ว่าง ใช้ได้เลย ไม่ต้อง OTP
        $customer->fill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'phone' => $phone,
            'home_branch_id' => $data['home_branch_id'] ?? null,
            'profile_completed_at' => now(),
        ])->save();

        return redirect()->route('home')
            ->with('status', 'บันทึกข้อมูลเรียบร้อยแล้ว เริ่มจองคลาสได้เลย');
    }

    /** ยืนยัน OTP แล้วรวมบัญชี LINE ใหม่เข้ากับบัญชีเดิมของแอดมิน */
    public function verifyOtp(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $pending = $request->session()->get(self::PENDING_KEY);

        if (! $pending) {
            return redirect()->route('customer.profile.complete')
                ->with('error', 'คำขอหมดอายุ กรุณากรอกเบอร์โทรใหม่อีกครั้ง');
        }

        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $ok = $this->otp->verify(
            $pending['phone'],
            $request->input('code'),
            'link_line',
            $customer->line_user_id,
        );

        if (! $ok) {
            throw ValidationException::withMessages([
                'code' => 'รหัสยืนยันไม่ถูกต้องหรือหมดอายุแล้ว',
            ]);
        }

        $target = Customer::find($pending['target_id']);

        if (! $target || $target->hasLineLinked()) {
            $request->session()->forget(self::PENDING_KEY);

            return redirect()->route('customer.profile.complete')
                ->with('error', 'ไม่พบบัญชีเดิม หรือบัญชีนั้นถูกผูก LINE ไปแล้ว');
        }

        $merged = $this->mergeIntoExisting($customer, $target, $pending);

        $request->session()->forget(self::PENDING_KEY);
        $this->signIn($request, $merged);

        return redirect()->route('home')
            ->with('status', 'ยืนยันเบอร์โทรสำเร็จ เชื่อมบัญชีเดิมของคุณเรียบร้อยแล้ว');
    }

    /** ขอ OTP ใหม่ */
    public function resendOtp(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $pending = $request->session()->get(self::PENDING_KEY);

        if (! $pending) {
            return redirect()->route('customer.profile.complete')
                ->with('error', 'คำขอหมดอายุ กรุณากรอกเบอร์โทรใหม่อีกครั้ง');
        }

        $result = $this->otp->send($pending['phone'], 'link_line', $customer->line_user_id);

        if ($result['error']) {
            return redirect()->route('customer.profile.complete')->with('error', $result['error']);
        }

        return redirect()->route('customer.profile.complete')
            ->with('status', $this->otpSentMessage($result, $pending['phone']));
    }

    /** ยกเลิกการรวมบัญชี กลับไปกรอกเบอร์ใหม่ */
    public function cancelMerge(Request $request)
    {
        $request->session()->forget(self::PENDING_KEY);

        return redirect()->route('customer.profile.complete');
    }

    /** ยกเลิกการผูก LINE จากหน้าโปรไฟล์ */
    public function unlink(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        // ถ้าไม่มีรหัสผ่าน ปลด LINE ออกแล้วจะเข้าระบบไม่ได้อีกเลย
        if (! $customer->hasPassword()) {
            return back()->with('error', 'กรุณาตั้งรหัสผ่านก่อน มิฉะนั้นจะเข้าสู่ระบบไม่ได้อีก');
        }

        $customer->forceFill([
            'line_user_id' => null,
            'line_display_name' => null,
            'line_picture_url' => null,
            'line_linked_at' => null,
        ])->save();

        return back()->with('status', 'ยกเลิกการผูกบัญชี LINE แล้ว');
    }

    // ---------- ตัวช่วย ----------

    /**
     * ย้าย LINE จากบัญชีใหม่ไปใส่บัญชีเดิม แล้วลบบัญชีใหม่ทิ้ง
     * ทำแบบนี้เพราะบัญชีเดิมมีประวัติการจอง/แพ็กเกจอยู่ ต้องรักษาไว้
     */
    private function mergeIntoExisting(Customer $lineAccount, Customer $target, array $pending): Customer
    {
        return DB::transaction(function () use ($lineAccount, $target, $pending) {
            $lineUserId = $lineAccount->line_user_id;
            $displayName = $lineAccount->line_display_name;
            $pictureUrl = $lineAccount->line_picture_url;

            // ต้องเคลียร์ออกจากบัญชีใหม่ก่อน ไม่งั้นชน unique
            $lineAccount->forceFill([
                'line_user_id' => null,
                'line_display_name' => null,
                'line_picture_url' => null,
                'line_linked_at' => null,
            ])->save();

            $target->forceFill([
                'line_user_id' => $lineUserId,
                'line_display_name' => $displayName,
                'line_picture_url' => $pictureUrl,
                'line_linked_at' => now(),
                'phone_verified_at' => now(),
                'profile_completed_at' => now(),
                'home_branch_id' => $target->home_branch_id ?: ($pending['home_branch_id'] ?? null),
            ])->save();

            // บัญชีที่เพิ่งสร้างจาก LINE ยังไม่มีข้อมูลอะไร ลบทิ้งได้ปลอดภัย
            $lineAccount->delete();

            return $target->fresh();
        });
    }

    private function attachLine(Customer $customer, array $profile): void
    {
        $customer->forceFill([
            'line_user_id' => $profile['line_user_id'],
            'line_display_name' => $profile['display_name'] ?? $customer->line_display_name,
            'line_picture_url' => $profile['picture_url'] ?? $customer->line_picture_url,
            'line_linked_at' => $customer->line_linked_at ?? now(),
        ])->save();
    }

    private function signIn(Request $request, Customer $customer): void
    {
        Auth::guard('customer')->login($customer, true);
        $request->session()->regenerate();

        $customer->forceFill(['last_login_at' => now()])->save();
    }

    private function otpSentMessage(array $result, string $phone): string
    {
        if (! $result['sent']) {
            return "กรุณารออีก {$result['wait_seconds']} วินาทีก่อนขอรหัสใหม่";
        }

        $masked = substr($phone, 0, 3) . 'xxx' . substr($phone, -3);
        $message = "ส่งรหัสยืนยันไปที่ {$masked} แล้ว";

        // ตอน dev ที่ยังไม่มี SMS จริง โชว์รหัสให้เลยจะได้ทดสอบได้
        if ($result['debug_code']) {
            $message .= " (โหมดทดสอบ รหัสคือ {$result['debug_code']})";
        }

        return $message;
    }

    private function nextCode(): string
    {
        $last = Customer::withTrashed()->orderByDesc('id')->value('id') ?? 0;

        return 'DP-' . str_pad((string) ($last + 1), 5, '0', STR_PAD_LEFT);
    }
}
