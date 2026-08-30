<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ClassSchedule;
use App\Models\ClassType;
use App\Models\Room;
use App\Models\Trainer;
use Illuminate\Database\Seeder;

class ClassScheduleSeeder extends Seeder
{
    /**
     * ตารางเรียนประจำสัปดาห์ สาขาอารีย์
     * Trio เป็นคลาสกลุ่มจึงตั้งเป็นรอบประจำ ส่วน Private/Duo ปกติจะนัดเป็นรอบๆ
     * แต่ใส่รอบตัวอย่างไว้ให้เห็นภาพในปฏิทิน แอดมินปรับได้ในหน้าแอดมิน
     *
     * สาขาสีลมยังไม่มีตาราง รอลูกค้าแจ้งข้อมูลกลับ
     */
    public function run(): void
    {
        $aree = Branch::where('code', 'aree')->firstOrFail();
        $types = ClassType::pluck('id', 'code');
        $trainers = Trainer::pluck('id', 'code');

        $rooms = Room::where('branch_id', $aree->id)->pluck('id', 'name_en');
        $studio1 = $rooms['Reformer Studio 1'];
        $studio2 = $rooms['Reformer Studio 2'];
        $treatment = $rooms['Treatment Room'];

        // [ห้อง, ประเภทคลาส, ครู, วัน(0=อา..6=ส), เวลา]
        $schedules = [
            // จันทร์
            [$studio2, 'trio-reformer',   'TR-02', 1, '08:00'],
            [$studio2, 'trio-reformer',   'TR-03', 1, '09:15'],
            [$studio1, 'private-pilates', 'TR-01', 1, '10:30'],
            [$studio2, 'trio-reformer',   'TR-03', 1, '17:30'],
            [$studio2, 'trio-reformer',   'TR-02', 1, '18:45'],
            [$studio1, 'duo-pilates',     'TR-01', 1, '19:00'],

            // อังคาร
            [$studio2, 'trio-reformer',   'TR-03', 2, '08:00'],
            [$studio1, 'private-pilates', 'TR-01', 2, '09:15'],
            [$treatment, 'revive-60',     'TR-04', 2, '11:00'],
            [$studio2, 'trio-reformer',   'TR-02', 2, '17:30'],
            [$studio2, 'trio-reformer',   'TR-03', 2, '18:45'],

            // พุธ
            [$studio2, 'trio-reformer',   'TR-02', 3, '08:00'],
            [$studio1, 'duo-pilates',     'TR-01', 3, '09:15'],
            [$studio1, 'private-pilates', 'TR-04', 3, '10:30'],
            [$studio2, 'trio-reformer',   'TR-03', 3, '18:00'],
            [$studio2, 'trio-reformer',   'TR-02', 3, '19:15'],

            // พฤหัสบดี
            [$studio2, 'trio-reformer',   'TR-03', 4, '08:00'],
            [$studio1, 'private-pilates', 'TR-01', 4, '09:15'],
            [$treatment, 'revive-30',     'TR-04', 4, '11:00'],
            [$studio2, 'trio-reformer',   'TR-02', 4, '17:30'],
            [$studio2, 'trio-reformer',   'TR-03', 4, '18:45'],

            // ศุกร์
            [$studio2, 'trio-reformer',   'TR-02', 5, '08:00'],
            [$studio1, 'duo-pilates',     'TR-01', 5, '09:15'],
            [$studio2, 'trio-reformer',   'TR-03', 5, '18:00'],

            // เสาร์
            [$studio2, 'trio-reformer',   'TR-01', 6, '09:00'],
            [$studio2, 'trio-reformer',   'TR-02', 6, '10:15'],
            [$studio1, 'private-pilates', 'TR-04', 6, '11:30'],
            [$studio2, 'trio-reformer',   'TR-03', 6, '14:00'],

            // อาทิตย์
            [$studio2, 'trio-reformer',   'TR-01', 0, '09:00'],
            [$studio2, 'trio-reformer',   'TR-03', 0, '10:15'],
            [$studio1, 'duo-pilates',     'TR-02', 0, '11:30'],
        ];

        foreach ($schedules as [$roomId, $typeCode, $trainerCode, $dow, $time]) {
            $type = ClassType::find($types[$typeCode]);

            ClassSchedule::updateOrCreate(
                [
                    'branch_id' => $aree->id,
                    'room_id' => $roomId,
                    'class_type_id' => $types[$typeCode],
                    'day_of_week' => $dow,
                    'start_time' => $time . ':00',
                ],
                [
                    'trainer_id' => $trainers[$trainerCode],
                    'duration_min' => $type->duration_min,
                    'capacity' => $type->default_capacity,
                    'credit_cost' => $type->credit_cost,
                    'effective_from' => now()->startOfMonth()->toDateString(),
                    'effective_until' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
