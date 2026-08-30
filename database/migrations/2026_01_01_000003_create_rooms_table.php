<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->string('name_th', 120);
            $table->string('name_en', 120);
            $table->unsignedSmallInteger('capacity')->default(10)->comment('ความจุห้องจริง เป็นเพดานของคลาส');
            $table->enum('equipment_type', ['reformer', 'mat', 'cadillac', 'chair', 'mixed'])->default('mixed');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['branch_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
