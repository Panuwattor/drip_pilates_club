<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * ประกาศ/บทความส่วนกลาง (branch_id = null) ใช้โชว์ทั้งในแอปลูกค้าและหน้าแรกสาธารณะ
     */
    public function run(): void
    {
        $announcements = [
            [
                'title_th' => 'เปิดสาขาอารีย์อย่างเป็นทางการ',
                'title_en' => 'Aree Branch Now Officially Open',
                'body_th' => 'Drip Pilates Club สาขาอารีย์เปิดให้บริการแล้ววันนี้ พร้อมอุปกรณ์รีฟอร์มเมอร์ครบครันและครูผู้สอนมืออาชีพ',
                'body_en' => 'Drip Pilates Club\'s Aree branch is now open, fully equipped with reformers and a professional instructor team.',
                'image' => 'images/02.jpg',
                'starts_at' => now()->subDays(14),
                'sort_order' => 1,
            ],
            [
                'title_th' => 'โปรทดลองสำหรับลูกค้าใหม่ เริ่มต้น 1,950 บาท',
                'title_en' => 'New Member Trial Packages From 1,950 THB',
                'body_th' => 'ลูกค้าใหม่รับสิทธิ์ทดลองเรียน 3 ครั้งในราคาพิเศษ เลือกได้ทั้งไพรเวท ดูโอ และทรีโอ รีฟอร์มเมอร์',
                'body_en' => 'New members get 3 trial sessions at a special rate — choose from Private, Duo, or Trio Reformer classes.',
                'image' => 'images/03.jpg',
                'starts_at' => now()->subDays(7),
                'sort_order' => 2,
            ],
            [
                'title_th' => '5 เหตุผลที่ควรเริ่มเล่นพิลาทิส',
                'title_en' => '5 Reasons To Start Pilates Today',
                'body_th' => 'พิลาทิสช่วยเสริมสร้างกล้ามเนื้อแกนกลางลำตัว ปรับสมดุลร่างกาย ลดอาการปวดหลัง และเพิ่มความยืดหยุ่น เหมาะกับทุกเพศทุกวัย',
                'body_en' => 'Pilates strengthens your core, improves posture and balance, eases back pain, and builds flexibility — suitable for every age and fitness level.',
                'image' => 'images/04.jpg',
                'starts_at' => now()->subDays(3),
                'sort_order' => 3,
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::updateOrCreate(
                ['branch_id' => null, 'title_th' => $data['title_th']],
                [
                    'title_en' => $data['title_en'],
                    'body_th' => $data['body_th'],
                    'body_en' => $data['body_en'],
                    'image' => $data['image'],
                    'starts_at' => $data['starts_at'],
                    'ends_at' => null,
                    'is_active' => true,
                    'sort_order' => $data['sort_order'],
                ]
            );
        }
    }
}
