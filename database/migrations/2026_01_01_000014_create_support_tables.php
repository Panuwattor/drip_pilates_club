<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // วันหยุด/ปิดสาขา ตอน generate รอบเรียนระบบจะข้ามวันเหล่านี้
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->cascadeOnDelete()
                ->comment('null = หยุดทุกสาขา');
            $table->date('date');
            $table->string('reason_th', 150);
            $table->string('reason_en', 150);
            $table->boolean('is_closed_all_day')->default(true);
            $table->time('closed_from')->nullable();
            $table->time('closed_until')->nullable();
            $table->timestamps();

            $table->index(['date', 'branch_id']);
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->cascadeOnDelete()
                ->comment('null = แสดงทุกสาขา');
            $table->string('title_th', 200);
            $table->string('title_en', 200);
            $table->text('body_th')->nullable();
            $table->text('body_en')->nullable();
            $table->string('image')->nullable();
            $table->string('link_url', 500)->nullable();
            $table->enum('type', ['info', 'promo', 'warning'])->default('info');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at'], 'idx_ann_visible');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('type', 60)->comment('booking_confirmed, class_cancelled, credit_expiring, waitlist_promoted');
            $table->string('title_th', 200);
            $table->string('title_en', 200);
            $table->text('body_th')->nullable();
            $table->text('body_en')->nullable();
            $table->json('data')->nullable()->comment('เก็บ id อ้างอิง เช่น booking_id');
            $table->enum('channel', ['in_app', 'line', 'email', 'sms'])->default('in_app');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'read_at', 'created_at'], 'idx_notif_unread');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string')->comment('string|int|bool|json');
            $table->string('group', 50)->default('general');
            $table->string('label_th', 200)->nullable();
            $table->string('label_en', 200)->nullable();
            $table->timestamps();

            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('holidays');
    }
};
