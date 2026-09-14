<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\CustomerPackage;
use App\Models\Notification;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * ออกแพ็กและเพิ่มเครดิตให้ลูกค้าเมื่อชำระเงินครบ
 *
 * เดิมตรรกะนี้อยู่ใน Admin\OrderController อย่างเดียว พอเปิดให้ลูกค้าซื้อเองแล้ว
 * ทั้งสองฝั่งต้องออกแพ็กแบบเดียวกันเป๊ะ เลยย้ายมาไว้ที่เดียว
 */
class PackageFulfillment
{
    /**
     * ออกแพ็กให้ลูกค้าถ้าชำระครบแล้ว คืนค่า true เมื่อออกแพ็กจริง
     *
     * @param  int|null  $userId  แอดมินที่กดอนุมัติ (null = ระบบทำเอง)
     */
    public function fulfill(Order $order, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($order, $userId) {
            // ล็อกแถวกันกดอนุมัติซ้อนกันสองหน้าต่างแล้วได้แพ็กสองใบ
            $order = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $order || $order->status === 'paid') {
                return false;
            }

            if ($order->paidAmount() < (float) $order->total) {
                return false;
            }

            $order->load('items.package', 'customer');

            foreach ($order->items as $item) {
                $package = $item->package;

                if (! $package) {
                    continue;
                }

                for ($i = 0; $i < $item->quantity; $i++) {
                    $customerPackage = CustomerPackage::create([
                        'code' => $this->nextPackageCode(),
                        'customer_id' => $order->customer_id,
                        'package_id' => $package->id,
                        'order_id' => $order->id,
                        'type' => $package->type,
                        'purchased_at' => now(),
                        'starts_at' => now()->toDateString(),
                        'expires_at' => now()->addDays($package->valid_days)->toDateString(),
                        'credit_total' => $package->credit_amount,
                        'credit_used' => 0,
                        'credit_remaining' => $package->credit_amount,
                        'max_per_day' => $package->max_per_day,
                        'max_per_week' => $package->max_per_week,
                        'max_future_bookings' => $package->max_future_bookings,
                        'status' => 'active',
                        'created_by' => $userId,
                    ]);

                    if ($package->credit_amount) {
                        CreditTransaction::create([
                            'customer_id' => $order->customer_id,
                            'customer_package_id' => $customerPackage->id,
                            'amount' => $package->credit_amount,
                            'balance_after' => $order->customer->totalCredits(),
                            'type' => 'purchase',
                            'reason_th' => 'ซื้อ ' . $package->name_th,
                            'reason_en' => 'Purchased ' . $package->name_en,
                            'user_id' => $userId,
                        ]);
                    }
                }
            }

            $order->update(['status' => 'paid', 'paid_at' => now()]);

            $this->notifyCustomer($order);

            return true;
        });
    }

    /** แจ้งลูกค้าในแอปว่าแพ็กพร้อมใช้แล้ว */
    private function notifyCustomer(Order $order): void
    {
        $credits = $order->customer->totalCredits();

        Notification::create([
            'customer_id' => $order->customer_id,
            'type' => 'order_paid',
            'title_th' => 'ยืนยันการชำระเงินแล้ว',
            'title_en' => 'Payment confirmed',
            'body_th' => "คำสั่งซื้อ {$order->code} ได้รับการยืนยันแล้ว เครดิตคงเหลือ {$credits} ครั้ง",
            'body_en' => "Order {$order->code} is confirmed. You now have {$credits} credits.",
            'data' => ['order_id' => $order->id],
            'channel' => 'in_app',
            'sent_at' => now(),
        ]);
    }

    private function nextPackageCode(): string
    {
        return 'CP-' . now()->format('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }
}
