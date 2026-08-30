<?php

namespace App\Services;

use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\Holiday;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * สร้างรอบเรียนจริง (class_sessions) จากแม่แบบตารางประจำสัปดาห์ (class_schedules)
 *
 * รันซ้ำได้ปลอดภัย เพราะมี unique index (class_schedule_id, start_at)
 * รอบที่มีอยู่แล้วจะถูกข้าม ไม่ทับของเดิมที่แอดมินแก้ไว้
 */
class SessionGenerator
{
    public function __construct(
        private int $created = 0,
        private int $skipped = 0,
        private int $holidaySkipped = 0,
    ) {}

    public function generate(?CarbonImmutable $from = null, ?int $days = null): array
    {
        $from ??= CarbonImmutable::today();
        $days ??= (int) Setting::get('session_generate_days_ahead', 90);
        $until = $from->addDays($days);

        $closeMinutes = (int) Setting::get('booking_close_minutes_before', 30);

        $schedules = ClassSchedule::query()
            ->where('is_active', true)
            ->with('classType')
            ->get();

        // ดึงวันหยุดมาไว้ในหน่วยความจำก่อน จะได้ไม่ query ซ้ำในลูป
        $holidays = Holiday::query()
            ->whereBetween('date', [$from->toDateString(), $until->toDateString()])
            ->get()
            ->groupBy(fn ($h) => $h->date->toDateString());

        foreach ($schedules as $schedule) {
            $this->generateForSchedule($schedule, $from, $until, $holidays, $closeMinutes);
        }

        return [
            'created' => $this->created,
            'skipped' => $this->skipped,
            'holiday_skipped' => $this->holidaySkipped,
        ];
    }

    private function generateForSchedule(
        ClassSchedule $schedule,
        CarbonImmutable $from,
        CarbonImmutable $until,
        $holidays,
        int $closeMinutes,
    ): void {
        // จำกัดช่วงตามอายุของแม่แบบ
        $start = $from;
        if ($schedule->effective_from) {
            $effectiveFrom = CarbonImmutable::parse($schedule->effective_from);
            $start = $effectiveFrom->gt($start) ? $effectiveFrom : $start;
        }

        $end = $until;
        if ($schedule->effective_until) {
            $effectiveUntil = CarbonImmutable::parse($schedule->effective_until);
            $end = $effectiveUntil->lt($end) ? $effectiveUntil : $end;
        }

        if ($start->gt($end)) {
            return;
        }

        // เลื่อนไปยังวันแรกที่ตรงกับ day_of_week ของแม่แบบ
        $date = $start;
        while ((int) $date->dayOfWeek !== (int) $schedule->day_of_week) {
            $date = $date->addDay();

            if ($date->gt($end)) {
                return;
            }
        }

        [$hour, $minute] = array_map('intval', explode(':', $schedule->start_time));

        while ($date->lte($end)) {
            $startAt = $date->setTime($hour, $minute);

            if ($this->isClosed($holidays, $date, $schedule->branch_id, $startAt)) {
                $this->holidaySkipped++;
                $date = $date->addWeek();

                continue;
            }

            $endAt = $startAt->addMinutes($schedule->duration_min);

            $existing = ClassSession::where('class_schedule_id', $schedule->id)
                ->where('start_at', $startAt->toDateTimeString())
                ->exists();

            if ($existing) {
                $this->skipped++;
            } else {
                ClassSession::create([
                    'class_schedule_id' => $schedule->id,
                    'branch_id' => $schedule->branch_id,
                    'room_id' => $schedule->room_id,
                    'class_type_id' => $schedule->class_type_id,
                    'trainer_id' => $schedule->trainer_id,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'capacity' => $schedule->capacity,
                    'credit_cost' => $schedule->credit_cost,
                    'status' => 'scheduled',
                    'booking_closes_at' => $startAt->subMinutes($closeMinutes),
                ]);

                $this->created++;
            }

            $date = $date->addWeek();
        }
    }

    private function isClosed($holidays, CarbonImmutable $date, int $branchId, CarbonImmutable $startAt): bool
    {
        $onDate = $holidays->get($date->toDateString());

        if (! $onDate) {
            return false;
        }

        foreach ($onDate as $holiday) {
            // branch_id = null หมายถึงหยุดทุกสาขา
            if ($holiday->branch_id !== null && $holiday->branch_id !== $branchId) {
                continue;
            }

            if ($holiday->is_closed_all_day) {
                return true;
            }

            $time = $startAt->format('H:i:s');

            if ($holiday->closed_from && $holiday->closed_until
                && $time >= $holiday->closed_from && $time < $holiday->closed_until) {
                return true;
            }
        }

        return false;
    }
}
