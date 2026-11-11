<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * อายุแพ็กเริ่มนับจากคลาสแรกที่ใช้ ไม่ใช่วันที่ซื้อ
     *
     * แพ็กที่ยังไม่เคยใช้ starts_at/expires_at เป็น null และจำจำนวนวันไว้ที่ valid_days
     * พอจองคลาสแรก ระบบตั้ง starts_at = วันของคลาสนั้น แล้วนับ valid_days ต่อจากนั้น
     * แพ็กเก่าที่ valid_days เป็น null คือแพ็กที่นับจากวันซื้อแบบเดิม ปล่อยไว้ตามนั้น
     */
    public function up(): void
    {
        Schema::table('customer_packages', function (Blueprint $table) {
            $table->date('starts_at')->nullable()->change();
            $table->date('expires_at')->nullable()->change();
            $table->unsignedSmallInteger('valid_days')->nullable()->after('expires_at')
                ->comment('อายุแพ็กนับจากคลาสแรก null = แพ็กแบบเดิมที่นับจากวันซื้อ');
        });
    }

    public function down(): void
    {
        // แพ็กที่ยังไม่เริ่มใช้ ย้อนกลับเป็นนับจากวันซื้อ
        DB::table('customer_packages')->whereNull('expires_at')->orderBy('id')->each(function ($row) {
            $start = \Illuminate\Support\Carbon::parse($row->purchased_at)->startOfDay();

            DB::table('customer_packages')->where('id', $row->id)->update([
                'starts_at' => $start->toDateString(),
                'expires_at' => $start->copy()->addDays((int) $row->valid_days)->toDateString(),
            ]);
        });

        Schema::table('customer_packages', function (Blueprint $table) {
            $table->dropColumn('valid_days');
            $table->date('starts_at')->nullable(false)->change();
            $table->date('expires_at')->nullable(false)->change();
        });
    }
};
