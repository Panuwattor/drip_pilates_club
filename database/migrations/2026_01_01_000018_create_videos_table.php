<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * คลิปวิดีโอ/รีลที่แอดมินเพิ่มเองแล้วมาแสดงหน้าแรก (landing)
     * เก็บแค่ URL ของคลิป (Instagram / YouTube / TikTok / Facebook)
     * ระบบสร้าง embed ที่ปลอดภัยให้เอง ไม่รับ HTML ดิบจากแอดมิน กัน XSS
     */
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title_th', 200)->nullable();
            $table->string('title_en', 200)->nullable();
            $table->text('caption_th')->nullable();
            $table->text('caption_en')->nullable();

            // URL ต้นทางของคลิป — ตัวเดียวที่แอดมินต้องกรอกจริงๆ
            $table->string('url', 500);
            // ตรวจจับจาก url ตอนบันทึก ไว้เลือกวิธี embed ให้ถูก
            $table->enum('provider', ['instagram', 'youtube', 'tiktok', 'facebook'])->default('instagram');
            // รูปปกไว้โชว์ก่อนคลิกเล่น (บาง provider embed แล้วหนัก โหลดแบบ lazy)
            $table->string('thumbnail')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order'], 'idx_video_visible');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
