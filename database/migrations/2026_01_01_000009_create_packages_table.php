<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * รองรับทั้งแบบนับครั้ง เหมาจ่าย และทดลอง อยู่ในตารางเดียว
     *  - credit_pack : credit_amount = จำนวนครั้ง         ตอนจองตัดเครดิต
     *  - unlimited   : credit_amount = null (ไม่จำกัด)     ตอนจองไม่ตัด เช็คแค่วันหมดอายุ
     *  - trial       : credit_amount = 1                  ซื้อได้ครั้งเดียวต่อคน
     */
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();

            $table->string('name_th', 150);
            $table->string('name_en', 150);
            $table->text('description_th')->nullable();
            $table->text('description_en')->nullable();

            $table->enum('type', ['credit_pack', 'unlimited', 'trial'])->default('credit_pack');
            $table->unsignedSmallInteger('credit_amount')->nullable()->comment('null = unlimited');
            $table->decimal('price', 10, 2);
            $table->decimal('compare_at_price', 10, 2)->nullable()->comment('ราคาก่อนลด ไว้โชว์ขีดฆ่า');
            $table->decimal('price_per_class', 10, 2)->nullable()->comment('ราคาต่อคลาสไว้โชว์ในเมนู เช่น 2,490฿/คลาส');
            $table->unsignedSmallInteger('valid_days')->default(90)->comment('ซื้อแล้วใช้ได้กี่วัน');
            $table->unsignedSmallInteger('valid_months')->nullable()->comment('ไว้โชว์ "Valid 3 month" ตามเมนูลูกค้า valid_days คือตัวที่ใช้คำนวณจริง');

            // เครดิตใช้ข้ามสาขาได้ทุกสาขา แต่จำกัดประเภทคลาสได้
            $table->boolean('all_class_types')->default(true);

            // ราคาต่างกันได้ตามสาขา อารีย์กับสีลมคนละเรต
            $table->boolean('all_branches')->default(true)->comment('false = ขายเฉพาะสาขาใน branch_package');

            // คุมโควตากันคนใช้ unlimited จองรัวทิ้ง ปัญหาจริงของทุกสตูดิโอ
            $table->unsignedSmallInteger('max_per_day')->nullable();
            $table->unsignedSmallInteger('max_per_week')->nullable();
            $table->unsignedSmallInteger('max_future_bookings')->nullable();

            $table->boolean('once_per_customer')->default(false)->comment('true สำหรับแพ็กทดลอง');
            $table->boolean('is_public')->default(true)->comment('false = ขายหน้าร้านเท่านั้น');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('image')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'is_public', 'sort_order'], 'idx_pkg_visible');
        });

        // ถ้า all_class_types = false ระบุได้ว่าแพ็กนี้ใช้กับคลาสไหนได้บ้าง
        Schema::create('class_type_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_type_id')->constrained()->cascadeOnDelete();
            $table->unique(['package_id', 'class_type_id'], 'uniq_pkg_classtype');
        });

        // ถ้า all_branches = false ระบุได้ว่าแพ็กนี้ขายที่สาขาไหนบ้าง
        Schema::create('branch_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->unique(['package_id', 'branch_id'], 'uniq_pkg_branch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_package');
        Schema::dropIfExists('class_type_package');
        Schema::dropIfExists('packages');
    }
};
