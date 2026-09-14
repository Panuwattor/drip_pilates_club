<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\CreditTransaction;
use App\Models\Customer;
use App\Models\CustomerPackage;
use Illuminate\Support\Facades\DB;

/**
 * ปรับเครดิตลูกค้าด้วยมือ (พนักงานหน้าร้าน)
 *
 * แยกออกมาจาก BookingService เพราะคนละเรื่องกัน:
 *   - BookingService ตัดเครดิตเพราะ "มีการจอง" ผูกกับ booking เสมอ
 *   - ตัวนี้คือปรับเครดิตล้วนๆ ไม่มี booking เช่น walk-in ชดเชย แก้ยอดผิด
 *
 * หลักการสำคัญ:
 *   - หักเครดิต: ตัดจากแพ็กที่ใกล้หมดอายุก่อน และตัดข้ามหลายแพ็กได้ถ้าใบเดียวไม่พอ
 *   - เพิ่มเครดิต: เติมเข้าแพ็กที่ยังใช้ได้และใกล้หมดอายุที่สุด
 *   - ทุกครั้งบันทึกลง credit_transactions พร้อมชื่อพนักงานที่ทำ
 */
class CreditAdjuster
{
    /** เหตุผลสำเร็จรูป: key => [ฉลากไทย, ทิศทาง (-1 หัก, +1 เพิ่ม), ประเภทที่บันทึก] */
    public const PRESETS = [
        'walk_in' => ['ลูกค้า walk-in มาเรียนสด', -1, 'admin_adjust'],
        'manual_class' => ['หักค่าคลาสที่เรียนไปแล้ว (ไม่ได้จองในระบบ)', -1, 'admin_adjust'],
        'correction_deduct' => ['แก้ยอดที่ผิดพลาด (หักออก)', -1, 'admin_adjust'],
        'compensate_class' => ['ชดเชยคลาสที่ถูกยกเลิก', 1, 'compensate'],
        'compensate_service' => ['ชดเชยจากปัญหาการให้บริการ', 1, 'compensate'],
        'refund_manual' => ['คืนเครดิตให้ลูกค้า', 1, 'refund'],
        'correction_add' => ['แก้ยอดที่ผิดพลาด (เพิ่มคืน)', 1, 'admin_adjust'],
        'promotion' => ['โปรโมชัน/ของแถม', 1, 'admin_adjust'],
        'other' => ['อื่นๆ (ระบุเอง)', 0, 'admin_adjust'],
    ];

    /**
     * หักเครดิตลูกค้า ตัดจากแพ็กที่ใกล้หมดอายุก่อน ข้ามหลายใบได้
     *
     * @return array{deducted: float, packages: array<int, array{code: string, amount: float}>}
     */
    public function deduct(
        Customer $customer,
        float $amount,
        string $reason,
        ?int $userId = null,
        string $type = 'admin_adjust',
    ): array {
        if ($amount <= 0) {
            throw new BookingException('invalid_credit_amount');
        }

        return DB::transaction(function () use ($customer, $amount, $reason, $userId, $type) {
            $packages = CustomerPackage::where('customer_id', $customer->id)
                ->where('status', 'active')
                ->where('type', '!=', 'unlimited')
                ->whereDate('expires_at', '>=', now())
                ->where('credit_remaining', '>', 0)
                ->orderBy('expires_at')
                ->lockForUpdate()
                ->get();

            $available = (float) $packages->sum('credit_remaining');

            if ($available < $amount) {
                throw new BookingException('insufficient_credit');
            }

            $left = $amount;
            $touched = [];

            foreach ($packages as $package) {
                if ($left <= 0) {
                    break;
                }

                $take = min($left, (float) $package->credit_remaining);

                $package->decrement('credit_remaining', $take);
                $package->increment('credit_used', $take);
                $package->refresh();

                if ($package->credit_remaining <= 0) {
                    $package->update(['status' => 'used_up']);
                }

                CreditTransaction::create([
                    'customer_id' => $customer->id,
                    'customer_package_id' => $package->id,
                    'amount' => -$take,
                    'balance_after' => $customer->totalCredits(),
                    'type' => $type,
                    'reason_th' => $reason,
                    'reason_en' => $reason,
                    'user_id' => $userId,
                ]);

                $touched[] = ['code' => $package->code, 'amount' => $take];
                $left -= $take;
            }

            return ['deducted' => $amount, 'packages' => $touched];
        });
    }

    /**
     * เพิ่มเครดิตให้ลูกค้า เติมเข้าแพ็กที่ใกล้หมดอายุที่สุดที่ยังใช้ได้
     *
     * ถ้าระบุ $packageId มาจะเติมใบนั้นตรงๆ (ใช้ตอนแก้ยอดเฉพาะใบ)
     */
    public function add(
        Customer $customer,
        float $amount,
        string $reason,
        ?int $userId = null,
        string $type = 'admin_adjust',
        ?int $packageId = null,
    ): CustomerPackage {
        if ($amount <= 0) {
            throw new BookingException('invalid_credit_amount');
        }

        return DB::transaction(function () use ($customer, $amount, $reason, $userId, $type, $packageId) {
            $query = CustomerPackage::where('customer_id', $customer->id)
                ->where('type', '!=', 'unlimited')
                ->lockForUpdate();

            if ($packageId) {
                $package = $query->whereKey($packageId)->first();
            } else {
                // เติมใบที่ใกล้หมดอายุก่อน ลูกค้าจะได้ใช้ทันก่อนหมดอายุ
                $package = $query->whereIn('status', ['active', 'used_up'])
                    ->whereDate('expires_at', '>=', now())
                    ->orderBy('expires_at')
                    ->first();
            }

            if (! $package) {
                throw new BookingException('no_package_to_credit');
            }

            $package->increment('credit_remaining', $amount);
            $package->decrement('credit_used', $amount);
            $package->refresh();

            // credit_used ติดลบไม่ได้ เกิดได้ถ้าเติมมากกว่าที่เคยใช้
            if ($package->credit_used < 0) {
                $package->update(['credit_used' => 0]);
            }

            // แพ็กที่เคยใช้หมด กลับมาใช้ได้อีกถ้ายังไม่หมดอายุ
            if ($package->status === 'used_up' && $package->credit_remaining > 0) {
                $package->update(['status' => 'active']);
            }

            CreditTransaction::create([
                'customer_id' => $customer->id,
                'customer_package_id' => $package->id,
                'amount' => $amount,
                'balance_after' => $customer->totalCredits(),
                'type' => $type,
                'reason_th' => $reason,
                'reason_en' => $reason,
                'user_id' => $userId,
            ]);

            return $package->fresh();
        });
    }

    /** ฉลากไทยของเหตุผลสำเร็จรูป ใช้ประกอบข้อความที่บันทึก */
    public static function presetLabel(string $key): ?string
    {
        return self::PRESETS[$key][0] ?? null;
    }

    /** ประเภทที่ควรบันทึกลง credit_transactions สำหรับเหตุผลนี้ */
    public static function presetType(string $key): string
    {
        return self::PRESETS[$key][2] ?? 'admin_adjust';
    }
}
