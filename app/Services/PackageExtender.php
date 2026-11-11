<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\CreditTransaction;
use App\Models\CustomerPackage;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

/**
 * แอดมินขยายอายุแพ็กให้ลูกค้า ทั้งแพ็กที่ยังใช้อยู่และแพ็กที่หมดอายุไปแล้ว
 *
 * - เก็บจำนวนวันที่ขยายไว้ที่ extended_days (สะสม) วันหมดอายุที่คำนวณใหม่ภายหลังจะไม่ทำให้วันที่ขยายหาย
 * - แพ็กที่หมดอายุแล้ว: ต้องขยายให้วันหมดอายุใหม่ไม่ย้อนหลัง แล้วแพ็กจะกลับมาใช้ได้
 *   เครดิตที่ถูกตัดตอนหมดอายุจะคืนกลับ และบันทึกลงประวัติเครดิตให้ยอดตรงกัน
 * - แพ็กที่ยังไม่เริ่มใช้: วันที่ขยายบวกเพิ่มเข้าอายุ มีผลตอนเริ่มนับจากคลาสแรก
 * - บันทึกทุกครั้งพร้อมเหตุผลและแอดมินที่ทำ และแจ้งลูกค้าในแอป
 */
class PackageExtender
{
    public const MAX_DAYS = 365;

    public function extend(CustomerPackage $package, int $days, string $reason, ?int $userId = null): CustomerPackage
    {
        if ($days < 1 || $days > self::MAX_DAYS) {
            throw new BookingException('invalid_extension_days');
        }

        return DB::transaction(function () use ($package, $days, $reason, $userId) {
            $package = CustomerPackage::with(['customer', 'package'])->lockForUpdate()->findOrFail($package->id);

            if ($package->status === 'cancelled') {
                throw new BookingException('package_cancelled');
            }

            $wasExpired = $package->status === 'expired';
            $newExpiry = $package->expires_at?->copy()->addDays($days);

            // ต่ออายุแพ็กที่หมดไปแล้ว ต้องขยายให้ถึงวันนี้เป็นอย่างน้อย ไม่งั้นต่อไปก็ยังใช้ไม่ได้
            if ($wasExpired && $newExpiry && $newExpiry->lt(now()->startOfDay())) {
                throw new BookingException('extension_too_short');
            }

            $updates = [
                'extended_days' => (int) $package->extended_days + $days,
                'expires_at' => $newExpiry?->toDateString(),
            ];

            if ($wasExpired) {
                $updates['status'] = ($package->isUnlimited() || $package->credit_remaining > 0) ? 'active' : 'used_up';
            }

            $package->update($updates);
            $customer = $package->customer;

            $until = $newExpiry
                ? $newExpiry->format('d/m/Y')
                : __t('นับจากคลาสแรก', 'from first class');

            CreditTransaction::create([
                'customer_id' => $customer->id,
                'customer_package_id' => $package->id,
                'amount' => 0,
                'balance_after' => $customer->totalCredits(),
                'type' => 'admin_adjust',
                'reason_th' => "ขยายอายุแพ็ก +{$days} วัน (ถึง {$until}) — {$reason}",
                'reason_en' => "Package extended +{$days} days (until {$until}) — {$reason}",
                'user_id' => $userId,
            ]);

            // ตอนหมดอายุ ระบบบันทึกเครดิตที่เหลือเป็น "ถูกตัด" ไว้ ต่ออายุแล้วต้องบันทึกคืนให้ยอดตรง
            $remaining = (float) ($package->credit_remaining ?? 0);

            if ($wasExpired && $remaining > 0) {
                CreditTransaction::create([
                    'customer_id' => $customer->id,
                    'customer_package_id' => $package->id,
                    'amount' => $remaining,
                    'balance_after' => $customer->totalCredits(),
                    'type' => 'admin_adjust',
                    'reason_th' => "ต่ออายุแพ็กที่หมดอายุ คืนเครดิตคงเหลือ {$remaining}",
                    'reason_en' => "Expired package renewed — {$remaining} credit(s) restored",
                    'user_id' => $userId,
                ]);
            }

            $this->notifyCustomer($package->fresh(['package']), $days);

            return $package->fresh();
        });
    }

    /** ขยายอย่างน้อยกี่วันถึงจะใช้ได้ถึงวันนี้ (ใช้แนะนำในฟอร์มสำหรับแพ็กที่หมดอายุแล้ว) */
    public static function minimumDaysToRevive(CustomerPackage $package): int
    {
        if (! $package->expires_at || $package->expires_at->gte(now()->startOfDay())) {
            return 1;
        }

        return (int) $package->expires_at->diffInDays(now()->startOfDay());
    }

    private function notifyCustomer(CustomerPackage $package, int $days): void
    {
        $name = $package->package;
        $untilTh = $package->expires_at ? $package->expires_at->locale('th')->isoFormat('D MMM YYYY') : null;
        $untilEn = $package->expires_at ? $package->expires_at->locale('en')->isoFormat('D MMM YYYY') : null;

        Notification::create([
            'customer_id' => $package->customer_id,
            'type' => 'package_extended',
            'title_th' => 'แพ็กเกจของคุณได้รับการขยายเวลา',
            'title_en' => 'Your package has been extended',
            'body_th' => ($name?->name_th ?? 'แพ็กเกจ') . " ขยายเวลาเพิ่ม {$days} วัน"
                . ($untilTh ? " ใช้ได้ถึง {$untilTh}" : ' (เริ่มนับเมื่อใช้คลาสแรก)'),
            'body_en' => ($name?->name_en ?? 'Package') . " extended by {$days} days"
                . ($untilEn ? ", valid until {$untilEn}" : ' (counted from your first class)'),
            'data' => ['customer_package_id' => $package->id],
            'channel' => 'in_app',
            'sent_at' => now(),
        ]);
    }
}
