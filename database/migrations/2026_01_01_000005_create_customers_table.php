<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique()->comment('รหัสสมาชิก เช่น DP-00042');

            $table->string('first_name', 120);
            $table->string('last_name', 120)->nullable();
            $table->string('nickname', 60)->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone', 30)->unique()->comment('ใช้ล็อกอินเป็นหลัก คนไทยจำเบอร์ง่ายกว่าอีเมล');
            $table->string('password')->nullable()->comment('null = แอดมินสร้างให้ ยังไม่ตั้งรหัส');

            $table->date('birth_date')->nullable();
            $table->enum('gender', ['female', 'male', 'other'])->nullable();
            $table->string('avatar')->nullable();
            $table->string('line_user_id', 100)->nullable()->index();

            $table->foreignId('home_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('preferred_locale', 5)->default('th');

            // ข้อมูลสุขภาพ สำคัญมากสำหรับพิลาทิส ครูต้องเห็นก่อนสอน
            $table->text('medical_note')->nullable();
            $table->boolean('is_pregnant')->default(false);
            $table->string('emergency_contact_name', 120)->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();

            $table->enum('status', ['active', 'inactive', 'banned'])->default('active');
            $table->text('admin_note')->nullable()->comment('โน้ตภายใน ลูกค้าไม่เห็น');

            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['first_name', 'last_name']);
        });

        Schema::create('customer_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_password_reset_tokens');
        Schema::dropIfExists('customers');
    }
};
