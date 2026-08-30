<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();

            $table->string('name_th', 120);
            $table->string('name_en', 120);
            $table->string('nickname_th', 60)->nullable();
            $table->string('nickname_en', 60)->nullable();
            $table->text('bio_th')->nullable();
            $table->text('bio_en')->nullable();
            $table->string('specialties_th')->nullable()->comment('คั่นด้วย comma');
            $table->string('specialties_en')->nullable();
            $table->string('certifications_th')->nullable();
            $table->string('certifications_en')->nullable();

            $table->string('avatar')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();

            // เทรนเนอร์ไม่ต้องล็อกอิน ใช้ token นี้เปิดดูตารางตัวเองผ่าน /trainer/{token}
            $table->string('public_token', 64)->unique();

            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        // เทรนเนอร์คนหนึ่งสอนได้หลายสาขา
        Schema::create('branch_trainer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainer_id')->constrained()->cascadeOnDelete();
            $table->unique(['branch_id', 'trainer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_trainer');
        Schema::dropIfExists('trainers');
    }
};
