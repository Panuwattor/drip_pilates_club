<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * หน้าให้แอดมินเปลี่ยนรหัสผ่านของตัวเอง
 *
 * แยกจาก UserController เพราะอันนั้นอยู่หลัง middleware 'owner'
 * พนักงานกับผู้จัดการเลยเปลี่ยนรหัสผ่านตัวเองไม่ได้ ต้องไปขอเจ้าของตั้งให้
 * หน้านี้เปิดให้ทุกคนที่ล็อกอินอยู่ แต่แก้ได้เฉพาะบัญชีตัวเองเท่านั้น
 */
class ProfileController extends Controller
{
    public function edit()
    {
        return view('admin.profile.password');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => __t('กรุณากรอกรหัสผ่านปัจจุบัน', 'Please enter your current password.'),
            'password.required' => __t('กรุณาตั้งรหัสผ่านใหม่', 'Please choose a new password.'),
            'password.min' => __t('รหัสผ่านต้องยาวอย่างน้อย 8 ตัวอักษร', 'Your password must be at least 8 characters.'),
            'password.confirmed' => __t('รหัสผ่านใหม่ทั้งสองช่องไม่ตรงกัน กรุณากรอกใหม่', 'The two new passwords do not match. Please re-enter them.'),
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __t('รหัสผ่านปัจจุบันไม่ถูกต้อง', 'That current password is incorrect.'),
            ]);
        }

        // กันตั้งรหัสเดิมซ้ำ เพราะผู้ใช้มักกดบันทึกแล้วเข้าใจว่าเปลี่ยนไปแล้วทั้งที่ยังเหมือนเดิม
        if (Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => __t('รหัสผ่านใหม่ต้องไม่ซ้ำกับรหัสผ่านเดิม', 'Your new password must be different from the current one.'),
            ]);
        }

        // cast 'hashed' ในโมเดล User จัดการ Hash::make ให้แล้ว ไม่ต้องแฮชซ้ำ
        $user->forceFill(['password' => $data['password']])->save();

        // ออก session id ใหม่กัน session fixation หลังเปลี่ยนรหัส
        //
        // หมายเหตุ: ยังไม่ได้เตะเครื่องอื่นที่ล็อกอินค้างไว้ออก เพราะ Auth::logoutOtherDevices()
        // ต้องเปิด middleware AuthenticateSession ก่อนถึงจะทำงาน ซึ่งโปรเจกต์นี้ยังไม่ได้เปิด
        // ถ้าอยากได้ค่อยเปิดใน bootstrap/app.php แล้วเพิ่มบรรทัดนั้นทีหลัง
        $request->session()->regenerate();

        return back()->with('status', __t('เปลี่ยนรหัสผ่านเรียบร้อยแล้ว', 'Your password has been changed.'));
    }
}
