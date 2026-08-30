<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Room;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount(['rooms', 'classSessions'])
            ->orderBy('sort_order')
            ->get();

        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.form', ['branch' => new Branch]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $branch = Branch::create($data);

        return redirect()->route('admin.branches.edit', $branch)
            ->with('status', 'เพิ่มสาขาเรียบร้อยแล้ว');
    }

    public function edit(Branch $branch)
    {
        $branch->load('rooms');

        return view('admin.branches.form', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $branch->update($this->validated($request, $branch));

        return back()->with('status', 'บันทึกข้อมูลสาขาแล้ว');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->classSessions()->exists()) {
            return back()->with('error', 'ลบไม่ได้ เพราะสาขานี้มีรอบเรียนอยู่ ให้ปิดใช้งานแทน');
        }

        $branch->delete();

        return redirect()->route('admin.branches.index')->with('status', 'ลบสาขาแล้ว');
    }

    public function storeRoom(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name_th' => ['required', 'string', 'max:120'],
            'name_en' => ['required', 'string', 'max:120'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'equipment_type' => ['required', 'in:reformer,mat,cadillac,chair,mixed'],
        ]);

        $branch->rooms()->create($data + ['is_active' => true]);

        return back()->with('status', 'เพิ่มห้องเรียบร้อยแล้ว');
    }

    public function updateRoom(Request $request, Room $room)
    {
        $data = $request->validate([
            'name_th' => ['required', 'string', 'max:120'],
            'name_en' => ['required', 'string', 'max:120'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'equipment_type' => ['required', 'in:reformer,mat,cadillac,chair,mixed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $room->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', 'บันทึกข้อมูลห้องแล้ว');
    }

    public function destroyRoom(Room $room)
    {
        $room->delete();

        return back()->with('status', 'ลบห้องแล้ว');
    }

    private function validated(Request $request, ?Branch $branch = null): array
    {
        $unique = 'unique:branches,code' . ($branch ? ',' . $branch->id : '');

        return $request->validate([
            'code' => ['required', 'string', 'max:30', 'alpha_dash', $unique],
            'name_th' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'short_name_th' => ['nullable', 'string', 'max:60'],
            'short_name_en' => ['nullable', 'string', 'max:60'],
            'address_th' => ['nullable', 'string'],
            'address_en' => ['nullable', 'string'],
            'direction_th' => ['nullable', 'string'],
            'direction_en' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'line_id' => ['nullable', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:255'],
            'google_map_url' => ['nullable', 'url', 'max:500'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'open_time' => ['required', 'date_format:H:i'],
            'close_time' => ['required', 'date_format:H:i', 'after:open_time'],
            'bank_name' => ['nullable', 'string', 'max:60'],
            'bank_account_name' => ['nullable', 'string', 'max:150'],
            'bank_account_number' => ['nullable', 'string', 'max:30'],
            'promptpay_id' => ['nullable', 'string', 'max:30'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
