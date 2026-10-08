<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('product_loads')) {
            Schema::create('product_loads', function (Blueprint $table) {
                $table->id();
                $table->string('load_type', 30)->default('counter_sale')->comment('counter_sale or delivery_sale');
                $table->date('date');
                $table->foreignId('delivery_person_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('delivery_person_name')->nullable();
                $table->string('delivery_person_phone', 20)->nullable();
                $table->string('shift', 20)->default('Morning');
                $table->text('remark')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_load_items')) {
            Schema::create('product_load_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_load_id')->constrained('product_loads')->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->string('product_name');
                $table->decimal('quantity', 10, 2)->default(0.00);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_load_items');
        Schema::dropIfExists('product_loads');
    }
};
