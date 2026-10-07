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
        if (!Schema::hasTable('product_sales')) {
            Schema::create('product_sales', function (Blueprint $table) {
                $table->id();
                $table->string('sale_number')->unique();
                $table->date('sale_date');
                $table->foreignId('farmer_id')->nullable()->constrained('farmers')->nullOnDelete();
                $table->string('farmer_code')->nullable();
                $table->string('farmer_name');
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('product_name');
                $table->string('unit')->default('kg');
                $table->decimal('quantity', 10, 2);
                $table->decimal('rate', 10, 2);
                $table->decimal('total_amount', 12, 2);
                $table->boolean('is_paid')->default(true);
                $table->decimal('paid_amount', 12, 2)->default(0.00);
                $table->decimal('remaining_amount', 12, 2)->default(0.00);
                $table->string('payment_mode')->default('Cash'); // Cash, UPI, Bank Transfer, Ledger
                $table->text('remarks')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sales');
    }
};
