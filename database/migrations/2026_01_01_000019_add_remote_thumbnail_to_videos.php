<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * remote_thumbnail = รูปปกที่ระบบดึงมาอัตโนมัติจากตัวคลิป (og:image / oEmbed)
     * แยกจาก thumbnail ที่แอดมินอัปเอง — ตอนแสดงผลใช้ thumbnail ก่อน ถ้าไม่มีค่อยใช้ remote_thumbnail
     */
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('remote_thumbnail', 1000)->nullable()->after('thumbnail');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('remote_thumbnail');
        });
    }
};
