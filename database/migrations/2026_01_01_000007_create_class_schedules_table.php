<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * แม่แบบตารางประจำสัปดาห์ เช่น "ทุกอังคาร 08:00 สาขาอารีย์ ห้อง 2 ครูแนน"
     * แอดมินตั้งครั้งเดียว แล้วให้ระบบ generate เป็น class_sessions ล่วงหน้า
     */
    public function up(): void
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('class_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedTinyInteger('day_of_week')->comment('0=อาทิตย์ ... 6=เสาร์ ตรงกับ JS getDay()');
            $table->time('start_time');
            $table->unsignedSmallInteger('duration_min');
            $table->unsignedSmallInteger('capacity');
            $table->decimal('credit_cost', 5, 2)->default(1);

            $table->date('effective_from');
            $table->date('effective_until')->nullable()->comment('null = ใช้ไปเรื่อยๆ');

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['branch_id', 'day_of_week', 'is_active']);
            $table->index(['effective_from', 'effective_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
    }
};
