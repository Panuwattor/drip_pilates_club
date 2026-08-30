<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique()->comment('เช่น ORD-2026080001');
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete()->comment('ซื้อที่สาขาไหน ไว้ดูรายงาน');

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('discount_note')->nullable();
            $table->decimal('total', 10, 2)->default(0);

            $table->enum('status', ['pending', 'paid', 'cancelled', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('พนักงานที่เปิดบิล');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();

            // เก็บชื่อ ณ วันที่ขาย เผื่อแอดมินเปลี่ยนชื่อแพ็กทีหลัง ใบเสร็จเก่าต้องไม่เปลี่ยนตาม
            $table->string('name_th_snapshot', 150);
            $table->string('name_en_snapshot', 150);
            $table->decimal('unit_price', 10, 2);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 10, 2);
            $table->enum('method', ['cash', 'transfer', 'promptpay', 'credit_card', 'other'])->default('transfer');
            $table->string('reference', 100)->nullable()->comment('เลขอ้างอิง/เลขที่สลิป');
            $table->string('slip_image')->nullable()->comment('รูปสลิปที่ลูกค้าแนบมา');
            $table->timestamp('paid_at')->nullable();

            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('reject_reason')->nullable();

            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
