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
    /**
     * คอลัมน์ที่ยอมให้กดเรียงได้ — รับเฉพาะคีย์ในนี้เท่านั้น
     * ค่าจาก query string จึงไม่มีทางหลุดเข้า SQL โดยตรง
     */
    private const SORTABLE = [
        'date' => 'class_sessions.start_at',
        'customer' => 'customers.first_name',
        'status' => 'bookings.status',
        'code' => 'bookings.code',
        'booked' => 'bookings.id',
    ];

    public function __construct(private BookingService $bookings) {}

    public function index(Request $request)
    {
        $branchIds = auth()->user()->accessibleBranchIds();

        $query = Booking::with(['customer', 'classSession.classType', 'classSession.branch'])
            ->whereHas('classSession', fn ($q) => $q->whereIn('branch_id', $branchIds));

        // ระบุชื่อตารางให้ชัด เพราะการกดเรียงจะ join customers/class_sessions
        // ซึ่งมีคอลัมน์ status และ code ชื่อซ้ำกัน
        if ($status = $request->input('status')) {
            $query->where('bookings.status', $status);
        }

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('bookings.code', 'like', "%{$search}%")
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

        // เรียงตามวันเวลาเรียนเป็นค่าเริ่มต้น ให้ตรงกับคอลัมน์ "วันเวลา" ที่เห็นในตาราง
        $sort = $request->input('sort');
        $sort = isset(self::SORTABLE[$sort]) ? $sort : 'date';
        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        // คอลัมน์ที่อยู่คนละตารางต้อง join ก่อน แล้ว select เฉพาะ bookings.*
        // ไม่งั้นคอลัมน์ชื่อซ้ำ (id, status, created_at) จะทับค่าของ booking
        if ($sort === 'date') {
            $query->join('class_sessions', 'class_sessions.id', '=', 'bookings.class_session_id')
                ->select('bookings.*');
        } elseif ($sort === 'customer') {
            $query->join('customers', 'customers.id', '=', 'bookings.customer_id')
                ->select('bookings.*');
        }

        $query->orderBy(self::SORTABLE[$sort], $dir);

        // เรียงชื่อลูกค้าต้องดูนามสกุลต่อ และทุกแบบปิดท้ายด้วย id กันลำดับสลับไปมาเมื่อค่าซ้ำ
        if ($sort === 'customer') {
            $query->orderBy('customers.last_name', $dir);
        }
        if ($sort !== 'booked') {
            $query->orderBy('bookings.id', 'desc');
        }

        $bookings = $query->paginate(30)->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'branches' => Branch::whereIn('id', $branchIds)->orderBy('sort_order')->get(),
            'sort' => $sort,
            'dir' => $dir,
        ]);
    }

    public function checkIn(Booking $booking)
    {
        try {
            $this->bookings->checkIn($booking, auth()->id());
        } catch (BookingException $e) {
            return back()->with('error', $e->localizedMessage());
        }

        return back()->with('status', __t('เช็คอินเรียบร้อยแล้ว', 'Checked in'));
    }

    public function noShow(Booking $booking)
    {
        try {
            $this->bookings->markNoShow($booking, auth()->id());
        } catch (BookingException $e) {
            return back()->with('error', $e->localizedMessage());
        }

        return back()->with('status', __t('บันทึกว่าไม่มาเรียนแล้ว', 'Marked as no-show'));
    }

    /** ย้อนสถานะที่ระบบปิดไปแล้ว ให้แอดมินแก้ตามจริง เช่น ลูกค้ามาเรียนแต่ลืมเช็คอิน */
    public function reopen(Request $request, Booking $booking)
    {
        try {
            $this->bookings->reopen($booking, auth()->id(), $request->input('reason'));
        } catch (BookingException $e) {
            return back()->with('error', $e->localizedMessage());
        }

        return back()->with('status', __t('ย้อนสถานะเป็นยืนยันแล้ว แก้ไขได้เลย', 'Reverted to Confirmed — you can edit it now'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        $reason = $request->input('reason');

        try {
            $this->bookings->cancel($booking, 'admin', $reason, auth()->id());
        } catch (BookingException $e) {
            return back()->with('error', $e->localizedMessage());
        }

        return back()->with('status', __t('ยกเลิกการจองและคืนเครดิตแล้ว', 'Booking cancelled and credit refunded'));
    }
}
