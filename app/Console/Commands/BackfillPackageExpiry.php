<?php

namespace App\Console\Commands;

use App\Models\CustomerPackage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * แปลงแพ็กที่ขายไปก่อนเปลี่ยนกติกา ให้อายุนับจากคลาสแรกแบบเดียวกับแพ็กใหม่
 *
 * รันครั้งเดียวหลัง migrate ค่าเริ่มต้นเป็นโหมดดูตัวอย่าง ต้องใส่ --apply ถึงจะบันทึกจริง
 * รันซ้ำได้ปลอดภัย แพ็กที่แปลงแล้ว (valid_days ไม่เป็น null) จะถูกข้าม
 *
 * กติกา:
 *  - อายุแพ็ก = จำนวนวันที่ลูกค้าได้ตอนซื้อจริง (expires - starts - วันฟรีซ) ไม่อ่านจาก package ปัจจุบัน
 *    เผื่อแอดมินแก้แพ็กทีหลัง ลูกค้าเก่าต้องได้ตามที่ซื้อ
 *  - เคยใช้แล้ว: วันเริ่ม = วันที่ของคลาสแรก (ปกติหลังวันซื้อ วันหมดอายุจึงยืดออกไป)
 *  - ยังไม่เคยใช้: กลับเป็น "ยังไม่เริ่มใช้" นับเมื่อจองคลาสแรก
 *  - ถ้าคำนวณแล้ววันหมดอายุสั้นลง ไม่แตะแพ็กนั้น ลูกค้าต้องไม่เสียสิทธิ์
 *  - แพ็กที่หมดอายุ/ยกเลิกไปแล้วไม่แตะ
 */
class BackfillPackageExpiry extends Command
{
    protected $signature = 'packages:backfill-first-class-expiry
                            {--apply : บันทึกจริง (ไม่ใส่ = ดูตัวอย่างอย่างเดียว)}';

    protected $description = 'แปลงแพ็กเก่าให้อายุเริ่มนับจากคลาสแรก';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $packages = CustomerPackage::with(['customer', 'package'])
            ->whereIn('status', ['active', 'frozen', 'used_up'])
            ->whereNull('valid_days')
            ->whereNotNull('starts_at')
            ->whereNotNull('expires_at')
            ->orderBy('id')
            ->get();

        if ($packages->isEmpty()) {
            $this->info('ไม่มีแพ็กที่ต้องแปลง');

            return self::SUCCESS;
        }

        $rows = [];
        $counts = ['เริ่มนับใหม่จากคลาสแรก' => 0, 'ยังไม่เริ่มใช้' => 0, 'ข้าม (วันหมดอายุจะสั้นลง)' => 0];

        // ทำในทรานแซกชันเดียว โหมดดูตัวอย่างจะ rollback ทิ้งตอนจบ
        DB::beginTransaction();

        try {
            foreach ($packages as $cp) {
                $oldStart = $cp->starts_at->copy();
                $oldExpiry = $cp->expires_at->copy();

                $validDays = max(1, (int) $oldStart->diffInDays($oldExpiry) - (int) $cp->frozen_days_used);

                $cp->valid_days = $validDays;
                $cp->syncActivation();
                $cp->refresh();

                if ($cp->expires_at && $cp->expires_at->lt($oldExpiry)) {
                    // คืนค่าเดิม ปล่อยให้นับแบบเก่าต่อไป
                    $cp->update(['valid_days' => null, 'starts_at' => $oldStart, 'expires_at' => $oldExpiry]);
                    $result = 'ข้าม (วันหมดอายุจะสั้นลง)';
                } else {
                    $result = $cp->isActivated() ? 'เริ่มนับใหม่จากคลาสแรก' : 'ยังไม่เริ่มใช้';
                }

                $counts[$result]++;

                $rows[] = [
                    $cp->code,
                    $cp->customer?->full_name,
                    $cp->package?->name,
                    $cp->status,
                    $validDays,
                    $oldExpiry->format('d/m/Y'),
                    $cp->expires_at?->format('d/m/Y') ?? '— ยังไม่เริ่ม',
                    $result,
                ];
            }

            $this->table(
                ['รหัสแพ็ก', 'ลูกค้า', 'แพ็ก', 'สถานะ', 'อายุ (วัน)', 'หมดอายุเดิม', 'หมดอายุใหม่', 'ผล'],
                $rows,
            );

            foreach ($counts as $label => $n) {
                $this->line("{$label}: {$n}");
            }

            if ($apply) {
                DB::commit();
                $this->info("บันทึกแล้ว {$packages->count()} แพ็ก");
            } else {
                DB::rollBack();
                $this->warn('โหมดดูตัวอย่าง ยังไม่ได้บันทึก ถ้าถูกต้องให้รันซ้ำพร้อม --apply');
            }
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        return self::SUCCESS;
    }
}
