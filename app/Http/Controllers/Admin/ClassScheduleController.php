<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ClassSchedule;
use App\Models\ClassType;
use App\Models\Room;
use App\Models\Trainer;
use App\Services\SessionGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class ClassScheduleController extends Controller
{
    public const DAYS = [
        0 => 'อาทิตย์', 1 => 'จันทร์', 2 => 'อังคาร', 3 => 'พุธ',
        4 => 'พฤหัสบดี', 5 => 'ศุกร์', 6 => 'เสาร์',
    ];

    public function index(Request $request)
    {
        $branchIds = auth()->user()->accessibleBranchIds();
        $branchId = (int) ($request->input('branch') ?: ($branchIds[0] ?? 0));

        if (! in_array($branchId, $branchIds, true)) {
            $branchId = $branchIds[0] ?? 0;
        }

        $schedules = ClassSchedule::with(['classType', 'trainer', 'room'])
            ->where('branch_id', $branchId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        return view('admin.schedules.index', [
            'schedules' => $schedules,
            'branches' => Branch::whereIn('id', $branchIds)->orderBy('sort_order')->get(),
            'branchId' => $branchId,
            'days' => self::DAYS,
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.schedules.form', $this->formData(new ClassSchedule([
            'branch_id' => $request->input('branch'),
            'day_of_week' => $request->input('day'),
            'effective_from' => now()->toDateString(),
        ])));
    }

    public function store(Request $request, SessionGenerator $generator)
    {
        $schedule = ClassSchedule::create($this->validated($request));

        // สร้างรอบเรียนให้ทันที แอดมินจะได้เห็นผลเลย
        $result = $generator->generate(CarbonImmutable::today());

        return redirect()->route('admin.schedules.index', ['branch' => $schedule->branch_id])
            ->with('status', "เพิ่มตารางแล้ว สร้างรอบเรียนใหม่ {$result['created']} รอบ");
    }

    public function edit(ClassSchedule $schedule)
    {
        return view('admin.schedules.form', $this->formData($schedule));
    }

    public function update(Request $request, ClassSchedule $schedule)
    {
        $schedule->update($this->validated($request));

        return redirect()->route('admin.schedules.index', ['branch' => $schedule->branch_id])
            ->with('status', 'บันทึกตารางแล้ว (รอบเรียนที่สร้างไว้แล้วไม่เปลี่ยนตาม ต้องแก้รายรอบเอง)');
    }

    public function destroy(ClassSchedule $schedule)
    {
        $branchId = $schedule->branch_id;

        // รอบเรียนที่ยังไม่ถึงและยังไม่มีใครจอง ลบทิ้งได้
        $deleted = $schedule->sessions()
            ->where('start_at', '>', now())
            ->where('booked_count', 0)
            ->delete();

        $schedule->update(['is_active' => false, 'effective_until' => now()->toDateString()]);

        return redirect()->route('admin.schedules.index', ['branch' => $branchId])
            ->with('status', "ปิดตารางแล้ว ลบรอบที่ยังไม่มีคนจอง {$deleted} รอบ");
    }

    /** ปุ่มสร้างรอบเรียนล่วงหน้าด้วยตัวเอง */
    public function generate(Request $request, SessionGenerator $generator)
    {
        $days = (int) $request->input('days', 90);
        $result = $generator->generate(CarbonImmutable::today(), $days);

        return back()->with('status', sprintf(
            'สร้างรอบเรียนใหม่ %d รอบ (มีอยู่แล้ว %d, ข้ามวันหยุด %d)',
            $result['created'], $result['skipped'], $result['holiday_skipped']
        ));
    }

    private function formData(ClassSchedule $schedule): array
    {
        $branchIds = auth()->user()->accessibleBranchIds();

        return [
            'schedule' => $schedule,
            'branches' => Branch::whereIn('id', $branchIds)->orderBy('sort_order')->get(),
            'rooms' => Room::active()->with('branch')->orderBy('branch_id')->get(),
            'classTypes' => ClassType::active()->orderBy('sort_order')->get(),
            'trainers' => Trainer::active()->orderBy('sort_order')->get(),
            'days' => self::DAYS,
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'class_type_id' => ['required', 'exists:class_types,id'],
            'trainer_id' => ['nullable', 'exists:trainers,id'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'duration_min' => ['required', 'integer', 'min:15', 'max:240'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'credit_cost' => ['required', 'numeric', 'min:0', 'max:99'],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date', 'after:effective_from'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
