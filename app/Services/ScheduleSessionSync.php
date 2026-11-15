<?php

namespace App\Services;

use App\Models\ClassSchedule;
use App\Models\ClassSession;
use Carbon\CarbonImmutable;

/**
 * ส่งการแก้ไขตารางประจำสัปดาห์ไปยังรอบเรียนในอนาคตที่สร้างไว้แล้ว
 *
 * - ครู / ห้อง / ประเภทคลาส / ที่นั่ง / เครดิต / ความยาว: ทุกรอบที่ยังไม่ถึงจะถูกตั้งให้ตรงกับตาราง
 *   รวมถึงรอบที่มีคนจองแล้ว (ครูสอนแทนรายรอบอยู่ในช่อง substitute_trainer_id จึงไม่โดนทับ)
 *   ไม่ได้เช็คว่า "รอบนี้เคยแก้รายรอบไหม" เพราะแยกไม่ออกจากรอบที่ค้างค่าเก่าจากการแก้ตารางครั้งก่อน
 * - ที่นั่งไม่ลดต่ำกว่าจำนวนคนที่จองแล้ว
 * - เปลี่ยนวัน / เวลา / สาขา: รอบที่ยังไม่มีการจองเลยจะถูกลบแล้วสร้างใหม่ตามเวลาใหม่
 *   รอบที่มีการจองแล้วคงไว้เวลาเดิม (ไม่ย้ายเวลาลูกค้าเอง) แล้วรายงานจำนวนให้แอดมินไปจัดการ
 * - ปิดตาราง / หดช่วงวันที่ใช้: ลบรอบที่ไม่มีการจองซึ่งอยู่นอกช่วงใหม่
 *
 * "ไม่มีการจอง" = ไม่มีแถวใน bookings เลย (รวมที่ยกเลิกแล้ว) เพราะ bookings ลบตามรอบแบบ cascade
 * ถ้าลบรอบที่มีประวัติยกเลิกจะทำให้ประวัติลูกค้าหายไป
 */
class ScheduleSessionSync
{
    private const SIMPLE_FIELDS = ['trainer_id', 'room_id', 'class_type_id', 'credit_cost'];

    public function __construct(
        private BookingService $bookings,
        private SessionGenerator $generator,
    ) {}

    /**
     * @param  array  $old  ค่าของตารางก่อนแก้ ($schedule->getOriginal() ก่อน update)
     * @return array{updated:int, moved:int, removed:int, kept:int, created:int}
     */
    public function sync(ClassSchedule $schedule, array $old): array
    {
        $removed = 0;
        $moved = 0;

        $timeChanged = (int) $old['day_of_week'] !== (int) $schedule->day_of_week
            || substr((string) $old['start_time'], 0, 5) !== substr((string) $schedule->start_time, 0, 5)
            || (int) $old['branch_id'] !== (int) $schedule->branch_id;

        // 1) ลบรอบว่างที่เวลาไม่ตรงกับตารางใหม่แล้ว (จะสร้างใหม่ในข้อ 3)
        foreach ($this->future($schedule)->whereDoesntHave('bookings')->get() as $session) {
            if ($timeChanged || ! $this->withinSchedule($schedule, $session)) {
                $session->delete();
                $timeChanged ? $moved++ : $removed++;
            }
        }

        // 2) แก้ค่าในรอบที่เหลือ
        $updated = 0;
        $kept = 0;
        foreach ($this->future($schedule)->get() as $session) {
            $changes = [];

            foreach (self::SIMPLE_FIELDS as $field) {
                if ($this->differs($session->$field, $schedule->$field)) {
                    $changes[$field] = $schedule->$field;
                }
            }

            $capacity = max((int) $schedule->capacity, (int) $session->booked_count);
            if ((int) $session->capacity !== $capacity) {
                $changes['capacity'] = $capacity;
            }

            if ((int) $session->start_at->diffInMinutes($session->end_at) !== (int) $schedule->duration_min) {
                $changes['end_at'] = $session->start_at->copy()->addMinutes($schedule->duration_min);
            }

            if ($changes) {
                $session->update($changes);
                $updated++;

                // เพิ่มที่นั่งแล้วเลื่อนคิวสำรองขึ้นมาจนเต็ม (คืนค่า null เมื่อเต็มหรือหมดคิว)
                if ($session->waitlist_count > 0) {
                    while ($this->bookings->promoteFromWaitlist($session)) {
                    }
                }
            }

            if ($timeChanged) {
                $kept++;
            }
        }

        // 3) สร้างรอบใหม่ตามตารางที่แก้แล้ว
        $created = $this->generator->generate(CarbonImmutable::today())['created'];

        return compact('updated', 'moved', 'removed', 'kept', 'created');
    }

    private function future(ClassSchedule $schedule)
    {
        return ClassSession::where('class_schedule_id', $schedule->id)
            ->where('status', 'scheduled')
            ->where('start_at', '>', now());
    }

    private function withinSchedule(ClassSchedule $schedule, ClassSession $session): bool
    {
        if (! $schedule->is_active) {
            return false;
        }

        $date = $session->start_at->toDateString();

        if ($schedule->effective_from && $date < CarbonImmutable::parse($schedule->effective_from)->toDateString()) {
            return false;
        }

        if ($schedule->effective_until && $date > CarbonImmutable::parse($schedule->effective_until)->toDateString()) {
            return false;
        }

        return true;
    }

    /** เทียบแบบไม่สนชนิดข้อมูล ("1.50" กับ 1.5 ถือว่าเท่ากัน, null ไม่เท่ากับ 0) */
    private function differs($a, $b): bool
    {
        if ($a === null || $b === null) {
            return $a !== $b;
        }

        return (string) (float) $a !== (string) (float) $b;
    }
}
