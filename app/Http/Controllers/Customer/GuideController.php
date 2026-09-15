<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Setting;

/**
 * คู่มือการใช้งานสำหรับลูกค้า — เนื้อหาอยู่ในวิว สองภาษาด้วย __t()
 * ภาพประกอบถ่ายจากมือถือจริง เก็บที่ public/docs/guide/img (แยกไฟล์ -th / -en)
 */
class GuideController extends Controller
{
    public function index()
    {
        return view('customer.guide', [
            // ดึงกฎที่ลูกค้าต้องรู้จากตั้งค่าระบบ คู่มือจะได้ไม่ขัดกับระบบจริงเวลาแอดมินแก้ค่า
            'cancelHours' => (int) Setting::get('cancel_deadline_hours', 6),
            'closeMinutes' => (int) Setting::get('booking_close_minutes_before', 30),
            'openDays' => (int) Setting::get('booking_open_days_ahead', 90),
            'waitlistEnabled' => (bool) Setting::get('waitlist_enabled', true),
            'lateCancelCharges' => (bool) Setting::get('late_cancel_charge_credit', true),
            'noShowCharges' => (bool) Setting::get('no_show_charge_credit', true),
            'bankName' => Setting::get('payment_bank_name'),
            'bankAccountName' => Setting::get('payment_bank_account_name'),
            'bankAccountNo' => Setting::get('payment_bank_account_no'),
            'lineUrl' => Setting::get('contact_line_url'),
        ]);
    }
}
