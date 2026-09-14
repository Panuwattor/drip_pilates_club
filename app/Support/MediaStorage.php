<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * จัดการไฟล์อัปโหลดของแอดมิน (รูปประกาศ, avatar ครู, ปกคลิป, สลิป)
 * ให้ทำงานได้ทั้งเก็บในเครื่อง (disk "public") และ Cloudflare R2 (disk "r2")
 * โดยไม่ต้องแก้ view — ค่าที่คืนออกไปส่งเข้า asset() แล้วได้ URL ถูกทั้งสองแบบ:
 *
 *   - public : คืน path แบบ relative "storage/videos/ab.jpg"
 *              asset() จะต่อ APP_URL ให้เอง (พฤติกรรมเดิม ของเก่าไม่พัง)
 *   - r2     : คืน URL เต็ม "https://pub-xxx.r2.dev/videos/ab.jpg"
 *              asset() เห็นว่าเป็น URL สมบูรณ์แล้วจะคืนกลับตรงๆ ไม่ต่ออะไร
 */
class MediaStorage
{
    /**
     * เก็บไฟล์ลงโฟลเดอร์ที่ระบุ แล้วคืนค่าที่เอาไปแสดงผ่าน asset() ได้เลย
     */
    public static function store(UploadedFile $file, string $folder): string
    {
        $disk = static::disk();
        $path = $file->store($folder, $disk);

        // ดิสก์คลาวด์อาจคืน false ถ้าอัปโหลดไม่สำเร็จ — กันไม่ให้บันทึก path ว่างลง DB
        if ($path === false || $path === '') {
            throw new RuntimeException("อัปโหลดไฟล์ไปยังดิสก์ [{$disk}] ไม่สำเร็จ");
        }

        return static::urlFor($path, $disk);
    }

    /**
     * ชื่อดิสก์ที่ใช้เก็บ media (public | r2 | ...) จาก config('filesystems.media')
     */
    public static function disk(): string
    {
        return config('filesystems.media', 'public');
    }

    /**
     * แปลง path ภายในดิสก์ให้เป็นค่าที่ asset() แสดงถูก
     */
    protected static function urlFor(string $path, string $disk): string
    {
        // ดิสก์ในเครื่อง: เก็บ relative path ให้ asset() ต่อ APP_URL เอง (เหมือนเดิม)
        if ($disk === 'public') {
            return 'storage/' . $path;
        }

        // ดิสก์คลาวด์ (R2/S3): เก็บ URL เต็มจาก disk->url() เพราะ host คนละที่กับเว็บ
        return Storage::disk($disk)->url($path);
    }
}
