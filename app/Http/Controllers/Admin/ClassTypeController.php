<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassType;
use Illuminate\Http\Request;

class ClassTypeController extends Controller
{
    public function index()
    {
        $classTypes = ClassType::withCount('schedules')
            ->orderBy('sort_order')
            ->get();

        return view('admin.class-types.index', compact('classTypes'));
    }

    public function create()
    {
        return view('admin.class-types.form', ['classType' => new ClassType]);
    }

    public function store(Request $request)
    {
        $classType = ClassType::create($this->validated($request));

        return redirect()->route('admin.class-types.index')
            ->with('status', 'เพิ่มประเภทคลาสเรียบร้อยแล้ว');
    }

    public function edit(ClassType $classType)
    {
        return view('admin.class-types.form', compact('classType'));
    }

    public function update(Request $request, ClassType $classType)
    {
        $classType->update($this->validated($request, $classType));

        return back()->with('status', 'บันทึกข้อมูลประเภทคลาสแล้ว');
    }

    public function destroy(ClassType $classType)
    {
        if ($classType->sessions()->exists()) {
            return back()->with('error', 'ลบไม่ได้ เพราะมีรอบเรียนใช้ประเภทนี้อยู่ ให้ปิดใช้งานแทน');
        }

        $classType->delete();

        return redirect()->route('admin.class-types.index')->with('status', 'ลบประเภทคลาสแล้ว');
    }

    private function validated(Request $request, ?ClassType $classType = null): array
    {
        $unique = 'unique:class_types,code' . ($classType ? ',' . $classType->id : '');

        return $request->validate([
            'code' => ['required', 'string', 'max:40', 'alpha_dash', $unique],
            'name_th' => ['required', 'string', 'max:150'],
            'name_en' => ['required', 'string', 'max:150'],
            'description_th' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'suitable_for_th' => ['nullable', 'string', 'max:255'],
            'suitable_for_en' => ['nullable', 'string', 'max:255'],
            'level' => ['required', 'in:all,beginner,intermediate,advanced'],
            'equipment_type' => ['required', 'in:reformer,mat,cadillac,chair,mixed'],
            'duration_min' => ['required', 'integer', 'min:15', 'max:240'],
            'default_capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'credit_cost' => ['required', 'numeric', 'min:0', 'max:99'],
            'color' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
