<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ClassType;
use App\Models\Package;
use Illuminate\Database\Seeder;

class SilomPackageSeeder extends Seeder
{
    /**
     * ราคาจริงจาก Services Menu ของสาขาสีลม
     *
     * ลูกค้าขอให้แยกจากอารีย์ขาดจากกัน ถึงราคาบางตัวจะเท่ากันตอนนี้ก็ตาม
     * แพ็กและประเภทคลาสของสีลมใช้โค้ดขึ้นต้น silom- ทั้งหมด
     * แก้ราคาสาขาไหนจึงไม่กระทบอีกสาขา
     *
     * ⚠️ ยังไม่ยืนยันกับลูกค้าปลายทาง: "ซื้อสาขาไหน ใช้ได้เฉพาะสาขานั้น"
     * ตอนนี้ตั้ง all_branches = false ทุกตัว = ซื้อสีลมใช้ได้แค่สีลม
     *
     * ถ้าลูกค้าตอบกลับว่าอยากให้ใช้ข้ามสาขาได้ ไม่ต้องแก้โค้ด:
     *   แอดมินเข้า /admin/packages แก้แพ็ก ติ๊ก "ขายได้ทุกสาขา" แล้วบันทึก
     *   (ทดสอบแล้วว่าติ๊กช่องเดียวจบ ทั้งหน้าซื้อและการจองเปิดข้ามสาขาทันที)
     * หรือถ้าอยากให้เป็นค่าตั้งต้น เปลี่ยน all_branches ด้านล่างเป็น true แล้วรัน seeder ใหม่
     *
     * ต่างจากอารีย์:
     *  - ไม่มี Duo Pilates
     *  - มี Mat Pilates Group Class เพิ่มเข้ามา
     *  - Reformer Group เมนูไม่เขียน "Trio" (รอลูกค้ายืนยันจำนวนคนต่อคลาส)
     *
     * valid_months คือตัวเลขที่ลูกค้าเขียนในเมนู valid_days คือตัวที่ระบบใช้คำนวณจริง
     */
    public function run(): void
    {
        $silom = Branch::where('code', 'silom')->firstOrFail();
        $types = ClassType::pluck('id', 'code');

        // ราคาต่อคลาสแบบซื้อเดี่ยว ไว้คำนวณราคาขีดฆ่าของแพ็ก
        $singleRate = [
            'silom-private-pilates' => 2890,
            'silom-reformer-group' => 1190,
            'silom-mat-group' => 990,
        ];

        // [code, ชื่อไทย, ชื่ออังกฤษ, จำนวนครั้ง, ราคารวม, ราคาต่อคลาส, กี่เดือน, คลาสที่ใช้ได้, ลำดับ]
        $packages = [
            // ── PRIVATE PILATES ──────────────────────────────────
            ['silom-private-1',  'ไพรเวท พิลาทิส 1 ครั้ง',  'Private Pilates — Single Class', 1,  2890,  2890, 1, 'silom-private-pilates', 1],
            ['silom-private-10', 'ไพรเวท พิลาทิส 10 ครั้ง', 'Private Pilates — 10 Classes',   10, 24900, 2490, 3, 'silom-private-pilates', 2],
            ['silom-private-20', 'ไพรเวท พิลาทิส 20 ครั้ง', 'Private Pilates — 20 Classes',   20, 45800, 2290, 6, 'silom-private-pilates', 3],
            ['silom-private-30', 'ไพรเวท พิลาทิส 30 ครั้ง', 'Private Pilates — 30 Classes',   30, 61500, 2050, 8, 'silom-private-pilates', 4],

            // ── REFORMER GROUP CLASS ─────────────────────────────
            ['silom-reformer-1',  'รีฟอร์มเมอร์ กรุ๊ปคลาส 1 ครั้ง',  'Reformer Group — Single Class', 1,  1190,  1190, 1, 'silom-reformer-group', 10],
            ['silom-reformer-5',  'รีฟอร์มเมอร์ กรุ๊ปคลาส 5 ครั้ง',  'Reformer Group — 5 Classes',    5,  4950,  990,  1, 'silom-reformer-group', 11],
            ['silom-reformer-10', 'รีฟอร์มเมอร์ กรุ๊ปคลาส 10 ครั้ง', 'Reformer Group — 10 Classes',   10, 8500,  850,  2, 'silom-reformer-group', 12],
            ['silom-reformer-20', 'รีฟอร์มเมอร์ กรุ๊ปคลาส 20 ครั้ง', 'Reformer Group — 20 Classes',   20, 15800, 790,  3, 'silom-reformer-group', 13],
            ['silom-reformer-30', 'รีฟอร์มเมอร์ กรุ๊ปคลาส 30 ครั้ง', 'Reformer Group — 30 Classes',   30, 20700, 690,  4, 'silom-reformer-group', 14],

            // ── MAT PILATES GROUP CLASS (อารีย์ไม่มี) ────────────
            ['silom-mat-1',  'แมท พิลาทิส กรุ๊ปคลาส 1 ครั้ง',  'Mat Pilates Group — Single Class', 1,  990,   990, 1, 'silom-mat-group', 20],
            ['silom-mat-5',  'แมท พิลาทิส กรุ๊ปคลาส 5 ครั้ง',  'Mat Pilates Group — 5 Classes',    5,  3450,  690, 1, 'silom-mat-group', 21],
            ['silom-mat-10', 'แมท พิลาทิส กรุ๊ปคลาส 10 ครั้ง', 'Mat Pilates Group — 10 Classes',   10, 6500,  650, 2, 'silom-mat-group', 22],
            ['silom-mat-20', 'แมท พิลาทิส กรุ๊ปคลาส 20 ครั้ง', 'Mat Pilates Group — 20 Classes',   20, 12000, 600, 3, 'silom-mat-group', 23],
            ['silom-mat-30', 'แมท พิลาทิส กรุ๊ปคลาส 30 ครั้ง', 'Mat Pilates Group — 30 Classes',   30, 16500, 550, 4, 'silom-mat-group', 24],
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

        $this->seedTrials($silom, $types);
    }

    /**
     * โปรทดลองลูกค้าใหม่ 3 ครั้ง ใช้ได้ 2 สัปดาห์ ซื้อได้ครั้งเดียวต่อคน
     * สีลมมี 3 ตัว (ไม่มีดูโอแบบอารีย์ แต่มีแมทเพิ่ม)
     */
    private function seedTrials(Branch $silom, $types): void
    {
        $trials = [
            ['silom-trial-private',  'ทดลอง ไพรเวท พิลาทิส 3 ครั้ง',      'First Trial — Private Pilates (3 sessions)',   5500, 'silom-private-pilates', 2890, 30],
            ['silom-trial-reformer', 'ทดลอง รีฟอร์มเมอร์ กรุ๊ปคลาส 3 ครั้ง', 'First Trial — Reformer Group (3 sessions)',    1950, 'silom-reformer-group',  1190, 31],
            ['silom-trial-mat',      'ทดลอง แมท พิลาทิส กรุ๊ปคลาส 3 ครั้ง',  'First Trial — Mat Pilates Group (3 sessions)', 1550, 'silom-mat-group',        990, 32],
        ];

        foreach ($trials as [$code, $nameTh, $nameEn, $price, $typeCode, $singleRate, $sort]) {
            $package = Package::updateOrCreate(['code' => $code], [
                'name_th' => $nameTh,
                'name_en' => $nameEn,
                'description_th' => 'สำหรับลูกค้าใหม่เท่านั้น ใช้ได้ภายใน 2 สัปดาห์ ซื้อได้ครั้งเดียว',
                'description_en' => 'For new customers only · 3 sessions valid for 2 weeks · one purchase per person.',
                'type' => 'trial',
                'credit_amount' => 3,
                'price' => $price,
                'price_per_class' => round($price / 3, 2),
                'compare_at_price' => 3 * $singleRate,
                'valid_months' => null,
                'valid_days' => 14,
                'all_class_types' => false,
                'all_branches' => false,
                'once_per_customer' => true,
                'is_public' => true,
                'is_active' => true,
                'sort_order' => $sort,
            ]);

            $package->classTypes()->sync([$types[$typeCode]]);
            $package->branches()->sync([$silom->id]);
        }
    }
}
