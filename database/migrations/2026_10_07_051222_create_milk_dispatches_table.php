<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milk_dispatches', function (Blueprint $table) {
            $table->id();
            $table->string('dispatch_number')->unique();
            $table->date('dispatch_date');
            $table->enum('shift', ['morning', 'evening'])->default('morning');
            $table->foreignId('route_id')->nullable()->constrained('delivery_routes')->nullOnDelete();
            $table->foreignId('delivery_boy_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('vehicle_number')->nullable();
            $table->decimal('total_milk_quantity', 10, 2); // Liters
            $table->decimal('fat', 4, 2)->nullable();
            $table->decimal('snf', 4, 2)->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->integer('bottles_loaded')->default(0);
            $table->integer('crates_loaded')->default(0);
            $table->time('dispatched_at')->nullable();
            $table->time('returned_at')->nullable();
            $table->enum('status', ['prepared', 'in_transit', 'delivered', 'returned'])->default('prepared');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milk_dispatches');
    }
};
