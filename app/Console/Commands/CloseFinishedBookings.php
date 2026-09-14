<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\BookingService;
use Illuminate\Console\Command;

/**
 * ปิดงานค้างประจำวัน ให้ระบบทำเองไม่ต้องรอแอดมิน
 *  - คลาสที่จบแล้วไม่มีใครเช็คอิน -> no_show (เครดิตตัดไปตั้งแต่จองแล้ว)
 *  - คิวสำรองที่ไม่ได้ที่นั่ง -> ปิดทิ้ง ไม่ตัดเครดิต
 *  - แพ็กที่เลยวันหมดอายุ -> expired พร้อมบันทึกเครดิตที่ถูกริบ
 *
 * แอดมินย้อนแก้รายการที่ระบบปิดไปได้ที่หน้าการจอง (ปุ่มย้อนสถานะ)
 */
class CloseFinishedBookings extends Command
{
    protected $signature = 'bookings:close-past
                            {--minutes= : ปิดหลังคลาสจบกี่นาที (ค่าเริ่มต้นอ่านจาก settings)}
                            {--dry-run : ดูจำนวนเฉยๆ ไม่บันทึกจริง}';

    protected $description = 'ปิดคลาสที่จบแล้วเป็นไม่มาเรียน และปิดแพ็กที่หมดอายุ';

    public function handle(BookingService $bookings): int
    {
        if (! Setting::get('auto_no_show_enabled', true)) {
            $this->warn('ปิดคลาสอัตโนมัติถูกปิดใช้งานอยู่ในการตั้งค่า ข้ามขั้นตอน no-show');
            $expired = $bookings->expirePackages();
            $this->info("ปิดแพ็กหมดอายุ {$expired} แพ็ก");

            return self::SUCCESS;
        }

        $minutes = $this->option('minutes') ? (int) $this->option('minutes') : null;

        if ($this->option('dry-run')) {
            $this->line('โหมดทดลอง ไม่บันทึกจริง');
            $this->table(
                ['รายการที่จะถูกปิด'],
                [[$this->pendingCount($minutes)]]
            );

            return self::SUCCESS;
        }

        $result = $bookings->closePastBookings($minutes);
        $expired = $bookings->expirePackages();

        $this->table(
            ['ไม่มาเรียน', 'คิวสำรองที่ปิด', 'แพ็กหมดอายุ'],
            [[$result['no_show'], $result['waitlist_expired'], $expired]]
        );

        return self::SUCCESS;
    }

    private function pendingCount(?int $minutes): int
    {
        $grace = $minutes ?? (int) Setting::get('auto_no_show_after_minutes', 120);

        return \App\Models\Booking::where('status', 'confirmed')
            ->whereHas('classSession', fn ($q) => $q
                ->where('end_at', '<', now()->subMinutes($grace))
                ->where('status', '!=', 'cancelled'))
            ->count();
    }
}
