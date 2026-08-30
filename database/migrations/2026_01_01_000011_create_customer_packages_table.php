<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * แพ็กที่ลูกค้าถืออยู่ ซื้อ 3 แพ็กก็มี 3 แถว แต่ละแถวหมดอายุคนละวัน
     * ตอนจองระบบตัดจากแพ็กที่ใกล้หมดอายุที่สุดก่อน
     */
    public function up(): void
    {
        Schema::create('customer_packages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', ['credit_pack', 'unlimited', 'trial'])->comment('คัดลอกจาก package ตอนซื้อ');

            $table->timestamp('purchased_at');
            $table->date('starts_at');
            $table->date('expires_at');

            // credit_pack/trial ใช้ 3 ฟิลด์นี้ ส่วน unlimited ปล่อย null
            $table->unsignedSmallInteger('credit_total')->nullable();
            $table->unsignedSmallInteger('credit_used')->default(0);
            $table->unsignedSmallInteger('credit_remaining')->nullable();

            // คัดลอกโควตามาจาก package เผื่อแอดมินแก้แพ็กทีหลัง ใบเก่าต้องไม่เปลี่ยนตาม
            $table->unsignedSmallInteger('max_per_day')->nullable();
            $table->unsignedSmallInteger('max_per_week')->nullable();
            $table->unsignedSmallInteger('max_future_bookings')->nullable();

            $table->enum('status', ['active', 'expired', 'used_up', 'frozen', 'cancelled'])->default('active');
            $table->date('frozen_from')->nullable();
            $table->date('frozen_until')->nullable();
            $table->unsignedSmallInteger('frozen_days_used')->default(0);

            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // ตอนหาแพ็กที่ใช้ได้ จะ query ด้วยชุดนี้บ่อยที่สุด
            $table->index(['customer_id', 'status', 'expires_at'], 'idx_cuspkg_usable');
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_packages');
    }
};
