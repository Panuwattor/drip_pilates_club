<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ClassType;
use App\Models\Package;
use Illuminate\Database\Seeder;

class SilomBarreSeeder extends Seeder
{
    /**
     * Barre Group Class — สาขาสีลมเท่านั้น (ลูกค้ายืนยัน ก.ย. 2026)
     *
     * เมนูสีลมเขียนหัวข้อรวมว่า "BARRE & MAT PILATES GROUP CLASS"
     * ลูกค้าแจ้งเพิ่ม Barre เป็นคอร์สแยก ราคาเท่ากับ Mat ทุกบรรทัด
     *   (990฿/คลาส, 5/3,450 · 10/6,500 · 20/12,000 · 30/16,500)
     *
     * ทำเป็น seeder รันแยกได้ในตัว: seed ทั้ง class type + แพ็ก + โปรทดลอง
     *   php artisan db:seed --class=SilomBarreSeeder
     *
     * โครงสร้าง/คอมเมนต์อิงตาม SilomPackageSeeder ตัวแม่ ให้ดูคู่กันได้
     * โค้ดขึ้นต้น silom-barre- ทั้งหมด แก้ราคา Barre ไม่กระทบ Mat/Reformer
     */
    public function run(): void
    {
        $silom = Branch::where('code', 'silom')->firstOrFail();

        // ── class type ───────────────────────────────────────────
        // ต่อท้าย sort_order จาก Mat (silom-mat-group อยู่ท้ายสุดใน ClassTypeSeeder)
        ClassType::updateOrCreate(
            ['code' => 'silom-barre-group'],
            [
                'name_th' => 'บาร์ กรุ๊ปคลาส (สีลม)',
                'name_en' => 'Barre Group Class (Silom)',
                'description_th' => 'คลาสกลุ่มบาร์ ผสมบัลเลต์ พิลาทิส และเวทเทรนนิ่ง เน้นความแข็งแรงและทรงตัว',
                'description_en' => 'A barre group class blending ballet, Pilates and light strength work for tone and balance.',
                'suitable_for_th' => 'ทุกระดับ เหมาะกับผู้เริ่มต้น',
                'suitable_for_en' => 'All levels — great for beginners',
                'level' => 'all',
                'equipment_type' => 'mat',
                'duration_min' => 50,
                'default_capacity' => 8,
                'credit_cost' => 1,
                'color' => '#C7A2B8',
                'is_active' => true,
                'sort_order' => 8,
            ]
        );

        $types = ClassType::pluck('id', 'code');

        // ราคาต่อคลาสแบบซื้อเดี่ยว ไว้คำนวณราคาขีดฆ่าของแพ็ก (เท่า Mat)
        $singleRate = ['silom-barre-group' => 990];

        // [code, ชื่อไทย, ชื่ออังกฤษ, จำนวนครั้ง, ราคารวม, ราคาต่อคลาส, กี่เดือน, คลาสที่ใช้ได้, ลำดับ]
        $packages = [
            ['silom-barre-1',  'บาร์ กรุ๊ปคลาส 1 ครั้ง',  'Barre Group — Single Class', 1,  990,   990, 1, 'silom-barre-group', 40],
            ['silom-barre-5',  'บาร์ กรุ๊ปคลาส 5 ครั้ง',  'Barre Group — 5 Classes',    5,  3450,  690, 1, 'silom-barre-group', 41],
            ['silom-barre-10', 'บาร์ กรุ๊ปคลาส 10 ครั้ง', 'Barre Group — 10 Classes',   10, 6500,  650, 2, 'silom-barre-group', 42],
            ['silom-barre-20', 'บาร์ กรุ๊ปคลาส 20 ครั้ง', 'Barre Group — 20 Classes',   20, 12000, 600, 3, 'silom-barre-group', 43],
            ['silom-barre-30', 'บาร์ กรุ๊ปคลาส 30 ครั้ง', 'Barre Group — 30 Classes',   30, 16500, 550, 4, 'silom-barre-group', 44],
        ];

        foreach ($packages as [$code, $nameTh, $nameEn, $credits, $price, $perClass, $months, $typeCode, $sort]) {
            $isSingle = $credits === 1;
            $fullPrice = $credits * $singleRate[$typeCode];

            $package = Package::updateOrCreate(['code' => $code], [
                'name_th' => $nameTh,
                'name_en' => $nameEn,
                'description_th' => $isSingle
                    ? 'ราคาต่อคลาส สำหรับซื้อครั้งเดียว'
                    : "ราคา {$perClass}฿ ต่อคลาส ใช้ได้ภายใน {$months} เดือน",
                'description_en' => $isSingle
                    ? 'Drop-in rate for a single class.'
                    : "{$perClass} THB per class · valid for {$months} month" . ($months > 1 ? 's' : ''),
                'type' => 'credit_pack',
                'credit_amount' => $credits,
                'price' => $price,
                'price_per_class' => $isSingle ? null : $perClass,
                // โชว์ขีดฆ่าเฉพาะแพ็กที่ถูกกว่าซื้อเดี่ยวจริงๆ
                'compare_at_price' => (! $isSingle && $fullPrice > $price) ? $fullPrice : null,
                'valid_months' => $months,
                'valid_days' => $months * 30,
                'all_class_types' => false,
                'all_branches' => false,
                'once_per_customer' => false,
                'is_public' => true,
                'is_active' => true,
                'sort_order' => $sort,
            ]);

            $package->classTypes()->sync([$types[$typeCode]]);
            $package->branches()->sync([$silom->id]);
        }

        $this->seedTrial($silom, $types);
    }

    /**
     * โปรทดลองลูกค้าใหม่ 3 ครั้ง ใช้ได้ 2 สัปดาห์ ซื้อได้ครั้งเดียวต่อคน
     * ราคาอิง Mat (1,550฿ = ราคาเดียวกับ silom-trial-mat)
     */
    private function seedTrial(Branch $silom, $types): void
    {
        $package = Package::updateOrCreate(['code' => 'silom-trial-barre'], [
            'name_th' => 'ทดลอง บาร์ กรุ๊ปคลาส 3 ครั้ง',
            'name_en' => 'First Trial — Barre Group (3 sessions)',
            'description_th' => 'สำหรับลูกค้าใหม่เท่านั้น ใช้ได้ภายใน 2 สัปดาห์ ซื้อได้ครั้งเดียว',
            'description_en' => 'For new customers only · 3 sessions valid for 2 weeks · one purchase per person.',
            'type' => 'trial',
            'credit_amount' => 3,
            'price' => 1550,
            'price_per_class' => round(1550 / 3, 2),
            'compare_at_price' => 3 * 990,
            'valid_months' => null,
            'valid_days' => 14,
            'all_class_types' => false,
            'all_branches' => false,
            'once_per_customer' => true,
            'is_public' => true,
            'is_active' => true,
            'sort_order' => 45,
        ]);

        $package->classTypes()->sync([$types['silom-barre-group']]);
        $package->branches()->sync([$silom->id]);
    }
}
