<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * รอบเรียนจริงที่ลูกค้าจองได้ generate มาจาก class_schedules
     * ฟิลด์อย่าง branch_id/capacity คัดลอกมาเก็บซ้ำโดยตั้งใจ
     * เพื่อให้แก้รอบเดี่ยวได้ (ครูลา เปลี่ยนห้อง ลดที่นั่ง) โดยไม่กระทบแม่แบบ
     */
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_schedule_id')->nullable()->constrained()->nullOnDelete()
                ->comment('null = แอดมินสร้างรอบพิเศษเอง ไม่ได้มาจากแม่แบบ');

            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('class_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('substitute_trainer_id')->nullable()->constrained('trainers')->nullOnDelete()
                ->comment('ครูสอนแทน เกิดบ่อยมากในงานจริง');

            $table->dateTime('start_at');
            $table->dateTime('end_at');

            $table->unsignedSmallInteger('capacity');
            $table->decimal('credit_cost', 5, 2)->default(1);

            // เก็บยอดไว้เพื่อไม่ต้องนับใหม่ทุกครั้ง ต้องอัปเดตใน transaction พร้อม lock
            $table->unsignedSmallInteger('booked_count')->default(0);
            $table->unsignedSmallInteger('waitlist_count')->default(0);
            $table->unsignedSmallInteger('attended_count')->default(0);

            $table->enum('status', ['scheduled', 'cancelled', 'completed'])->default('scheduled');
            $table->string('cancel_reason_th')->nullable();
            $table->string('cancel_reason_en')->nullable();
            $table->text('note_th')->nullable();
            $table->text('note_en')->nullable();

            $table->dateTime('booking_opens_at')->nullable();
            $table->dateTime('booking_closes_at')->nullable();

            $table->timestamps();

            // กันระบบ generate รอบซ้ำเวลารันหลายครั้ง
            $table->unique(['class_schedule_id', 'start_at'], 'uniq_schedule_start');

            $table->index(['branch_id', 'start_at', 'status'], 'idx_branch_start');
            $table->index(['start_at', 'status']);
            $table->index(['trainer_id', 'start_at']);
            $table->index(['room_id', 'start_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};
