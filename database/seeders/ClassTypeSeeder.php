<?php

namespace Database\Seeders;

use App\Models\ClassType;
use Illuminate\Database\Seeder;

class ClassTypeSeeder extends Seeder
{
    /**
     * อิงจาก Services Menu จริงของลูกค้า (สาขาอารีย์)
     *  - Private / Duo / Trio ต่างกันที่จำนวนคนต่อคลาส 1/2/3
     *  - Revive Your Body เป็นบริการนวด ขายเป็นนาที ไม่ใช่คลาสพิลาทิส
     */
    public function run(): void
    {
        $types = [
            [
                'code' => 'private-pilates',
                'name_th' => 'ไพรเวท พิลาทิส',
                'name_en' => 'Private Pilates',
                'description_th' => 'คลาสส่วนตัวตัวต่อตัวกับเทรนเนอร์ ออกแบบโปรแกรมเฉพาะบุคคล',
                'description_en' => 'One-on-one session with your trainer, programmed entirely around you.',
                'suitable_for_th' => 'ทุกระดับ เหมาะกับผู้ที่ต้องการดูแลใกล้ชิด หรือมีอาการบาดเจ็บ',
                'suitable_for_en' => 'All levels — ideal for close guidance or injury recovery',
                'level' => 'all',
                'equipment_type' => 'reformer',
                'duration_min' => 50,
                'default_capacity' => 1,
                'credit_cost' => 1,
                'color' => '#8A6112',
            ],
            [
                'code' => 'duo-pilates',
                'name_th' => 'ดูโอ พิลาทิส',
                'name_en' => 'Duo Pilates',
                'description_th' => 'คลาสสำหรับ 2 คน มาคู่กับเพื่อนหรือคนในครอบครัว ดูแลใกล้ชิดเหมือนไพรเวท',
                'description_en' => 'A two-person session — train alongside a friend or partner with private-level attention.',
                'suitable_for_th' => 'ทุกระดับ มากันเป็นคู่',
                'suitable_for_en' => 'All levels — for pairs',
                'level' => 'all',
                'equipment_type' => 'reformer',
                'duration_min' => 50,
                'default_capacity' => 2,
                'credit_cost' => 1,
                'color' => '#7C93B8',
            ],
            [
                'code' => 'trio-reformer',
                'name_th' => 'ทรีโอ รีฟอร์มเมอร์ กรุ๊ปคลาส',
                'name_en' => 'Trio Reformer Group Class',
                'description_th' => 'คลาสกลุ่มเล็ก จำกัดเพียง 3 คนต่อคลาส ได้รับการดูแลทั่วถึง',
                'description_en' => 'A small group class capped at just three people, so everyone still gets attention.',
                'suitable_for_th' => 'ทุกระดับ',
                'suitable_for_en' => 'All levels',
                'level' => 'all',
                'equipment_type' => 'reformer',
                'duration_min' => 50,
                'default_capacity' => 3,
                'credit_cost' => 1,
                'color' => '#5E7699',
            ],

            // บริการนวด/ฟื้นฟู แยกตามระยะเวลา เพราะราคาต่างกัน (690฿ / 1,290฿)
            [
                'code' => 'revive-30',
                'name_th' => 'รีไววฟ์ ยัวร์ บอดี้ 30 นาที',
                'name_en' => 'Revive Your Body 30 mins',
                'description_th' => 'บริการฟื้นฟูร่างกาย คลายกล้ามเนื้อ 30 นาที',
                'description_en' => 'A 30-minute body recovery and muscle release session.',
                'suitable_for_th' => 'ทุกคน เสริมกับคลาสพิลาทิส',
                'suitable_for_en' => 'Everyone — pairs well with your Pilates sessions',
                'level' => 'all',
                'equipment_type' => 'mixed',
                'duration_min' => 30,
                'default_capacity' => 1,
                'credit_cost' => 1,
                'color' => '#C7A2B8',
            ],
            [
                'code' => 'revive-60',
                'name_th' => 'รีไววฟ์ ยัวร์ บอดี้ 60 นาที',
                'name_en' => 'Revive Your Body 60 mins',
                'description_th' => 'บริการฟื้นฟูร่างกาย คลายกล้ามเนื้อ แบบเต็มรูปแบบ 60 นาที',
                'description_en' => 'A full 60-minute body recovery and muscle release session.',
                'suitable_for_th' => 'ทุกคน เสริมกับคลาสพิลาทิส',
                'suitable_for_en' => 'Everyone — pairs well with your Pilates sessions',
                'level' => 'all',
                'equipment_type' => 'mixed',
                'duration_min' => 60,
                'default_capacity' => 1,
                'credit_cost' => 1,
                'color' => '#B98FA8',
            ],
        ];

        foreach ($types as $i => $type) {
            ClassType::updateOrCreate(
                ['code' => $type['code']],
                $type + ['is_active' => true, 'sort_order' => $i]
            );
        }
    }
}
