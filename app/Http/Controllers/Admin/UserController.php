<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', [
            'users' => User::with('branch')->orderBy('role')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.users.form', [
            'user' => new User(['role' => 'staff']),
            'branches' => Branch::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'in:owner,manager,staff'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]) + ['is_active' => $request->boolean('is_active', true)];

        User::create($data);

        return redirect()->route('admin.users.index')
            ->with('status', 'เพิ่มผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', [
            'user' => $user,
            'branches' => Branch::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'in:owner,manager,staff'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]) + ['is_active' => $request->boolean('is_active')];

        if (empty($data['password'])) {
            unset($data['password']);
        }

        // กันไม่ให้เจ้าของคนสุดท้ายถอดสิทธิ์ตัวเองจนไม่มีใครดูแลระบบได้
        if ($user->isOwner() && $data['role'] !== 'owner') {
            $otherOwners = User::where('role', 'owner')->where('id', '!=', $user->id)->count();

            if ($otherOwners === 0) {
                return back()->with('error', 'ต้องมีเจ้าของระบบอย่างน้อย 1 คน');
            }
        }

        $user->update($data);

        return back()->with('status', 'บันทึกข้อมูลผู้ใช้งานแล้ว');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'ลบบัญชีตัวเองไม่ได้');
        }

        if ($user->isOwner() && User::where('role', 'owner')->count() <= 1) {
            return back()->with('error', 'ต้องมีเจ้าของระบบอย่างน้อย 1 คน');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'ลบผู้ใช้งานแล้ว');
    }
}
