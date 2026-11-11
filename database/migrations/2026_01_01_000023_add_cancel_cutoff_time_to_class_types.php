<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * กติกายกเลิกของกรุ๊ปคลาส: ยกเลิกฟรีได้ถึงเวลาที่กำหนดของ "วันก่อนคลาส" (เช่น 22:00)
     * เลยจากนั้นถือว่ายกเลิกช้า ตัดเครดิต
     *
     * null = ใช้กติกาเดิม (ยกเลิกก่อนคลาสเริ่มกี่ชั่วโมง จาก settings.cancel_deadline_hours)
     * ไพรเวท/ดูโอ/รีไววฟ์ยังใช้แบบชั่วโมงเหมือนเดิม
     */
    public function up(): void
    {
        Schema::table('class_types', function (Blueprint $table) {
            $table->string('cancel_cutoff_time', 5)->nullable()->after('credit_cost')
                ->comment('ยกเลิกฟรีได้ถึงกี่โมงของวันก่อนคลาส (HH:MM) null = ใช้จำนวนชั่วโมงจาก settings');
        });

        // กรุ๊ปคลาส (ที่นั่งตั้งแต่ 3 ขึ้นไป: ทรีโอ รีฟอร์มเมอร์กรุ๊ป แมท บาร์) ใช้กติกา 22:00 ของวันก่อนหน้า
        DB::table('class_types')->where('default_capacity', '>=', 3)->update(['cancel_cutoff_time' => '22:00']);
    }

    public function down(): void
    {
        Schema::table('class_types', function (Blueprint $table) {
            $table->dropColumn('cancel_cutoff_time');
        });
    }
};
