<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\BookingService;
use Illuminate\Console\Command;

/**
 * ส่งแจ้งเตือนให้ลูกค้าก่อนคลาสเริ่ม เพื่อลด no-show
 *
 * ระยะเวลาเตือนล่วงหน้าอ่านจาก settings (class_reminder_hours, ค่าเริ่มต้น 12 ชม.)
 * ตั้งให้รันบ่อยได้ (เช่นทุก 15 นาที) — ระบบกันส่งซ้ำให้เอง
 */
class SendClassReminders extends Command
{
    protected $signature = 'bookings:send-reminders
                            {--hours= : เตือนล่วงหน้ากี่ชั่วโมง (ค่าเริ่มต้นอ่านจาก settings)}';

    protected $description = 'ส่งแจ้งเตือนก่อนคลาสเริ่มให้ลูกค้าที่จองไว้';

    public function handle(BookingService $bookings): int
    {
        if (! Setting::get('class_reminder_enabled', true)) {
            $this->warn('การเตือนก่อนคลาสถูกปิดใช้งานอยู่ในการตั้งค่า');

            return self::SUCCESS;
        }

        $hours = $this->option('hours') ? (int) $this->option('hours') : null;

        $sent = $bookings->sendClassReminders($hours);

        $this->info("ส่งแจ้งเตือนก่อนคลาส {$sent} รายการ");

        return self::SUCCESS;
    }
}
