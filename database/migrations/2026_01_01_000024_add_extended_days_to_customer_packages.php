<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * จำนวนวันที่แอดมินขยายอายุแพ็กให้ (ปุ่ม "ขยายเวลา" หน้าลูกค้า)
     * เก็บแยกไว้เพราะวันหมดอายุของแพ็กแบบนับจากคลาสแรกถูกคำนวณใหม่ได้เรื่อยๆ
     * (ดู CustomerPackage::syncActivation) ถ้าบวกเข้า expires_at ตรงๆ วันที่ขยายจะหายตอนคำนวณใหม่
     */
    public function up(): void
    {
        Schema::table('customer_packages', function (Blueprint $table) {
            $table->unsignedSmallInteger('extended_days')->default(0)->after('valid_days')
                ->comment('วันที่แอดมินขยายอายุให้ รวมสะสม');
        });
    }

    public function down(): void
    {
        Schema::table('customer_packages', function (Blueprint $table) {
            $table->dropColumn('extended_days');
        });
    }
};
