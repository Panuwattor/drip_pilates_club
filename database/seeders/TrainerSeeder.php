<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Trainer;
use Illuminate\Database\Seeder;

class TrainerSeeder extends Seeder
{
    public function run(): void
    {
        $branchIds = Branch::pluck('id')->all();

        $trainers = [
            ['TR-01', 'แนน', 'Nan', 'ครูแนน', 'Coach Nan', 'images/01.jpg', 'Private, Duo Reformer', 'Private, Duo Reformer'],
            ['TR-02', 'ต้า', 'Ta', 'ครูต้า', 'Coach Ta', 'images/02.jpg', 'Private, Trio Reformer', 'Private, Trio Reformer'],
            ['TR-03', 'ฟ้า', 'Fah', 'ครูฟ้า', 'Coach Fah', 'images/03.jpg', 'Trio Reformer', 'Trio Reformer'],
            ['TR-04', 'เก็ท', 'Get', 'ครูเก็ท', 'Coach Get', 'images/04.jpg', 'Private, Revive Your Body', 'Private, Revive Your Body'],
        ];

        foreach ($trainers as $i => [$code, $nickTh, $nickEn, $nameTh, $nameEn, $avatar, $specTh, $specEn]) {
            $trainer = Trainer::updateOrCreate(['code' => $code], [
                'name_th' => $nameTh,
                'name_en' => $nameEn,
                'nickname_th' => $nickTh,
                'nickname_en' => $nickEn,
                'bio_th' => 'ผู้สอนพิลาทิสประสบการณ์มากกว่า 5 ปี เชี่ยวชาญด้าน ' . $specTh,
                'bio_en' => 'Pilates instructor with over 5 years of experience, specialising in ' . $specEn . '.',
                'specialties_th' => $specTh,
                'specialties_en' => $specEn,
                'avatar' => $avatar,
                'is_active' => true,
                'sort_order' => $i,
            ]);

            // เทรนเนอร์สอนได้ทุกสาขา
            $trainer->branches()->sync($branchIds);
        }
    }
}
