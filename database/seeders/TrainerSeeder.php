<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Trainer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TrainerSeeder extends Seeder
{
    /**
     * เทรนเนอร์จริง 9 คน จากรูป OUR INSTRUCTOR ที่ลูกค้าส่งมา
     * ลูกค้าให้ข้อมูลมาแค่ ชื่อเล่น + สถาบันที่จบ + ระดับ (Certified / Trained) เท่านั้น
     * ยังไม่มีชื่อจริง ประวัติ หรือความถนัดรายคน — ช่องพวกนั้นจึงเว้น null ไว้
     * ห้ามแต่งข้อมูลเอง รอลูกค้าส่งเพิ่มแล้วค่อยเติมผ่านหน้าแอดมิน
     *
     * TR-01..TR-04 เป็น record เดิมที่มีตารางสอนผูกอยู่ (สั่งเขียนทับด้วยชื่อใหม่)
     * ตารางและ session เดิมจึงยังอยู่ครบ แค่เปลี่ยนชื่อคนสอน
     *
     * รูป: public/images/01-09.jpg เรียงตามรูปโปสเตอร์ ซ้าย→ขวา บน→ล่าง
     * อัปขึ้น R2 ให้อัตโนมัติ (ดูเมธอด uploadAvatar)
     */
    public function run(): void
    {
        $branchIds = Branch::pluck('id')->all();

        // [code, ชื่อเล่นไทย, ชื่อเล่นอังกฤษ, สถาบัน, ระดับ, ไฟล์รูป]
        $trainers = [
            ['TR-01', 'ผักกาด', 'Pakkad', 'PMI',            'Certified', '01.jpg'],
            ['TR-02', 'กิฟท์',  'Gift',   'Balanced Body',  'Certified', '02.jpg'],
            ['TR-03', 'โม',     'Mo',     'Classical',      'Certified', '03.jpg'],
            ['TR-04', 'ปอย',    'Poy',    'STOTT PILATES',  'Certified', '04.jpg'],
            ['TR-05', 'ปริว',   'Priaw',  'STOTT PILATES',  'Certified', '05.jpg'],
            ['TR-06', 'มาย',    'Maii',   'STOTT PILATES',  'Trained',   '06.jpg'],
            ['TR-07', 'แบมบี้', 'Bambi',  'STOTT PILATES',  'Trained',   '07.jpg'],
            ['TR-08', 'ปรีช',   'Preech', 'STOTT PILATES',  'Trained',   '08.jpg'],
            ['TR-09', 'แคร์',   'Care',   'STOTT PILATES',  'Trained',   '09.jpg'],
        ];

        foreach ($trainers as $i => [$code, $nickTh, $nickEn, $school, $level, $image]) {
            // ระดับตามที่ลูกค้าเขียนในรูป Certified = จบหลักสูตรเต็ม, Trained = ผ่านการอบรม
            $levelTh = $level === 'Certified' ? 'ผู้สอนที่ได้รับการรับรอง' : 'ผู้สอนที่ผ่านการอบรม';

            Trainer::updateOrCreate(['code' => $code], [
                // ลูกค้ายังไม่ให้ชื่อจริง ใช้ชื่อเล่นไปก่อนเพื่อไม่ให้ช่อง required ว่าง
                'name_th' => $nickTh,
                'name_en' => $nickEn,
                'nickname_th' => $nickTh,
                'nickname_en' => $nickEn,
                'certifications_th' => "{$school} · {$levelTh}",
                'certifications_en' => "{$school} · {$level} Instructor",
                'avatar' => $this->uploadAvatar($image),
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }

        // ครูผูกไว้ทุกสาขา เพราะจริงๆ ครูเดินสายสอนได้ทั้ง 2 สาขา
        //
        // ช่องนี้ไม่ได้คุมอะไรในระบบ — ฟอร์มจัดตารางดึงครูทุกคนมาให้เลือกอยู่แล้ว
        // ไม่ได้กรองตามสาขา ตัวที่กำหนดจริงว่าครูสอนที่ไหนคือ "ตารางลงสาขาไหน"
        // ค่านี้ใช้แค่โชว์ในหน้ารายชื่อครูของแอดมิน แก้ได้ที่ /admin/trainers
        Trainer::all()->each(fn ($t) => $t->branches()->sync($branchIds));
    }

    /**
     * อัปรูปจาก public/images ขึ้นดิสก์ media (R2) แล้วคืน URL เต็มไว้เก็บลง DB
     * ตั้งชื่อไฟล์คงที่ trainers/seed/xx.jpg เพื่อให้รันซ้ำแล้วทับไฟล์เดิม ไม่กองซ้ำในบักเก็ต
     * ถ้าดิสก์เป็น public ก็คืน path เดิมแบบที่ asset() ใช้ได้ เหมือนพฤติกรรมก่อนหน้า
     */
    private function uploadAvatar(string $image): ?string
    {
        $source = public_path('images/' . $image);

        if (! is_file($source)) {
            $this->command?->warn("  ไม่พบรูป {$image} — ข้ามการอัปโหลด");

            return null;
        }

        $disk = config('filesystems.media', 'public');

        if ($disk === 'public') {
            return 'images/' . $image;
        }

        $path = 'trainers/seed/' . $image;
        Storage::disk($disk)->put($path, file_get_contents($source));

        return Storage::disk($disk)->url($path);
    }
}
