<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\CreditTransaction;
use App\Models\CustomerPackage;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

/**
 * แอดมินจัดการอายุแพ็กให้ลูกค้า รวมทุกอย่างไว้ที่เดียว (ปุ่ม "จัดการอายุแพ็ก" หน้าลูกค้า)
 *
 * มี 2 โหมด:
 *   - addDays()      บวกวันเพิ่มจากเดิม (เดิมคือปุ่ม "ขยายเวลา/ต่ออายุ")
 *   - setValidDays() ตั้งอายุรวมใหม่ ลดลงก็ได้ (เดิมคือปุ่ม "กำหนด/ลดอายุ")
 *
 * เรื่องเงินอยู่ที่เดียว: ตอนแพ็กหมดอายุ ระบบริบเครดิตที่เหลือทิ้ง (ดู BookingService::expirePackages)
 * ทั้งสองโหมดจึงต้องผ่าน reactivateIfRevived()/expireAndForfeit() เหมือนกัน เพื่อให้ยอดเครดิตตรงเสมอ
 *   - ต่ออายุแพ็กที่ตายแล้วให้ถึงวันนี้ -> ฟื้นแพ็ก + คืนเครดิตที่เคยริบ
 *   - ตั้งอายุสั้นจนเลยวันนี้ -> ปิดแพ็กเป็นหมดอายุ + ริบเครดิตที่เหลือ
 */
class PackageAgeManager
{
    public const MAX_DAYS = 3650;

    /** ขยายได้ครั้งละไม่เกินเท่านี้ (โหมดบวกวัน) กันแอดมินพิมพ์พลาดเป็นหมื่นวัน */
    public const MAX_ADD_DAYS = 365;

    /**
     * บวกวันเพิ่มจากอายุเดิม สะสมที่ extended_days
     * ใช้ต่ออายุแพ็กที่หมดไปแล้วได้ (ต้องบวกให้ถึงวันนี้เป็นอย่างน้อย)
     */
    public function addDays(CustomerPackage $package, int $days, string $reason, ?int $userId = null): CustomerPackage
    {
        if ($days < 1 || $days > self::MAX_ADD_DAYS) {
            throw new BookingException('invalid_extension_days');
        }

        return DB::transaction(function () use ($package, $days, $reason, $userId) {
            $package = $this->lock($package);
            $wasExpired = $package->status === 'expired';
            $newExpiry = $package->expires_at?->copy()->addDays($days);

            // ต่ออายุแพ็กที่หมดไปแล้ว ต้องขยายให้ถึงวันนี้เป็นอย่างน้อย ไม่งั้นต่อไปก็ยังใช้ไม่ได้
            if ($wasExpired && $newExpiry && $newExpiry->lt(now()->startOfDay())) {
                throw new BookingException('extension_too_short');
            }

            $package->update([
                'extended_days' => (int) $package->extended_days + $days,
                'expires_at' => $newExpiry?->toDateString(),
            ]);

            if ($wasExpired) {
                $this->reactivateIfRevived($package->refresh());
            }

            return $this->finish($package, $days, $reason, $userId, mode: 'add');
        });
    }

    /**
     * ตั้งอายุรวมของแพ็กใหม่ทั้งก้อน (นับจากคลาสแรก) ลดลงก็ได้
     *
     * ตั้ง valid_days = จำนวนวันที่ต้องการ แล้วล้าง extended_days/frozen_days_used = 0
     * เพื่อให้ totalValidDays() เท่ากับที่ตั้งเป๊ะ ไม่มีวันเก่าค้างมาบวก
     */
    public function setValidDays(CustomerPackage $package, int $days, string $reason, ?int $userId = null): CustomerPackage
    {
        if ($days < 1 || $days > self::MAX_DAYS) {
            throw new BookingException('invalid_validity_days');
        }

        return DB::transaction(function () use ($package, $days, $reason, $userId) {
            $package = $this->lock($package);

            // แพ็กเก่าแบบนับจากวันซื้อ (valid_days = null) ไม่รองรับ ให้ใช้โหมดบวกวันแทน
            if (! $package->startsOnFirstUse()) {
                throw new BookingException('validity_not_supported');
            }

            $package->update([
                'valid_days' => $days,
                'extended_days' => 0,
                'frozen_days_used' => 0,
            ]);

            if ($package->isActivated()) {
                $this->applySetToActivated($package);
            } else {
                // ยังไม่เริ่มใช้ อายุใหม่มีผลผ่าน valid_days จะคำนวณตอนใช้คลาสแรก
                $package->refresh()->syncActivation();
            }

            return $this->finish($package->refresh(), $days, $reason, $userId, mode: 'set');
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

    private function lock(CustomerPackage $package): CustomerPackage
    {
        $package = CustomerPackage::with(['customer', 'package'])->lockForUpdate()->findOrFail($package->id);

        if ($package->status === 'cancelled') {
            throw new BookingException('package_cancelled');
        }

        return $package;
    }

    /**
     * โหมดตั้งอายุ: คำนวณวันหมดอายุใหม่ให้แพ็กที่เริ่มใช้แล้ว
     * ถ้าอายุใหม่สั้นจนเลยวันนี้ ให้ปิดแพ็ก+ริบเครดิต ถ้ายังไม่เลยและเคยตายอยู่ก็ฟื้นคืน
     */
    private function applySetToActivated(CustomerPackage $package): void
    {
        $newExpiry = $package->starts_at->copy()->addDays($package->totalValidDays());
        $package->update(['expires_at' => $newExpiry->toDateString()]);
        $package->refresh();

        if ($newExpiry->gte(now()->startOfDay())) {
            $this->reactivateIfRevived($package);
        } else {
            $this->expireAndForfeit($package);
        }
    }

    /**
     * ฟื้นแพ็กที่เคยหมดอายุให้กลับมาใช้ได้ พร้อมคืนเครดิตที่ถูกริบตอนหมดอายุ
     * (ตอนหมดอายุ ระบบบันทึกเครดิตที่เหลือเป็น "ถูกตัด" ไว้ คืนแล้วต้องบันทึกคืนให้ยอดตรง)
     */
    private function reactivateIfRevived(CustomerPackage $package): void
    {
        if ($package->status !== 'expired') {
            return;
        }

        $remaining = (float) ($package->credit_remaining ?? 0);

        $package->update([
            'status' => ($package->isUnlimited() || $remaining > 0) ? 'active' : 'used_up',
        ]);

        if ($remaining > 0) {
            CreditTransaction::create([
                'customer_id' => $package->customer_id,
                'customer_package_id' => $package->id,
                'amount' => $remaining,
                'balance_after' => $package->customer->totalCredits(),
                'type' => 'admin_adjust',
                'reason_th' => "ต่ออายุแพ็กที่หมดอายุ คืนเครดิตคงเหลือ {$remaining}",
                'reason_en' => "Expired package renewed — {$remaining} credit(s) restored",
            ]);
        }
    }

    /**
     * ปิดแพ็กเป็นหมดอายุและริบเครดิตที่เหลือ (เหมือน BookingService::expirePackages)
     * ใช้ตอนตั้งอายุสั้นจนวันหมดอายุใหม่เลยวันนี้ไปแล้ว
     */
    private function expireAndForfeit(CustomerPackage $package): void
    {
        if (! in_array($package->status, ['active', 'frozen', 'used_up'], true)) {
            return;
        }

        $remaining = (float) ($package->credit_remaining ?? 0);
        $package->update(['status' => 'expired']);

        if ($remaining > 0) {
            CreditTransaction::create([
                'customer_id' => $package->customer_id,
                'customer_package_id' => $package->id,
                'amount' => -$remaining,
                'balance_after' => $package->customer->totalCredits(),
                'type' => 'expire',
                'reason_th' => 'แพ็กหมดอายุ เครดิตคงเหลือ ' . $remaining . ' ถูกตัด',
                'reason_en' => 'Package expired — ' . $remaining . ' credit(s) forfeited',
            ]);
        }
    }

    /** บันทึกประวัติ + แจ้งลูกค้า (ทำเหมือนกันทั้งสองโหมด ต่างแค่ข้อความ) */
    private function finish(CustomerPackage $package, int $days, string $reason, ?int $userId, string $mode): CustomerPackage
    {
        $package = $package->fresh(['customer', 'package']);
        $customer = $package->customer;

        $until = $package->expires_at
            ? $package->expires_at->format('d/m/Y')
            : __t('นับจากคลาสแรก', 'from first class');

        [$reasonTh, $reasonEn] = $mode === 'set'
            ? ["ตั้งอายุแพ็กเป็น {$days} วัน (ถึง {$until}) — {$reason}",
                "Package validity set to {$days} days (until {$until}) — {$reason}"]
            : ["ขยายอายุแพ็ก +{$days} วัน (ถึง {$until}) — {$reason}",
                "Package extended +{$days} days (until {$until}) — {$reason}"];

        CreditTransaction::create([
            'customer_id' => $customer->id,
            'customer_package_id' => $package->id,
            'amount' => 0,
            'balance_after' => $customer->totalCredits(),
            'type' => 'admin_adjust',
            'reason_th' => $reasonTh,
            'reason_en' => $reasonEn,
            'user_id' => $userId,
        ]);

        $this->notifyCustomer($package, $days, $mode);

        return $package->fresh();
    }

    private function notifyCustomer(CustomerPackage $package, int $days, string $mode): void
    {
        $name = $package->package;
        $untilTh = $package->expires_at ? $package->expires_at->locale('th')->isoFormat('D MMM YYYY') : null;
        $untilEn = $package->expires_at ? $package->expires_at->locale('en')->isoFormat('D MMM YYYY') : null;

        if ($mode === 'set') {
            $titleTh = 'อายุแพ็กเกจของคุณถูกปรับ';
            $titleEn = 'Your package validity was updated';
            $bodyTh = ($name?->name_th ?? 'แพ็กเกจ') . " มีอายุ {$days} วัน"
                . ($untilTh ? " ใช้ได้ถึง {$untilTh}" : ' (เริ่มนับเมื่อใช้คลาสแรก)');
            $bodyEn = ($name?->name_en ?? 'Package') . " is valid for {$days} days"
                . ($untilEn ? ", until {$untilEn}" : ' (counted from your first class)');
        } else {
            $titleTh = 'แพ็กเกจของคุณได้รับการขยายเวลา';
            $titleEn = 'Your package has been extended';
            $bodyTh = ($name?->name_th ?? 'แพ็กเกจ') . " ขยายเวลาเพิ่ม {$days} วัน"
                . ($untilTh ? " ใช้ได้ถึง {$untilTh}" : ' (เริ่มนับเมื่อใช้คลาสแรก)');
            $bodyEn = ($name?->name_en ?? 'Package') . " extended by {$days} days"
                . ($untilEn ? ", valid until {$untilEn}" : ' (counted from your first class)');
        }

        Notification::create([
            'customer_id' => $package->customer_id,
            'type' => $mode === 'set' ? 'package_validity_set' : 'package_extended',
            'title_th' => $titleTh,
            'title_en' => $titleEn,
            'body_th' => $bodyTh,
            'body_en' => $bodyEn,
            'data' => ['customer_package_id' => $package->id],
            'channel' => 'in_app',
            'sent_at' => now(),
        ]);
    }
}
