<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * บัญชีเดินสะพัดของเครดิต เป็นหลักฐานเวลาลูกค้าถามว่าเครดิตหายไปไหน
     * เปิดดูได้ทันทีว่าถูกตัดเมื่อไหร่ เพราะอะไร ใครทำ
     */
    public function up(): void
    {
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->comment('ผูก FK ทีหลังเพราะ bookings สร้างหลังตารางนี้');

            $table->decimal('amount', 6, 2)->comment('บวก = เพิ่ม, ลบ = ตัด');
            $table->decimal('balance_after', 8, 2)->nullable()->comment('ยอดรวมของลูกค้าหลังรายการนี้');

            $table->enum('type', [
                'purchase',      // ซื้อแพ็ก
                'booking',       // จองคลาส
                'refund',        // ยกเลิกทันเวลา คืนเครดิต
                'late_cancel',   // ยกเลิกช้า ไม่คืน (บันทึกไว้เป็นหลักฐาน)
                'no_show',       // ไม่มาเรียน
                'expire',        // แพ็กหมดอายุ
                'admin_adjust',  // แอดมินปรับเอง
                'compensate',    // ชดเชย เช่น คลาสถูกยกเลิก
            ]);

            $table->string('reason_th')->nullable();
            $table->string('reason_en')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
