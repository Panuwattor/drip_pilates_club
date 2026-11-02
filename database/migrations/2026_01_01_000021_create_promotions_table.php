<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * โปรโมชั่นแบบมีช่วงเวลา เช่น "ลด 20% ทุกแพ็ก 1-31 ม.ค."
     *  - percent : discount_value = เปอร์เซ็นต์ที่ลด (0-100)
     *  - fixed   : discount_value = จำนวนเงินที่ลด (ลดไม่เกินราคาแพ็ก)
     * active = is_active จริง และ now() อยู่ในช่วง starts_at..ends_at
     */
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();

            $table->string('name_th', 150);
            $table->string('name_en', 150);

            $table->enum('discount_type', ['percent', 'fixed'])->default('percent');
            $table->decimal('discount_value', 10, 2);

            // true = ลดทุกแพ็ก, false = ลดเฉพาะแพ็กใน package_promotion
            $table->boolean('applies_to_all')->default(true);

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            // ปิดชั่วคราวได้โดยไม่ต้องลบ หรือเลื่อนช่วงเวลา
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at'], 'idx_promo_active');
        });

        // ถ้า applies_to_all = false ระบุได้ว่าโปรนี้ใช้กับแพ็กไหนบ้าง
        Schema::create('package_promotion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->unique(['promotion_id', 'package_id'], 'uniq_promo_pkg');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_promotion');
        Schema::dropIfExists('promotions');
    }
};
