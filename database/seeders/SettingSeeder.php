<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // การจอง
            ['booking_open_days_ahead', '90', 'int', 'booking', 'เปิดให้จองล่วงหน้ากี่วัน', 'Booking window (days ahead)'],
            ['booking_close_minutes_before', '30', 'int', 'booking', 'ปิดรับจองก่อนคลาสเริ่มกี่นาที', 'Close booking (minutes before start)'],
            ['session_generate_days_ahead', '90', 'int', 'booking', 'สร้างรอบเรียนล่วงหน้ากี่วัน', 'Generate sessions (days ahead)'],
            ['schedule_past_days', '2', 'int', 'booking', 'ให้ดูตารางย้อนหลังกี่วัน', 'Schedule past days visible'],

            // ยกเลิก
            ['cancel_deadline_hours', '6', 'int', 'cancellation', 'ยกเลิกฟรีก่อนคลาสกี่ชั่วโมง', 'Free cancellation window (hours)'],
            ['late_cancel_charge_credit', 'true', 'bool', 'cancellation', 'ยกเลิกช้าตัดเครดิตหรือไม่', 'Charge credit on late cancel'],
            ['no_show_charge_credit', 'true', 'bool', 'cancellation', 'ไม่มาเรียนตัดเครดิตหรือไม่', 'Charge credit on no-show'],
            ['auto_no_show_enabled', 'true', 'bool', 'cancellation', 'ปิดคลาสอัตโนมัติเมื่อจบแล้วไม่ได้เช็คอิน', 'Auto mark no-show after class ends'],
            ['auto_no_show_after_minutes', '120', 'int', 'cancellation', 'ปิดคลาสอัตโนมัติหลังคลาสจบกี่นาที', 'Auto no-show delay after class end (minutes)'],

            // waitlist
            ['waitlist_enabled', 'true', 'bool', 'waitlist', 'เปิดใช้ waitlist', 'Enable waitlist'],
            ['waitlist_max', '10', 'int', 'waitlist', 'จำนวนคิวสูงสุด', 'Maximum waitlist size'],
            ['waitlist_auto_promote', 'true', 'bool', 'waitlist', 'เลื่อนคิวอัตโนมัติเมื่อมีคนยกเลิก', 'Auto-promote from waitlist'],

            // แพ็กเกจ
            ['credit_expiry_warn_days', '[30,14,3]', 'json', 'package', 'เตือนก่อนแพ็กหมดอายุกี่วัน', 'Expiry reminder days'],
            ['freeze_max_days_per_package', '30', 'int', 'package', 'ฟรีซแพ็กได้สูงสุดกี่วัน', 'Max freeze days per package'],

            // ทั่วไป
            ['studio_name_th', 'Drip Pilates', 'string', 'general', 'ชื่อสตูดิโอ', 'Studio name'],
            ['studio_name_en', 'Drip Pilates', 'string', 'general', 'ชื่อสตูดิโอ (EN)', 'Studio name (EN)'],
            ['default_locale', 'th', 'string', 'general', 'ภาษาเริ่มต้น', 'Default language'],
            ['currency', 'THB', 'string', 'general', 'สกุลเงิน', 'Currency'],

            // บัญชีรับโอนเงิน โชว์ให้ลูกค้าตอนชำระค่าแพ็ก
            ['payment_bank_name', 'SCB', 'string', 'payment', 'ธนาคาร', 'Bank'],
            ['payment_bank_account_name', 'DRIP Pilates and Wellness club', 'string', 'payment', 'ชื่อบัญชี', 'Account name'],
            ['payment_bank_account_no', '4381913491', 'string', 'payment', 'เลขที่บัญชี', 'Account number'],
            ['payment_promptpay_id', '', 'string', 'payment', 'พร้อมเพย์ (เบอร์/เลขประจำตัว)', 'PromptPay ID'],
            ['payment_qr_image', '', 'string', 'payment', 'รูป QR รับเงิน (ลิงก์รูป)', 'Payment QR image (URL)'],
            ['payment_note_th', 'โอนแล้วแนบสลิปในระบบ แอดมินตรวจสอบและเพิ่มเครดิตให้ภายใน 24 ชั่วโมง', 'string', 'payment', 'ข้อความแจ้งลูกค้า (TH)', 'Payment note (TH)'],
            ['payment_note_en', 'After transferring, upload your slip. Admin will verify and add credits within 24 hours.', 'string', 'payment', 'ข้อความแจ้งลูกค้า (EN)', 'Payment note (EN)'],
            ['order_expire_hours', '24', 'int', 'payment', 'คำสั่งซื้อค้างชำระหมดอายุใน (ชม.)', 'Pending order expiry (hours)'],

            // ช่องทางติดต่อของเจ้าของ (ใช้ร่วมทุกสาขา) โชว์ที่หน้าแรกและ footer
            ['contact_line_url', 'https://lin.ee/WHbMRXz', 'string', 'contact', 'LINE Official Account (ลิงก์)', 'LINE Official Account (URL)'],
            ['contact_facebook_url', 'https://www.facebook.com/profile.php?id=61565064751946', 'string', 'contact', 'Facebook (ลิงก์)', 'Facebook (URL)'],
            ['contact_tiktok_url', 'https://www.tiktok.com/@drippilatesclub', 'string', 'contact', 'TikTok (ลิงก์)', 'TikTok (URL)'],
            ['contact_instagram_url', 'https://www.instagram.com/drip.pilatesclub', 'string', 'contact', 'Instagram (ลิงก์)', 'Instagram (URL)'],
        ];

        foreach ($settings as [$key, $value, $type, $group, $labelTh, $labelEn]) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => $type,
                    'group' => $group,
                    'label_th' => $labelTh,
                    'label_en' => $labelEn,
                ]
            );
        }
    }
}
