<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

/**
 * ศูนย์แจ้งเตือนของลูกค้า — แสดง Notification ที่ระบบสร้างไว้ตอนจอง/ได้คิว/คลาสยกเลิก/จ่ายเงินสำเร็จ
 * เดิมข้อมูลถูกเขียนลง DB แต่ไม่มีหน้าไหนแสดง ตรงนี้คือหน้าที่เปิดให้ลูกค้าเห็นและกดอ่าน
 */
class NotificationController extends Controller
{
    /** รายการแจ้งเตือนทั้งหมด (ใหม่สุดก่อน) */
    public function index()
    {
        $customer = auth('customer')->user();

        $notifications = $customer->notifications()
            ->latest('id')
            ->paginate(30);

        return view('customer.notifications', [
            'notifications' => $notifications,
        ]);
    }

    /** กดเข้าอ่านทีละอัน แล้วเด้งไปหน้าที่เกี่ยวข้อง (ถ้ามี) */
    public function read(Notification $notification)
    {
        $this->authorizeNotification($notification);

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return redirect($this->targetUrl($notification));
    }

    /** ทำเครื่องหมายอ่านทั้งหมด */
    public function readAll()
    {
        auth('customer')->user()->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('status', __t('ทำเครื่องหมายอ่านทั้งหมดแล้ว', 'All notifications marked as read'));
    }

    /** จำนวนที่ยังไม่อ่าน ใช้กับกระดิ่งใน topbar (โหลดแบบ ajax) */
    public function unreadCount()
    {
        return response()->json([
            'count' => auth('customer')->user()->notifications()->unread()->count(),
        ]);
    }

    private function authorizeNotification(Notification $notification): void
    {
        abort_unless($notification->customer_id === auth('customer')->id(), 403);
    }

    /** หาปลายทางที่ควรพาไปตามชนิดของแจ้งเตือน */
    private function targetUrl(Notification $notification): string
    {
        $data = $notification->data ?? [];

        if (! empty($data['order_id'])) {
            return route('customer.purchase.orders');
        }

        if (! empty($data['booking_id']) || ! empty($data['session_id'])) {
            return route('customer.bookings');
        }

        return route('customer.notifications.index');
    }
}
