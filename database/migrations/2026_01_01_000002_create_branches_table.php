<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique()->comment('เช่น aree, thonglor');

            $table->string('name_th');
            $table->string('name_en');
            $table->string('short_name_th', 60)->nullable()->comment('ชื่อสั้นสำหรับแท็บ/ปุ่ม');
            $table->string('short_name_en', 60)->nullable();
            $table->text('address_th')->nullable();
            $table->text('address_en')->nullable();
            $table->text('direction_th')->nullable()->comment('วิธีเดินทาง เช่น BTS อารีย์ ทางออก 1');
            $table->text('direction_en')->nullable();

            $table->string('phone', 30)->nullable();
            $table->string('line_id', 60)->nullable();
            $table->string('email')->nullable();
            $table->string('google_map_url', 500)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->time('open_time')->default('07:00:00');
            $table->time('close_time')->default('21:00:00');

            // บัญชีรับโอน แต่ละสาขาใช้คนละบัญชีได้ ลูกค้าเห็นตอนกดชำระเงิน
            $table->string('bank_name', 60)->nullable()->comment('เช่น SCB, KBank');
            $table->string('bank_account_name', 150)->nullable();
            $table->string('bank_account_number', 30)->nullable();
            $table->string('promptpay_id', 30)->nullable();

            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
