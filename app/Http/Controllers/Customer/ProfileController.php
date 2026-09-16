<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Booking;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /** ภาพรวมบัญชี — สถิติ แพ็กเกจ เมนูตั้งค่า */
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $branches = Branch::active()->orderBy('sort_order')->get();

        $upcoming = Booking::with(['classSession.classType', 'classSession.branch', 'classSession.trainer'])
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['confirmed', 'waitlisted'])
            ->whereHas('classSession', fn ($q) => $q->where('start_at', '>', now()))
            ->get();

        return view('customer.profile.index', [
            'customer' => $customer,
            'branches' => $branches,
            'currentBranchId' => $customer->home_branch_id ?: ($branches->first()->id ?? 0),
            'upcoming' => $upcoming,
            'packages' => $customer->packages()->with('package')->where('status', '!=', 'cancelled')->orderBy('expires_at')->get(),
            'totalCredits' => $customer->totalCredits(),
            'hasUnlimited' => $customer->hasUnlimited(),
        ]);
    }

    /** ฟอร์มแก้ไขข้อมูลส่วนตัว */
    public function edit()
    {
        return view('customer.profile.edit', [
            'customer' => Auth::guard('customer')->user(),
            'branches' => Branch::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'nickname' => ['nullable', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer->id)],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:female,male,other'],
            'home_branch_id' => ['nullable', 'exists:branches,id'],
            'medical_note' => ['nullable', 'string'],
            'is_pregnant' => ['nullable', 'boolean'],
            'emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $customer->fill($data + ['is_pregnant' => $request->boolean('is_pregnant')])->save();

        return back()->with('status', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    /**
     * ตั้ง/เปลี่ยนรหัสผ่าน
     * คนที่สมัครผ่าน LINE ยังไม่มีรหัสเดิม จึงไม่ต้องกรอกรหัสปัจจุบัน
     */
    public function updatePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $rules = ['password' => ['required', 'string', 'min:6', 'confirmed']];

        if ($customer->hasPassword()) {
            $rules['current_password'] = ['required', 'string'];
        }

        $data = $request->validate($rules, [
            'password.required' => __t('กรุณาตั้งรหัสผ่าน', 'Please choose a password.'),
            'password.min' => __t('รหัสผ่านต้องยาวอย่างน้อย 6 ตัวอักษร', 'Your password must be at least 6 characters.'),
            'password.confirmed' => __t('รหัสผ่านทั้งสองช่องไม่ตรงกัน กรุณากรอกใหม่', 'The two passwords do not match. Please re-enter them.'),
            'current_password.required' => __t('กรุณากรอกรหัสผ่านปัจจุบัน', 'Please enter your current password.'),
        ]);

        if ($customer->hasPassword() && ! Hash::check($data['current_password'], $customer->password)) {
            throw ValidationException::withMessages([
                'current_password' => __t('รหัสผ่านปัจจุบันไม่ถูกต้อง', 'That current password is incorrect.'),
            ]);
        }

        $customer->forceFill(['password' => $data['password']])->save();

        return back()->with('status', $customer->wasChanged()
            ? __t(
                'ตั้งรหัสผ่านเรียบร้อยแล้ว ตอนนี้เข้าสู่ระบบด้วยเบอร์โทรได้แล้ว',
                'Your password is set. You can now log in with your phone number.'
            )
            : __t('บันทึกรหัสผ่านแล้ว', 'Your password has been saved.'));
    }
}
