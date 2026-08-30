<?php

namespace App\Console\Commands;

use App\Services\SessionGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class GenerateClassSessions extends Command
{
    protected $signature = 'classes:generate
                            {--days= : สร้างล่วงหน้ากี่วัน (ค่าเริ่มต้นอ่านจาก settings)}
                            {--from= : เริ่มจากวันที่ (YYYY-MM-DD)}';

    protected $description = 'สร้างรอบเรียนจากแม่แบบตารางประจำสัปดาห์';

    public function handle(SessionGenerator $generator): int
    {
        $from = $this->option('from')
            ? CarbonImmutable::parse($this->option('from'))
            : CarbonImmutable::today();

        $days = $this->option('days') ? (int) $this->option('days') : null;

        $this->info("กำลังสร้างรอบเรียนจาก {$from->toDateString()} ...");

        $result = $generator->generate($from, $days);

        $this->table(
            ['สร้างใหม่', 'มีอยู่แล้ว', 'ข้ามวันหยุด'],
            [[$result['created'], $result['skipped'], $result['holiday_skipped']]]
        );

        return self::SUCCESS;
    }
}
