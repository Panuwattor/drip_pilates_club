<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\ClassSession;
use App\Models\ClassType;
use App\Models\Customer;
use App\Models\Room;
use App\Models\Trainer;
use App\Services\BookingService;
use Illuminate\Http\Request;

class ClassSessionController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    public function index(Request $request)
    {
        $branchIds = auth()->user()->accessibleBranchIds();
        $branchId = (int) ($request->input('branch') ?: ($branchIds[0] ?? 0));

        if (! in_array($branchId, $branchIds, true)) {
            $branchId = $branchIds[0] ?? 0;
        }

        $date = $request->input('date', now()->toDateString());
        $view = $request->input('view', 'day');

        $query = ClassSession::with(['classType', 'trainer', 'substituteTrainer', 'room'])
            ->where('branch_id', $branchId)
            ->orderBy('start_at');

        if ($view === 'week') {
            $start = \Carbon\Carbon::parse($date)->startOfWeek();
            $end = \Carbon\Carbon::parse($date)->endOfWeek();
            $query->whereBetween('start_at', [$start, $end]);
        } else {
            $query->whereDate('start_at', $date);
        }

        $sessions = $query->get();

        return view('admin.sessions.index', [
            'sessions' => $view === 'week' ? $sessions->groupBy(fn ($s) => $s->start_at->toDateString()) : collect(['x' => $sessions]),
            'flatSessions' => $sessions,
            'branches' => Branch::whereIn('id', $branchIds)->orderBy('sort_order')->get(),
            'branchId' => $branchId,
            'date' => $date,
            'view' => $view,
        ]);
    }

    public function show(ClassSession $session)
    {
        $session->load(['classType', 'trainer', 'substituteTrainer', 'room', 'branch']);

        // เรียงเองใน PHP แทน FIELD() ของ MySQL จะได้ใช้ได้กับทุกฐานข้อมูล
        $order = ['confirmed' => 1, 'attended' => 2, 'waitlisted' => 3,
                  'no_show' => 4, 'late_cancelled' => 5, 'cancelled' => 6];

        $bookings = Booking::with(['customer', 'customerPackage.package'])
            ->where('class_session_id', $session->id)
            ->get()
            ->sortBy(fn ($b) => [$order[$b->status] ?? 9, $b->waitlist_position ?? 0, $b->id])
            ->values();

        return view('admin.sessions.show', [
            'session' => $session,
            'bookings' => $bookings,
            'trainers' => Trainer::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function edit(ClassSession $session)
    {
        return view('admin.sessions.form', [
            'session' => $session,
            'rooms' => Room::active()->where('branch_id', $session->branch_id)->get(),
            'classTypes' => ClassType::active()->orderBy('sort_order')->get(),
            'trainers' => Trainer::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, ClassSession $session)
    {
        $data = $request->validate([
            'class_type_id' => ['required', 'exists:class_types,id'],
            'trainer_id' => ['nullable', 'exists:trainers,id'],
            'substitute_trainer_id' => ['nullable', 'exists:trainers,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'start_at' => ['required', 'date'],
            'duration_min' => ['required', 'integer', 'min:15', 'max:240'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'credit_cost' => ['required', 'numeric', 'min:0', 'max:99'],
            'note_th' => ['nullable', 'string'],
            'note_en' => ['nullable', 'string'],
        ]);

        // ลดที่นั่งต่ำกว่าจำนวนคนที่จองไปแล้วไม่ได้
        if ($data['capacity'] < $session->booked_count) {
            return back()->with('error', "ลดที่นั่งเหลือ {$data['capacity']} ไม่ได้ เพราะมีคนจองแล้ว {$session->booked_count} คน");
        }

        $start = \Carbon\Carbon::parse($data['start_at']);
        $data['end_at'] = $start->copy()->addMinutes($data['duration_min']);
        unset($data['duration_min']);

        $session->update($data);

        // ถ้าเพิ่มที่นั่ง ลองเลื่อนคิวขึ้นมาเลย
        if ($session->waitlist_count > 0) {
            $this->bookings->promoteFromWaitlist($session);
        }

        return redirect()->route('admin.sessions.show', $session)
            ->with('status', 'บันทึกรอบเรียนแล้ว');
    }

    /** เปลี่ยนครูสอนแทนแบบเร็ว */
    public function setSubstitute(Request $request, ClassSession $session)
    {
        $data = $request->validate([
            'substitute_trainer_id' => ['nullable', 'exists:trainers,id'],
        ]);

        $session->update($data);

        return back()->with('status', $data['substitute_trainer_id']
            ? 'บันทึกครูสอนแทนแล้ว'
            : 'ยกเลิกครูสอนแทนแล้ว');
    }

    public function cancel(Request $request, ClassSession $session)
    {
        $data = $request->validate([
            'reason_th' => ['required', 'string', 'max:255'],
            'reason_en' => ['required', 'string', 'max:255'],
        ]);

        $affected = $this->bookings->cancelSession(
            $session, $data['reason_th'], $data['reason_en'], auth()->id()
        );

        return redirect()->route('admin.sessions.index', [
            'branch' => $session->branch_id,
            'date' => $session->start_at->toDateString(),
        ])->with('status', "ยกเลิกรอบเรียนแล้ว คืนเครดิตให้ลูกค้า {$affected} คน");
    }

    /** แอดมินจองแทนลูกค้า */
    public function bookForCustomer(Request $request, ClassSession $session)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
        ]);

        $customer = Customer::findOrFail($data['customer_id']);

        try {
            $booking = $this->bookings->book($customer, $session, 'admin', auth()->id());
        } catch (BookingException $e) {
            return back()->with('error', $e->localizedMessage('th'));
        }

        $label = $booking->status === 'waitlisted' ? 'เข้าคิวสำรอง' : 'จอง';

        return back()->with('status', "{$label}ให้ {$customer->full_name} เรียบร้อยแล้ว");
    }
}
