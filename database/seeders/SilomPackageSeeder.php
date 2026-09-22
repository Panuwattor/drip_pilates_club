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
     * ลูกค้ายืนยันแล้ว (ก.ย. 2026): "ใช้ข้ามสาขาได้เฉพาะคลาสไพรเวทนะคะ"
     * ดังนั้น:
     *  - Private       -> ใช้ได้ทั้ง 2 สาขา ดูแลใน PackageSeeder ที่เดียว (ไม่มี silom-private-* แล้ว)
     *                     เพราะราคาสองสาขาเท่ากันเป๊ะ ถ้าแยกไว้จะมีแพ็กชื่อเหมือนกันราคาเท่ากันซ้ำซ้อน
     *  - Reformer/Mat  -> ผูกสาขาสีลมเท่านั้น (ไฟล์นี้)
     *  - Duo           -> มีแต่อารีย์ตามเดิม
     *
     * ถ้าวันหน้าราคา Private ของสองสาขาต่างกัน ต้องแยกแพ็กกลับ:
     *   สร้าง class type + แพ็ก silom-private-* ใหม่ แล้วตั้ง all_branches = false
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
            'silom-reformer-group' => 1190,
            'silom-mat-group' => 990,
        ];

        // Private ไม่อยู่ในนี้ — ใช้แพ็กชุดเดียวกับอารีย์ที่ตั้ง all_branches = true ไว้ใน PackageSeeder

        // [code, ชื่อไทย, ชื่ออังกฤษ, จำนวนครั้ง, ราคารวม, ราคาต่อคลาส, กี่เดือน, คลาสที่ใช้ได้, ลำดับ]
        $packages = [
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
        $this->retireSilomPrivate();
    }

    /**
     * เก็บกวาดแพ็ก Private ของสีลมที่เคย seed ไว้ตอนยังแยกสาขากัน
     * ตอนนี้ยุบไปใช้แพ็กชุดเดียวกับอารีย์แล้ว (ลูกค้ายืนยันว่าไพรเวทข้ามสาขาได้)
     *
     * ปิดการใช้งานแทนการลบ ตามหลักของโปรเจ็คนี้ — ถ้าเผลอมีคนซื้อไปแล้ว
     * ข้อมูลการซื้อและเครดิตจะยังอยู่ครบ ไม่พังทั้งระบบ
     */
    private function retireSilomPrivate(): void
    {
        $retired = Package::whereIn('code', [
            'silom-private-1',
            'silom-private-10',
            'silom-private-20',
            'silom-private-30',
            'silom-trial-private',
        ])->update([
            'is_active' => false,
            'is_public' => false,
        ]);

        if ($retired > 0) {
            $this->command?->info("  ปิดแพ็ก Private สีลมเดิม {$retired} รายการ (ยุบไปใช้แพ็กร่วมกับอารีย์)");
        }
    }

    /**
     * โปรทดลองลูกค้าใหม่ 3 ครั้ง ใช้ได้ 2 สัปดาห์ ซื้อได้ครั้งเดียวต่อคน
     *
     * เหลือ 2 ตัว — ทดลอง Private ใช้ของอารีย์ร่วมกัน (trial-private) เพราะข้ามสาขาได้
     * ถ้าแยกไว้ ลูกค้าจะซื้อทดลอง Private ได้ 2 ใบ (สาขาละใบ) ทั้งที่ควรได้คนละครั้งเดียว
     */
    private function seedTrials(Branch $silom, $types): void
    {
        $trials = [
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
