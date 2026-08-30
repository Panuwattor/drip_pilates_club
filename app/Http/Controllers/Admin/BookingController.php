<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Branch;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    public function index(Request $request)
    {
        $branchIds = auth()->user()->accessibleBranchIds();

        $query = Booking::with(['customer', 'classSession.classType', 'classSession.branch'])
            ->whereHas('classSession', fn ($q) => $q->whereIn('branch_id', $branchIds));

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%"));
            });
        }

        if ($branch = $request->input('branch')) {
            $query->whereHas('classSession', fn ($q) => $q->where('branch_id', $branch));
        }

        if ($from = $request->input('from')) {
            $query->whereHas('classSession', fn ($q) => $q->whereDate('start_at', '>=', $from));
        }

        if ($to = $request->input('to')) {
            $query->whereHas('classSession', fn ($q) => $q->whereDate('start_at', '<=', $to));
        }

        $bookings = $query->latest('id')->paginate(30)->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'branches' => Branch::whereIn('id', $branchIds)->orderBy('sort_order')->get(),
        ]);
    }

    public function checkIn(Booking $booking)
    {
        try {
            $this->bookings->checkIn($booking, auth()->id());
        } catch (BookingException $e) {
            return back()->with('error', $e->localizedMessage('th'));
        }

        return back()->with('status', 'เช็คอินเรียบร้อยแล้ว');
    }

    public function noShow(Booking $booking)
    {
        try {
            $this->bookings->markNoShow($booking, auth()->id());
        } catch (BookingException $e) {
            return back()->with('error', $e->localizedMessage('th'));
        }

        return back()->with('status', 'บันทึกว่าไม่มาเรียนแล้ว');
    }

    public function cancel(Request $request, Booking $booking)
    {
        $reason = $request->input('reason');

        try {
            $this->bookings->cancel($booking, 'admin', $reason, auth()->id());
        } catch (BookingException $e) {
            return back()->with('error', $e->localizedMessage('th'));
        }

        return back()->with('status', 'ยกเลิกการจองและคืนเครดิตแล้ว');
    }
}
