<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| งานอัตโนมัติ
|--------------------------------------------------------------------------
| ต้องตั้ง cron บนเซิร์ฟเวอร์ให้เรียกทุกนาที:
|   * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
*/

// ปิดคลาสที่จบแล้วไม่ได้เช็คอิน + ปิดแพ็กหมดอายุ ทุกชั่วโมง
Schedule::command('bookings:close-past')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// สร้างรอบเรียนล่วงหน้าจากแม่แบบตารางประจำสัปดาห์ วันละครั้งตอนตีสาม
Schedule::command('classes:generate')
    ->dailyAt('03:00')
    ->withoutOverlapping();

// เตือนลูกค้าก่อนคลาสเริ่ม ทุก 15 นาที (กันส่งซ้ำในตัว) เพื่อลด no-show
Schedule::command('bookings:send-reminders')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();
