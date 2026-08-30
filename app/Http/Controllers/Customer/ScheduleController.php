<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\ClassSession;
use App\Models\Setting;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::active()->orderBy('sort_order')->get();
        $customer = auth('customer')->user();

        $branchId = (int) $request->input('branch', 0);

        if (! $branches->contains('id', $branchId)) {
            $branchId = $customer?->home_branch_id ?: ($branches->first()->id ?? 0);
        }

        $date = $request->input('date', now()->toDateString());

        $sessions = ClassSession::with(['classType', 'trainer', 'substituteTrainer', 'room'])
            ->where('branch_id', $branchId)
            ->whereDate('start_at', $date)
            ->where('status', 'scheduled')
            ->orderBy('start_at')
            ->get();

        $myBookings = collect();

        if ($customer) {
            $myBookings = Booking::where('customer_id', $customer->id)
                ->whereIn('class_session_id', $sessions->pluck('id'))
                ->whereIn('status', ['confirmed', 'waitlisted'])
                ->get()
                ->keyBy('class_session_id');
        }

        $branchesForJs = $branches->map(fn ($b) => [
            'id' => (string) $b->id,
            'nameTh' => $b->name_th,
            'nameEn' => $b->name_en,
            'shortTh' => $b->short_name_th ?: $b->name_th,
            'shortEn' => $b->short_name_en ?: $b->name_en,
            'addrTh' => trim(($b->address_th ?: '') . ($b->phone ? ' · โทร ' . $b->phone : '')),
            'addrEn' => trim(($b->address_en ?: '') . ($b->phone ? ' · Tel ' . $b->phone : '')),
        ])->values();

        return view('customer.schedule', [
            'branches' => $branches,
            'branchesForJs' => $branchesForJs,
            'currentBranchId' => $branchId,
            'selectedDate' => $date,
            'sessions' => $sessions,
            'myBookings' => $myBookings,
            'rangeFutureDays' => (int) Setting::get('booking_open_days_ahead', 90),
            'rangePastDays' => (int) Setting::get('schedule_past_days', 2),
            'cancelDeadlineHours' => (int) Setting::get('cancel_deadline_hours', 12),
        ]);
    }
}
