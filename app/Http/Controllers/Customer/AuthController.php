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
        ]);

        $phone = preg_replace('/\D/', '', $data['phone']);

        if (! Auth::guard('customer')->attempt(['phone' => $phone, 'password' => $data['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'phone' => 'เบอร์โทรหรือรหัสผ่านไม่ถูกต้อง',
            ]);
        }

        $customer = Auth::guard('customer')->user();

        if ($customer->status !== 'active') {
            Auth::guard('customer')->logout();

            throw ValidationException::withMessages([
                'phone' => 'บัญชีนี้ถูกระงับการใช้งาน กรุณาติดต่อเจ้าหน้าที่',
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
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'unique:customers,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'home_branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $data['phone'] = preg_replace('/\D/', '', $data['phone']);
        $data['code'] = $this->nextCode();
        $data['status'] = 'active';
        $data['preferred_locale'] = app()->getLocale();

        $customer = Customer::create($data);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', 'สมัครสมาชิกเรียบร้อยแล้ว');
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
