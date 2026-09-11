<?php

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    /**
     * คลิปตัวอย่างหน้าแรก — แอดมินเพิ่ม/แก้/ลบเองได้จากหลังบ้าน (เมนู "คลิปวิดีโอ")
     * ใส่รีลจริงของสตูดิโอที่ลูกค้าส่งมาเป็นตัวตั้งต้น
     */
    public function run(): void
    {
        $videos = [
            [
                'url' => 'https://www.instagram.com/reel/DbaGkp9y4yG/',
                'provider' => 'instagram',
                'title_th' => 'พิลาทิสสร้างสมดุลและการควบคุมร่างกาย',
                'title_en' => 'Pilates for balance and control',
                'caption_th' => 'พิลาทิสช่วยเสริมสร้างกล้ามเนื้อมัดเล็กที่สร้างสมดุลและการควบคุม',
                'caption_en' => 'Pilates strengthens the small muscles that create balance and control.',
                'sort_order' => 1,
            ],
            [
                'url' => 'https://www.instagram.com/reel/DbVb5ciSU60/',
                'provider' => 'instagram',
                'title_th' => 'แขนแข็งแรง บุคลิกดีขึ้น',
                'title_en' => 'Strong arms, stronger posture',
                'caption_th' => 'แขนที่แข็งแรงขึ้น ช่วยให้บุคลิกและการทรงตัวดีขึ้น 🙌🏻',
                'caption_en' => 'Strong arms, stronger posture. 🙌🏻',
                'sort_order' => 2,
            ],
            [
                'url' => 'https://www.instagram.com/reel/DbPdvuPyTJ4/',
                'provider' => 'instagram',
                'title_th' => 'สมดุลเป็นคู่ สนุกเป็นสองเท่า',
                'title_en' => 'Double the balance, double the fun',
                'caption_th' => 'ฝึกเป็นคู่ เพิ่มสมดุล เพิ่มความสนุกเป็นสองเท่า',
                'caption_en' => 'Double the balance, double the fun.',
                'sort_order' => 3,
            ],
            [
                'url' => 'https://www.instagram.com/reel/Da-bBs5SKsu/',
                'provider' => 'instagram',
                'title_th' => 'บาเรลไม่เคยโกหก',
                'title_en' => "The barrel doesn't lie",
                'caption_th' => 'บาเรลไม่เคยโกหก 😮‍💨 ทุกการเคลื่อนไหวเผยความแข็งแรงและความมั่นคงของคุณ',
                'caption_en' => "The barrel doesn't lie. 😮‍💨 Every movement reveals your strength and stability.",
                'sort_order' => 4,
            ],
        ];

        foreach ($videos as $data) {
            // ถ้ามีรูปปกที่แคปไว้ตามชื่อ reel id ใน public/images/videos/ ให้ใช้เป็น thumbnail เลย
            if (preg_match('~/reel/([^/]+)~', $data['url'], $m)) {
                $relative = 'images/videos/' . $m[1] . '.jpg';
                if (is_file(public_path($relative))) {
                    $data['thumbnail'] = $relative;
                }
            }

            Video::updateOrCreate(['url' => $data['url']], $data + ['is_active' => true]);
        }
    }
}
