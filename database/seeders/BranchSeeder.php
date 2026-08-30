<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Room;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        // สาขาที่ 1 — ที่อยู่จริง
        $aree = Branch::updateOrCreate(['code' => 'aree'], [
            'name_th' => 'สาขาอารีย์',
            'name_en' => 'Aree Branch',
            'short_name_th' => 'อารีย์',
            'short_name_en' => 'Aree',
            'address_th' => 'ชั้น 14 อาคารวานิช เพลส (Vanit Place) ถ.พหลโยธิน แขวงสามเสนใน เขตพญาไท กรุงเทพมหานคร 10400',
            'address_en' => '14th Floor, Vanit Place Office Building, Phahonyothin Rd, Samsen Nai, Phaya Thai, Bangkok 10400',
            'direction_th' => 'BTS อารีย์ ทางออก 1 เดินประมาณ 3 นาที',
            'direction_en' => 'BTS Ari, Exit 1 — about a 3-minute walk',
            'phone' => '02-000-0000',
            'line_id' => '@drippilatesclub',
            'open_time' => '07:00:00',
            'close_time' => '21:00:00',
            // บัญชีจริงที่ลูกค้าแจ้งมา เฉพาะสาขาอารีย์
            'bank_name' => 'SCB',
            'bank_account_name' => 'DRIP Pilates and Wellness club',
            'bank_account_number' => '4381913491',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // สาขาที่ 2 — ที่อยู่ยังเป็นข้อมูลคร่าวๆ (ไม่ยืนยัน) รอลูกค้าแจ้งที่อยู่/เบอร์/บัญชีจริงอีกที
        $silom = Branch::updateOrCreate(['code' => 'silom'], [
            'name_th' => 'สาขาสีลม',
            'name_en' => 'Silom Branch',
            'short_name_th' => 'สีลม',
            'short_name_en' => 'Silom',
            'address_th' => 'ถนนสีลม แขวงสีลม เขตบางรัก กรุงเทพมหานคร 10500 (ที่อยู่เบื้องต้น รอยืนยัน)',
            'address_en' => 'Silom Road, Silom, Bang Rak, Bangkok 10500 (preliminary address, to be confirmed)',
            'direction_th' => null,
            'direction_en' => null,
            'phone' => null,
            'line_id' => '@drippilatesclub',
            'open_time' => '07:00:00',
            'close_time' => '21:00:00',
            'bank_name' => null,
            'bank_account_name' => null,
            'bank_account_number' => null,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // ความจุห้องอิงจากเมนูจริง คลาสใหญ่สุดคือ Trio = 3 คน
        $rooms = [
            [$aree, 'ห้องรีฟอร์มเมอร์ 1', 'Reformer Studio 1', 3, 'reformer'],
            [$aree, 'ห้องรีฟอร์มเมอร์ 2', 'Reformer Studio 2', 3, 'reformer'],
            [$aree, 'ห้องทรีตเมนต์', 'Treatment Room', 1, 'mixed'],
            [$silom, 'ห้องรีฟอร์มเมอร์ 1', 'Reformer Studio 1', 3, 'reformer'],
        ];

        foreach ($rooms as $i => [$branch, $nameTh, $nameEn, $capacity, $equipment]) {
            Room::updateOrCreate(
                ['branch_id' => $branch->id, 'name_en' => $nameEn],
                [
                    'name_th' => $nameTh,
                    'capacity' => $capacity,
                    'equipment_type' => $equipment,
                    'is_active' => true,
                    'sort_order' => $i,
                ]
            );
        }
    }
}
