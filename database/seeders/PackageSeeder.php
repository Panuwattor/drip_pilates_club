<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ClassType;
use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * ราคาจริงจาก Services Menu ของลูกค้า — เฉพาะสาขาอารีย์
     * สาขาสีลมลูกค้าขอแจ้งกลับอีกที ยังไม่ใส่ราคา
     *
     * valid_months คือตัวเลขที่ลูกค้าเขียนในเมนู ("Valid 3 month")
     * valid_days คือตัวที่ระบบใช้คำนวณจริง = valid_months x 30
     * ถ้าลูกค้ายืนยันว่าหมายถึงเดือนปฏิทิน ค่อยปรับ valid_days ทีหลัง
     */
    public function run(): void
    {
        $aree = Branch::where('code', 'aree')->firstOrFail();
        $types = ClassType::pluck('id', 'code');

        // ราคาต่อคลาสแบบซื้อเดี่ยว ไว้คำนวณราคาขีดฆ่าของแพ็ก
        $singleRate = [
            'private-pilates' => 2890,
            'duo-pilates' => 3890,
            'trio-reformer' => 1190,
        ];

        // [code, ชื่อไทย, ชื่ออังกฤษ, จำนวนครั้ง, ราคารวม, ราคาต่อคลาส, กี่เดือน, คลาสที่ใช้ได้, ลำดับ]
        $packages = [
            // ── PRIVATE PILATES ──────────────────────────────────
            ['private-1',  'ไพรเวท พิลาทิส 1 ครั้ง',  'Private Pilates — Single Class', 1,  2890,  2890, 1, 'private-pilates', 1],
            ['private-10', 'ไพรเวท พิลาทิส 10 ครั้ง', 'Private Pilates — 10 Classes',   10, 24900, 2490, 3, 'private-pilates', 2],
            ['private-20', 'ไพรเวท พิลาทิส 20 ครั้ง', 'Private Pilates — 20 Classes',   20, 45800, 2290, 6, 'private-pilates', 3],
            ['private-30', 'ไพรเวท พิลาทิส 30 ครั้ง', 'Private Pilates — 30 Classes',   30, 61500, 2050, 8, 'private-pilates', 4],

            // ── DUO PILATES ──────────────────────────────────────
            ['duo-1',  'ดูโอ พิลาทิส 1 ครั้ง',  'Duo Pilates — Single Class', 1,  3890,  3890, 1, 'duo-pilates', 5],
            ['duo-10', 'ดูโอ พิลาทิส 10 ครั้ง', 'Duo Pilates — 10 Classes',   10, 34900, 3490, 3, 'duo-pilates', 6],
            ['duo-20', 'ดูโอ พิลาทิส 20 ครั้ง', 'Duo Pilates — 20 Classes',   20, 65800, 3290, 6, 'duo-pilates', 7],
            ['duo-30', 'ดูโอ พิลาทิส 30 ครั้ง', 'Duo Pilates — 30 Classes',   30, 91500, 3050, 9, 'duo-pilates', 8],

            // ── TRIO REFORMER GROUP CLASS (ใหม่) ─────────────────
            ['trio-1',  'ทรีโอ รีฟอร์มเมอร์ 1 ครั้ง',  'Trio Reformer — Single Class', 1,  1190,  1190, 1, 'trio-reformer', 9],
            ['trio-5',  'ทรีโอ รีฟอร์มเมอร์ 5 ครั้ง',  'Trio Reformer — 5 Classes',    5,  4950,  990,  1, 'trio-reformer', 10],
            ['trio-10', 'ทรีโอ รีฟอร์มเมอร์ 10 ครั้ง', 'Trio Reformer — 10 Classes',   10, 8500,  850,  2, 'trio-reformer', 11],
            ['trio-20', 'ทรีโอ รีฟอร์มเมอร์ 20 ครั้ง', 'Trio Reformer — 20 Classes',   20, 15800, 790,  3, 'trio-reformer', 12],
            ['trio-30', 'ทรีโอ รีฟอร์มเมอร์ 30 ครั้ง', 'Trio Reformer — 30 Classes',   30, 20700, 690,  4, 'trio-reformer', 13],
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
            $package->branches()->sync([$aree->id]);
        }

        $this->seedTrials($aree, $types);
        $this->seedRevive($aree, $types);
        $this->seedUnlimited($aree);
    }

    /**
     * ลูกค้ายังไม่มีแพ็กเหมาจ่ายขาย แต่ระบบรองรับไว้แล้ว
     * ตั้ง is_public = false ไม่ให้โผล่หน้าเว็บ เปิดขายได้ทันทีถ้าลูกค้าต้องการ
     */
    private function seedUnlimited(Branch $aree): void
    {
        $package = Package::updateOrCreate(['code' => 'unlimited-monthly'], [
            'name_th' => 'เหมาจ่ายรายเดือน',
            'name_en' => 'Monthly Unlimited',
            'description_th' => 'เรียนได้ไม่จำกัดภายใน 30 วัน (สูงสุด 2 คลาสต่อวัน) — ยังไม่เปิดขาย',
            'description_en' => 'Unlimited classes for 30 days, max 2 per day — not yet on sale.',
            'type' => 'unlimited',
            'credit_amount' => null,
            'price' => 12900,
            'price_per_class' => null,
            'compare_at_price' => null,
            'valid_months' => 1,
            'valid_days' => 30,
            'max_per_day' => 2,
            'max_per_week' => 10,
            'max_future_bookings' => 5,
            'all_class_types' => true,
            'all_branches' => false,
            'once_per_customer' => false,
            'is_public' => false,
            'is_active' => false,
            'sort_order' => 90,
        ]);

        $package->branches()->sync([$aree->id]);
    }

    /**
     * โปรทดลองลูกค้าใหม่ 3 ครั้ง ใช้ได้ 2 สัปดาห์ ซื้อได้ครั้งเดียวต่อคน
     */
    private function seedTrials(Branch $aree, $types): void
    {
        $trials = [
            ['trial-private', 'ทดลอง ไพรเวท พิลาทิส 3 ครั้ง', 'First Trial — Private Pilates (3 sessions)', 5500, 'private-pilates', 2890, 20],
            ['trial-duo',     'ทดลอง ดูโอ พิลาทิส 3 ครั้ง',   'First Trial — Duo Pilates (3 sessions)',     7500, 'duo-pilates',    3890, 21],
            ['trial-trio',    'ทดลอง ทรีโอ รีฟอร์มเมอร์ 3 ครั้ง', 'First Trial — Trio Reformer (3 sessions)', 1950, 'trio-reformer', 1190, 22],
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
            $package->branches()->sync([$aree->id]);
        }
    }

    /**
     * Revive Your Body — บริการฟื้นฟูร่างกาย ขายเป็นครั้งตามระยะเวลา
     * ส่วนลด 10/15/20% สำหรับลูกค้าที่ซื้อแพ็กพิลาทิส ยังต้องให้แอดมินใส่มือตอนเปิดบิล
     */
    private function seedRevive(Branch $aree, $types): void
    {
        $revive = [
            ['revive-60', 'รีไววฟ์ ยัวร์ บอดี้ 60 นาที', 'Revive Your Body — 60 mins', 1290, 'revive-60', 30],
            ['revive-30', 'รีไววฟ์ ยัวร์ บอดี้ 30 นาที', 'Revive Your Body — 30 mins', 690,  'revive-30', 31],
        ];

        foreach ($revive as [$code, $nameTh, $nameEn, $price, $typeCode, $sort]) {
            $package = Package::updateOrCreate(['code' => $code], [
                'name_th' => $nameTh,
                'name_en' => $nameEn,
                'description_th' => 'ลูกค้าที่ซื้อแพ็กพิลาทิสรับส่วนลด 10% (10 ครั้ง), 15% (20 ครั้ง), 20% (30 ครั้ง)',
                'description_en' => 'Pilates package holders receive 10% off (10-class), 15% off (20-class), 20% off (30-class).',
                'type' => 'credit_pack',
                'credit_amount' => 1,
                'price' => $price,
                'price_per_class' => null,
                'compare_at_price' => null,
                'valid_months' => 3,
                'valid_days' => 90,
                'all_class_types' => false,
                'all_branches' => false,
                'once_per_customer' => false,
                'is_public' => true,
                'is_active' => true,
                'sort_order' => $sort,
            ]);

            $package->classTypes()->sync([$types[$typeCode]]);
            $package->branches()->sync([$aree->id]);
        }
    }
}
