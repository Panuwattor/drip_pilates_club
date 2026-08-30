<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ClassSession;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::with('branch')
            ->whereDate('date', '>=', now()->subMonth())
            ->orderBy('date')
            ->get();

        return view('admin.holidays.index', [
            'holidays' => $holidays,
            'branches' => Branch::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'date' => ['required', 'date'],
            'reason_th' => ['required', 'string', 'max:150'],
            'reason_en' => ['required', 'string', 'max:150'],
        ]);

        Holiday::create($data + ['is_closed_all_day' => true]);

        // เตือนแอดมินว่ามีรอบเรียนในวันนั้นที่สร้างไว้แล้ว ต้องเข้าไปจัดการเอง
        $affected = ClassSession::whereDate('start_at', $data['date'])
            ->where('status', 'scheduled')
            ->when($data['branch_id'] ?? null, fn ($q, $id) => $q->where('branch_id', $id))
            ->count();

        $message = 'บันทึกวันหยุดแล้ว';

        if ($affected > 0) {
            $message .= " (มีรอบเรียน {$affected} รอบในวันนั้นที่สร้างไว้แล้ว ต้องเข้าไปยกเลิกเอง)";
        }

        return back()->with('status', $message);
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return back()->with('status', 'ลบวันหยุดแล้ว');
    }
}
