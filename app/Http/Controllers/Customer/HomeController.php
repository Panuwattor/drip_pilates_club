<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\ClassSession;
use App\Models\Trainer;
use App\Services\BookingService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(private BookingService $bookings) {}

    /** หน้าแรก — สรุปเครดิต คลาสถัดไป บริการด่วน และรายชื่อครู */
    public function index(Request $request)
    {
        $branches = Branch::active()->orderBy('sort_order')->get();
        $customer = auth('customer')->user();

        $branchId = (int) $request->input('branch', 0);

        if (! $branches->contains('id', $branchId)) {
            $branchId = $customer?->home_branch_id ?: ($branches->first()->id ?? 0);
        }

        $upcoming = collect();

        if ($customer) {
            $upcoming = Booking::with(['classSession.classType', 'classSession.branch', 'classSession.trainer'])
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['confirmed', 'waitlisted'])
                ->whereHas('classSession', fn ($q) => $q->where('start_at', '>', now()))
                ->get()
                ->sortBy(fn ($b) => $b->classSession->start_at)
                ->values();
        }

        return view('customer.home', [
            'branches' => $branches,
            'currentBranchId' => $branchId,
            'customer' => $customer,
            'upcoming' => $upcoming,
            'packages' => $customer
                ? $customer->packages()->with('package')->where('status', '!=', 'cancelled')->orderBy('expires_at')->get()
                : collect(),
            'totalCredits' => $customer?->totalCredits() ?? 0,
            'hasUnlimited' => $customer?->hasUnlimited() ?? false,
            'trainers' => Trainer::active()->orderBy('sort_order')->take(8)->get(),
            'announcements' => Announcement::visible()
                ->where(fn ($q) => $q->whereNull('branch_id')->orWhere('branch_id', $branchId))
                ->orderBy('sort_order')->get(),
        ]);
    }

    /** ดึงตารางคลาสแบบ JSON ไว้ให้หน้าเว็บโหลดใหม่ตอนสลับวัน/สาขา */
    public function sessions(Request $request)
    {
        $data = $request->validate([
            'branch' => ['required', 'exists:branches,id'],
            'date' => ['required', 'date'],
        ]);

        $customer = auth('customer')->user();

        $sessions = ClassSession::with(['classType', 'trainer', 'substituteTrainer', 'room'])
            ->where('branch_id', $data['branch'])
            ->whereDate('start_at', $data['date'])
            ->where('status', 'scheduled')
            ->orderBy('start_at')
            ->get();

        $myBookings = $customer
            ? Booking::where('customer_id', $customer->id)
                ->whereIn('class_session_id', $sessions->pluck('id'))
                ->whereIn('status', ['confirmed', 'waitlisted'])
                ->get()->keyBy('class_session_id')
            : collect();

        // ส่ง HTML ที่เรนเดอร์แล้วกลับไป จะได้ใช้ partial ตัวเดียวกับตอนโหลดหน้าแรก
        return response()->json([
            'html' => view('partials.class-cards', compact('sessions', 'myBookings'))->render(),
            'count' => $sessions->count(),
        ]);
    }

    public function book(Request $request, ClassSession $session)
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return response()->json(['ok' => false, 'message' => 'กรุณาเข้าสู่ระบบก่อนจองคลาส', 'need_login' => true], 401);
        }

        try {
            $booking = $this->bookings->book($customer, $session);
        } catch (BookingException $e) {
            return response()->json(['ok' => false, 'message' => $e->localizedMessage()], 422);
        }

        $isWaitlist = $booking->status === 'waitlisted';

        return response()->json([
            'ok' => true,
            'status' => $booking->status,
            'waitlist_position' => $booking->waitlist_position,
            'message' => $isWaitlist
                ? __('คุณอยู่ในคิวสำรองลำดับที่ :n', ['n' => $booking->waitlist_position])
                : 'จองคลาสเรียบร้อยแล้ว',
        ]);
    }

    /** ดูก่อนว่ายกเลิกตอนนี้จะเสียเครดิตไหม */
    public function cancelPreview(Booking $booking)
    {
        $this->authorizeBooking($booking);

        $preview = $this->bookings->cancellationPreview($booking);

        return response()->json([
            'will_lose_credit' => $preview['will_lose_credit'],
            'credit_at_stake' => $preview['credit_at_stake'],
            'deadline_hours' => $preview['deadline_hours'],
            'deadline' => $preview['deadline']->format('d/m/Y H:i'),
        ]);
    }

    public function cancel(Booking $booking)
    {
        $this->authorizeBooking($booking);

        try {
            $result = $this->bookings->cancel($booking, 'customer');
        } catch (BookingException $e) {
            return response()->json(['ok' => false, 'message' => $e->localizedMessage()], 422);
        }

        return response()->json([
            'ok' => true,
            'status' => $result->status,
            'refunded' => (bool) $result->credit_refunded,
            'message' => $result->credit_refunded
                ? 'ยกเลิกเรียบร้อยแล้ว คืนเครดิตให้แล้ว'
                : 'ยกเลิกเรียบร้อยแล้ว (เลยกำหนดยกเลิกฟรี จึงไม่คืนเครดิต)',
        ]);
    }

    private function authorizeBooking(Booking $booking): void
    {
        abort_unless(
            auth('customer')->check() && $booking->customer_id === auth('customer')->id(),
            403
        );
    }

    /** สลับภาษา จำไว้ใน session */
    public function setLocale(Request $request, string $locale)
    {
        if (! in_array($locale, ['th', 'en'], true)) {
            $locale = 'th';
        }

        session(['locale' => $locale]);

        if ($customer = auth('customer')->user()) {
            $customer->update(['preferred_locale' => $locale]);
        }

        return back();
    }
}
