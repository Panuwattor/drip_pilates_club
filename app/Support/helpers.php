<?php

if (! function_exists('__t')) {
    /**
     * เลือกข้อความตามภาษาปัจจุบัน สำหรับข้อความสั้นๆ ใน Blade
     * ที่ไม่ได้เก็บในฐานข้อมูล เช่น "จองเลย" / "Book Now"
     */
    function __t(string $th, string $en): string
    {
        return app()->getLocale() === 'en' ? $en : $th;
    }
}
