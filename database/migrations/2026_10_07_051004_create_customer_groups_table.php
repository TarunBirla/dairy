<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 30)->default('#10B981');
            $table->timestamps();
        });

        Schema::create('customer_group_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('customer_group_id')->constrained('customer_groups')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['customer_id', 'customer_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_group_pivot');
        Schema::dropIfExists('customer_groups');
    }
};
