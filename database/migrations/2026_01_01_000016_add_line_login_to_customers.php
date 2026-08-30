<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * รองรับล็อกอินด้วย LINE
     *  - phone เปลี่ยนเป็น nullable เพราะคนที่สมัครผ่าน LINE ยังไม่มีเบอร์ตอนแรก
     *    (unique เดิมยังอยู่ MySQL ยอมให้ NULL ซ้ำกันได้หลายแถว)
     *  - line_user_id ต้อง unique กัน 1 LINE ผูกได้หลายบัญชี
     *  - profile_completed_at ไว้เช็คว่ากรอกข้อมูลที่จำเป็นครบหรือยัง
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->change();

            $table->string('line_display_name', 120)->nullable()->after('line_user_id');
            $table->string('line_picture_url', 500)->nullable()->after('line_display_name');
            $table->timestamp('line_linked_at')->nullable()->after('line_picture_url');
            $table->timestamp('phone_verified_at')->nullable()->after('line_linked_at');
            $table->timestamp('profile_completed_at')->nullable()->after('phone_verified_at');
        });

        // index เดิมเป็นแบบธรรมดา ต้องเปลี่ยนเป็น unique กัน LINE เดียวผูกหลายบัญชี
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['line_user_id']);
            $table->unique('line_user_id');
        });

        // ลูกค้าเก่าที่มีเบอร์อยู่แล้วถือว่าข้อมูลครบ ไม่ต้องไปบังคับกรอกใหม่
        DB::table('customers')
            ->whereNotNull('phone')
            ->update([
                'profile_completed_at' => now(),
                'phone_verified_at' => now(),
            ]);

        /**
         * OTP ยืนยันเบอร์โทร ใช้ตอนผูก LINE เข้ากับบัญชีเดิมที่แอดมินสร้างไว้
         * เก็บเป็น hash ไม่เก็บเลขตรงๆ หลุดไปก็ใช้ไม่ได้
         */
        Schema::create('phone_verification_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 30)->index();
            $table->string('code_hash');
            $table->string('purpose', 30)->default('link_line');

            // เก็บไว้ว่า OTP ใบนี้ออกให้ LINE ไหน กันคนอื่นเอา code ไปใช้ต่อ
            $table->string('line_user_id', 100)->nullable();

            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['phone', 'purpose', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_verification_codes');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['line_user_id']);
            $table->index('line_user_id');

            $table->dropColumn([
                'line_display_name',
                'line_picture_url',
                'line_linked_at',
                'phone_verified_at',
                'profile_completed_at',
            ]);
        });

        // ลูกค้าที่ไม่มีเบอร์ต้องเคลียร์ก่อน ไม่งั้นเปลี่ยนกลับเป็น NOT NULL ไม่ได้
        DB::table('customers')->whereNull('phone')->delete();

        Schema::table('customers', function (Blueprint $table) {
            $table->string('phone', 30)->nullable(false)->change();
        });
    }
};
