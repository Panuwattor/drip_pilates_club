<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class BookingListController extends Controller
{
    /** หน้าการจองของฉัน — กำลังจะถึงและประวัติ */
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        $upcoming = collect();
        $pastBookings = collect();

        if ($customer) {
            $upcoming = Booking::with(['classSession.classType', 'classSession.branch', 'classSession.trainer'])
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['confirmed', 'waitlisted'])
                ->whereHas('classSession', fn ($q) => $q->where('start_at', '>', now()))
                ->get()
                ->sortBy(fn ($b) => $b->classSession->start_at)
                ->values();

            $pastBookings = Booking::with(['classSession.classType', 'classSession.branch'])
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['attended', 'no_show', 'cancelled', 'late_cancelled'])
                ->latest('id')
                ->take(10)
                ->get();
        }

        return view('customer.bookings', [
            'customer' => $customer,
            'upcoming' => $upcoming,
            'pastBookings' => $pastBookings,
        ]);
    }
}
