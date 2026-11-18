<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('buyer_invoices')) {
            Schema::create('buyer_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique();
                $table->foreignId('buyer_id')->constrained('buyers')->cascadeOnDelete();
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('total_quantity', 10, 2)->default(0.00); // Total Milk Sold (Liters)
                $table->decimal('milk_amount', 12, 2)->default(0.00); // Total Sales Value
                $table->decimal('cash_received', 12, 2)->default(0.00); // Payments Received in Period
                $table->decimal('discount_amount', 10, 2)->default(0.00); // Discount Given
                $table->decimal('other_charges', 10, 2)->default(0.00); // Delivery/Transport/Other charges
                $table->decimal('previous_balance', 12, 2)->default(0.00); // Opening Due (Previous Balance)
                $table->decimal('net_receivable', 12, 2)->default(0.00); // Net Due (prev_bal + milk_amount + other_charges - cash_received - discount)
                $table->longText('sales_breakdown')->nullable();
                $table->string('status')->default('pending'); // pending, generated, paid, cancelled
                $table->boolean('is_edited')->default(false);
                $table->string('payment_mode')->nullable()->default('Cash');
                $table->string('payment_reference')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('milk_sales') && !Schema::hasColumn('milk_sales', 'buyer_invoice_id')) {
            Schema::table('milk_sales', function (Blueprint $table) {
                $table->foreignId('buyer_invoice_id')->nullable()->after('status')->constrained('buyer_invoices')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('milk_sales') && Schema::hasColumn('milk_sales', 'buyer_invoice_id')) {
            Schema::table('milk_sales', function (Blueprint $table) {
                $table->dropForeign(['buyer_invoice_id']);
                $table->dropColumn('buyer_invoice_id');
            });
        }
        Schema::dropIfExists('buyer_invoices');
    }
};
