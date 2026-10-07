<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_charts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('milk_type')->default('cow'); // cow, buffalo, mixed
            $table->string('calculation_type')->default('fat_snf_formula'); // fat_snf_formula, matrix, flat
            $table->decimal('base_rate', 8, 2)->default(35.00);
            $table->decimal('min_fat', 4, 2)->default(3.0);
            $table->decimal('max_fat', 4, 2)->default(10.0);
            $table->decimal('min_snf', 4, 2)->default(8.0);
            $table->decimal('max_snf', 4, 2)->default(10.0);
            $table->decimal('fat_factor', 6, 2)->default(6.50);
            $table->decimal('snf_factor', 6, 2)->default(4.20);
            $table->date('effective_date')->nullable();
            $table->boolean('is_default')->default(true);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('rate_chart_slabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_chart_id')->constrained('rate_charts')->cascadeOnDelete();
            $table->decimal('fat_from', 4, 2);
            $table->decimal('fat_to', 4, 2);
            $table->decimal('snf_from', 4, 2);
            $table->decimal('snf_to', 4, 2);
            $table->decimal('rate', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_chart_slabs');
        Schema::dropIfExists('rate_charts');
    }
};
