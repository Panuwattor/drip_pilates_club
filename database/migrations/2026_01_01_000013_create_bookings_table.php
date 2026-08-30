<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique()->comment('เช่น BK-2026080001 ใช้เช็คอินหน้าร้าน');

            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_package_id')->nullable()->constrained()->nullOnDelete()
                ->comment('null = แอดมินให้จองฟรี');

            $table->enum('status', [
                'confirmed',
                'waitlisted',
                'cancelled',       // ยกเลิกทันเวลา คืนเครดิต
                'late_cancelled',  // ยกเลิกช้ากว่ากำหนด ไม่คืนเครดิต
                'no_show',
                'attended',
            ])->default('confirmed');

            $table->decimal('credit_used', 5, 2)->default(0);
            $table->timestamp('booked_at');
            $table->enum('booked_via', ['customer', 'admin', 'walk_in'])->default('customer');

            // waitlist
            $table->unsignedSmallInteger('waitlist_position')->nullable();
            $table->timestamp('promoted_at')->nullable()->comment('เวลาที่ถูกเลื่อนจากคิวขึ้นเป็น confirmed');

            // ยกเลิก
            $table->timestamp('cancelled_at')->nullable();
            $table->enum('cancelled_by', ['customer', 'admin', 'system'])->nullable();
            $table->string('cancel_reason')->nullable();
            $table->boolean('credit_refunded')->default(false);

            // เช็คอิน
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();

            // รีวิวหลังเรียน
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('review')->nullable();

            $table->timestamps();

            // กันจองซ้ำรอบเดียวกัน สำคัญมาก
            $table->unique(['customer_id', 'class_session_id'], 'uniq_customer_session');

            $table->index(['class_session_id', 'status'], 'idx_session_status');
            $table->index(['customer_id', 'status', 'booked_at'], 'idx_customer_status');
            $table->index(['status', 'created_at']);
        });

        // ผูก FK ที่ค้างไว้จาก credit_transactions
        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->foreign('booking_id')->references('id')->on('bookings')->nullOnDelete();
        });

        Schema::create('booking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->enum('actor_type', ['customer', 'admin', 'system'])->default('system');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['booking_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_logs');

        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
        });

        Schema::dropIfExists('bookings');
    }
};
