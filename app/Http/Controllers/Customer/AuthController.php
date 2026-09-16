<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('home');
        }

        return view('customer.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required'],
        ], [
            'phone.required' => __t('กรุณากรอกเบอร์โทรศัพท์', 'Please enter your phone number.'),
            'password.required' => __t('กรุณากรอกรหัสผ่าน', 'Please enter your password.'),
        ]);

        $phone = preg_replace('/\D/', '', $data['phone']);

        if (! Auth::guard('customer')->attempt(['phone' => $phone, 'password' => $data['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'phone' => __t('เบอร์โทรหรือรหัสผ่านไม่ถูกต้อง', 'That phone number or password is incorrect.'),
            ]);
        }

        $customer = Auth::guard('customer')->user();

        if ($customer->status !== 'active') {
            Auth::guard('customer')->logout();

            throw ValidationException::withMessages([
                'phone' => __t('บัญชีนี้ถูกระงับการใช้งาน กรุณาติดต่อเจ้าหน้าที่', 'This account has been suspended. Please contact our staff.'),
            ]);
        }

        $request->session()->regenerate();
        $customer->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('home'));
    }

    public function showRegister()
    {
        return view('customer.auth.register', [
            'branches' => Branch::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function register(Request $request)
    {
        // ล้างเบอร์เป็นตัวเลขล้วนก่อนตรวจ ไม่งั้น unique จะเช็คค่าดิบที่ไม่ตรงกับค่าที่บันทึกจริง
        // (เช่น "!!!???" กับ "abc" ต่างกันตอนตรวจ แต่กลายเป็นค่าว่างเหมือนกันตอนบันทึก แล้วชน unique ที่ระดับ DB)
        if ($request->filled('phone')) {
            $request->merge([
                'phone' => app(\App\Services\PhoneVerificationService::class)
                    ->normalize($request->input('phone')),
            ]);
        }

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            // เบอร์ไทยมี 9-10 หลัก ตรวจหลังล้างแล้วเพื่อกันเบอร์ปลอมอย่าง "123"
            'phone' => ['required', 'string', 'digits_between:9,10', 'unique:customers,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'home_branch_id' => ['nullable', 'exists:branches,id'],
        ], [
            'first_name.required' => __t('กรุณากรอกชื่อ', 'Please enter your first name.'),
            'first_name.max' => __t('ชื่อยาวเกินไป (ไม่เกิน 120 ตัวอักษร)', 'First name is too long (max 120 characters).'),
            'last_name.max' => __t('นามสกุลยาวเกินไป (ไม่เกิน 120 ตัวอักษร)', 'Last name is too long (max 120 characters).'),

            'phone.required' => __t('กรุณากรอกเบอร์โทรศัพท์', 'Please enter your phone number.'),
            'phone.digits_between' => __t(
                'เบอร์โทรศัพท์ไม่ถูกต้อง กรุณากรอกเบอร์ 9-10 หลัก เช่น 0812345678',
                'That phone number looks invalid. Please enter 9-10 digits, e.g. 0812345678'
            ),
            'phone.unique' => __t(
                'เบอร์นี้เคยสมัครไว้แล้ว กรุณาเข้าสู่ระบบ หรือใช้เบอร์อื่น',
                'This phone number is already registered. Please log in, or use another number.'
            ),

            'email.email' => __t('รูปแบบอีเมลไม่ถูกต้อง เช่น name@example.com', 'That email address looks invalid, e.g. name@example.com'),
            'email.max' => __t('อีเมลยาวเกินไป', 'That email address is too long.'),
            'email.unique' => __t(
                'อีเมลนี้เคยสมัครไว้แล้ว กรุณาเข้าสู่ระบบ หรือใช้อีเมลอื่น',
                'This email is already registered. Please log in, or use another email.'
            ),

            'password.required' => __t('กรุณาตั้งรหัสผ่าน', 'Please choose a password.'),
            'password.min' => __t('รหัสผ่านต้องยาวอย่างน้อย 6 ตัวอักษร', 'Your password must be at least 6 characters.'),
            'password.confirmed' => __t('รหัสผ่านทั้งสองช่องไม่ตรงกัน กรุณากรอกใหม่', 'The two passwords do not match. Please re-enter them.'),

            'home_branch_id.exists' => __t('ไม่พบสาขาที่เลือก กรุณาเลือกใหม่', 'That branch was not found. Please choose again.'),
        ]);

        $data['phone'] = preg_replace('/\D/', '', $data['phone']);
        $data['code'] = $this->nextCode();
        $data['status'] = 'active';
        $data['preferred_locale'] = app()->getLocale();

        $customer = Customer::create($data);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', __t('สมัครสมาชิกเรียบร้อยแล้ว', 'Your account has been created.'));
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // หน้าแอปลูกค้าต้องล็อกอินแล้ว ออกจากระบบจึงต้องส่งกลับหน้าแนะนำตัว
        return redirect()->route('landing');
    }

    private function nextCode(): string
    {
        $last = Customer::withTrashed()->orderByDesc('id')->value('id') ?? 0;

        return 'DP-' . str_pad((string) ($last + 1), 5, '0', STR_PAD_LEFT);
    }
}
