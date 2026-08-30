<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();

            $table->string('name_th', 150);
            $table->string('name_en', 150);
            $table->text('description_th')->nullable();
            $table->text('description_en')->nullable();
            $table->string('suitable_for_th')->nullable()->comment('เหมาะกับใคร');
            $table->string('suitable_for_en')->nullable();

            $table->enum('level', ['all', 'beginner', 'intermediate', 'advanced'])->default('all');
            $table->enum('equipment_type', ['reformer', 'mat', 'cadillac', 'chair', 'mixed'])->default('mat');
            $table->unsignedSmallInteger('duration_min')->default(50);
            $table->unsignedSmallInteger('default_capacity')->default(8);
            $table->decimal('credit_cost', 5, 2)->default(1)->comment('คลาสพิเศษอาจใช้ 2 เครดิต');

            $table->string('color', 20)->nullable()->comment('สีในปฏิทินแอดมิน');
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_types');
    }
};
