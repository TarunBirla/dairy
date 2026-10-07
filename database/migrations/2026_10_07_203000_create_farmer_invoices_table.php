<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('farmer_invoices')) {
            Schema::create('farmer_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique();
                $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('total_quantity', 10, 2)->default(0.00); // Milks quantity (liters)
                $table->decimal('milk_amount', 10, 2)->default(0.00); // Milk Amount (C)
                $table->decimal('stationary_deduction', 10, 2)->default(0.00); // Stationary
                $table->decimal('feed_deduction', 10, 2)->default(0.00); // Feed
                $table->decimal('advance_deduction', 10, 2)->default(0.00); // In Hand Advance
                $table->decimal('other_deduction', 10, 2)->default(0.00);
                $table->decimal('total_deduction', 10, 2)->default(0.00); // Total Deduction (A)
                $table->decimal('total_credit', 10, 2)->default(0.00); // Total Credit (B)
                $table->decimal('total_amount', 10, 2)->default(0.00); // Total Amount (B + C)
                $table->decimal('previous_balance', 10, 2)->default(0.00); // Balance
                $table->decimal('net_payment', 10, 2)->default(0.00); // Payment (B + C - A)
                $table->string('status')->default('generated'); // generated, paid, cancelled
                $table->string('payment_mode')->nullable()->default('Bank Transfer');
                $table->string('payment_reference')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_invoices');
    }
};
