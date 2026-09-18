<?php

namespace App\Exceptions;

use Exception;

/**
 * ข้อผิดพลาดที่เกิดจากเงื่อนไขการจอง ไม่ใช่บั๊ก
 * ใช้ code สั้นๆ แล้วแปลงเป็นข้อความ 2 ภาษาตอนแสดงผล
 */
class BookingException extends Exception
{
    public function __construct(public readonly string $reason)
    {
        parent::__construct($reason);
    }

    private const MESSAGES = [
        'no_active_package' => [
            'th' => 'คุณยังไม่มีแพ็กเกจที่ใช้งานได้ กรุณาซื้อแพ็กเกจก่อนจองคลาส',
            'en' => 'You have no active package. Please purchase one before booking.',
        ],
        'insufficient_credit' => [
            'th' => 'เครดิตคงเหลือไม่พอสำหรับคลาสนี้',
            'en' => 'You do not have enough credits for this class.',
        ],
        'package_not_valid_for_class' => [
            'th' => 'แพ็กเกจของคุณใช้กับคลาสประเภทนี้ไม่ได้ กรุณาเลือกคลาสอื่นหรือซื้อแพ็กเกจที่ตรงกับคลาสนี้',
            'en' => 'Your package cannot be used for this class type. Please choose another class or purchase a matching package.',
        ],
        'package_not_valid_for_branch' => [
            'th' => 'แพ็กเกจของคุณใช้ได้เฉพาะบางสาขา ใช้กับคลาสของสาขานี้ไม่ได้',
            'en' => 'Your package is only valid at certain branches and cannot be used for this branch.',
        ],
        'quota_exceeded' => [
            'th' => 'คุณจองครบโควตาของแพ็กเกจแล้ว กรุณาลองใหม่ในวันถัดไป',
            'en' => 'You have reached your package booking limit. Please try again another day.',
        ],
        'already_booked' => [
            'th' => 'คุณจองคลาสรอบนี้ไว้แล้ว',
            'en' => 'You have already booked this class.',
        ],
        'class_full' => [
            'th' => 'คลาสนี้เต็มแล้ว',
            'en' => 'This class is full.',
        ],
        'waitlist_full' => [
            'th' => 'คิวสำรองเต็มแล้ว',
            'en' => 'The waitlist is full.',
        ],
        'session_not_available' => [
            'th' => 'คลาสรอบนี้ไม่เปิดให้จอง',
            'en' => 'This class is not available for booking.',
        ],
        'session_already_started' => [
            'th' => 'คลาสรอบนี้เริ่มไปแล้ว',
            'en' => 'This class has already started.',
        ],
        'session_already_ended' => [
            'th' => 'คลาสรอบนี้จบไปแล้ว บันทึก walk-in ย้อนหลังไม่ได้',
            'en' => 'This class has already ended; a walk-in cannot be recorded.',
        ],
        'booking_closed' => [
            'th' => 'ปิดรับจองคลาสรอบนี้แล้ว',
            'en' => 'Booking for this class has closed.',
        ],
        'booking_not_open' => [
            'th' => 'ยังไม่ถึงเวลาเปิดจองคลาสรอบนี้',
            'en' => 'Booking for this class has not opened yet.',
        ],
        'booking_not_active' => [
            'th' => 'การจองนี้ถูกยกเลิกหรือใช้ไปแล้ว',
            'en' => 'This booking is already cancelled or completed.',
        ],
        'booking_not_confirmed' => [
            'th' => 'การจองนี้ไม่ได้อยู่ในสถานะยืนยัน',
            'en' => 'This booking is not in a confirmed state.',
        ],
        'booking_not_reopenable' => [
            'th' => 'การจองนี้ย้อนกลับไม่ได้',
            'en' => 'This booking cannot be reopened.',
        ],
        'invalid_credit_amount' => [
            'th' => 'จำนวนเครดิตต้องมากกว่า 0',
            'en' => 'Credit amount must be greater than zero.',
        ],
        'no_package_to_credit' => [
            'th' => 'ลูกค้าไม่มีแพ็กที่ยังใช้ได้ ให้ขายแพ็กใหม่ก่อน',
            'en' => 'Customer has no usable package — sell a new package first.',
        ],
    ];

    public function localizedMessage(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $locale = in_array($locale, ['th', 'en'], true) ? $locale : 'th';

        return self::MESSAGES[$this->reason][$locale]
            ?? self::MESSAGES[$this->reason]['th']
            ?? $this->reason;
    }
}
