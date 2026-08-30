<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SMSMKT เป็นคนสร้างและตรวจ OTP เอง เราไม่ได้ถือรหัส 6 หลัก
     * จึงต้องเก็บ token/ref_code ของเขาไว้อ้างอิงตอน validate
     *
     * code_hash เปลี่ยนเป็น nullable เพราะโหมด SMSMKT ไม่มี hash ให้เก็บ
     * (โหมด log ตอน dev ยังใช้ code_hash เหมือนเดิม)
     */
    public function up(): void
    {
        Schema::table('phone_verification_codes', function (Blueprint $table) {
            $table->string('code_hash')->nullable()->change();

            $table->string('provider', 20)->default('log')->after('purpose');
            $table->string('provider_token', 255)->nullable()->after('provider');
            $table->string('ref_code', 40)->nullable()->after('provider_token');
        });
    }

    public function down(): void
    {
        Schema::table('phone_verification_codes', function (Blueprint $table) {
            $table->dropColumn(['provider', 'provider_token', 'ref_code']);
        });

        // ต้องลบแถวที่ไม่มี hash ก่อน ไม่งั้นเปลี่ยนกลับเป็น NOT NULL ไม่ได้
        \Illuminate\Support\Facades\DB::table('phone_verification_codes')
            ->whereNull('code_hash')->delete();

        Schema::table('phone_verification_codes', function (Blueprint $table) {
            $table->string('code_hash')->nullable(false)->change();
        });
    }
};
