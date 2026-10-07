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
        // 1. Vehicles Table (Matching media_1791389598973.png)
        if (!Schema::hasTable('vehicles')) {
            Schema::create('vehicles', function (Blueprint $table) {
                $table->id();
                $table->string('vehicle_number')->unique();
                $table->string('vehicle_type')->default('van'); // van, car, tanker, auto, bike, other
                $table->string('driver_name')->nullable();
                $table->string('driver_phone')->nullable();
                $table->decimal('capacity', 10, 2)->default(0.00); // In Liters
                $table->decimal('current_km', 10, 2)->default(0.00);
                $table->decimal('per_km_rate', 10, 2)->default(0.00);
                $table->string('assigned_route')->nullable();
                $table->foreignId('route_id')->nullable()->constrained('delivery_routes')->nullOnDelete();
                $table->string('status')->default('available'); // available, on_duty, maintenance, inactive
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 2. Buyers Table (Commercial Milk Buyers / Bulk Customers - Matching media_1791389622008.png & media_1791389678004.png)
        if (!Schema::hasTable('buyers')) {
            Schema::create('buyers', function (Blueprint $table) {
                $table->id();
                $table->string('buyer_code')->unique();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('milk_type')->default('both'); // cow, buffalo, both
                $table->string('cow_rate_mode')->default('fixed'); // as_per_rate_chart, manual, penalty, bonus, fixed
                $table->decimal('cow_fixed_rate', 10, 2)->default(0.00);
                $table->string('buffalo_rate_mode')->default('fixed'); // as_per_rate_chart, manual, penalty, bonus, fixed
                $table->decimal('buffalo_fixed_rate', 10, 2)->default(0.00);
                $table->text('address')->nullable();
                $table->string('taluka')->nullable();
                $table->string('district')->nullable();
                $table->text('details')->nullable();
                $table->decimal('current_balance', 12, 2)->default(0.00); // debit/credit running balance
                $table->string('status')->default('active'); // active, inactive
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 3. Milk Sales Table (Matching media_1791389566806.png)
        if (!Schema::hasTable('milk_sales')) {
            Schema::create('milk_sales', function (Blueprint $table) {
                $table->id();
                $table->string('sale_number')->unique();
                $table->foreignId('buyer_id')->constrained('buyers')->cascadeOnDelete();
                $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                $table->date('sale_date');
                $table->string('shift')->default('morning'); // morning, evening
                $table->string('milk_type')->default('cow'); // cow, buffalo, mixed
                $table->decimal('quantity_liters', 10, 2);
                $table->decimal('fat_percentage', 4, 2)->default(0.00);
                $table->decimal('snf_percentage', 4, 2)->default(0.00);
                $table->decimal('clr_reading', 5, 2)->default(0.00);
                $table->decimal('rate_per_liter', 10, 2);
                $table->decimal('total_amount', 12, 2);
                $table->decimal('paid_amount', 12, 2)->default(0.00);
                $table->decimal('balance_amount', 12, 2)->default(0.00);
                $table->string('payment_mode')->nullable()->default('Cash'); // Cash, UPI, Bank Transfer, Credit
                $table->text('description')->nullable();
                $table->string('status')->default('completed'); // completed, cancelled
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 4. Buyer Payments / Khata Transactions Table (Matching media_1791389718502.png)
        if (!Schema::hasTable('buyer_payments')) {
            Schema::create('buyer_payments', function (Blueprint $table) {
                $table->id();
                $table->string('receipt_number')->unique();
                $table->foreignId('buyer_id')->constrained('buyers')->cascadeOnDelete();
                $table->foreignId('milk_sale_id')->nullable()->constrained('milk_sales')->nullOnDelete();
                $table->date('payment_date');
                $table->string('type')->default('received'); // received (buyer pays dairy), paid (dairy refunds buyer)
                $table->decimal('amount', 12, 2);
                $table->string('payment_mode')->default('Cash'); // Cash, Bank Transfer, UPI, Cheque
                $table->string('transaction_reference')->nullable();
                $table->text('comment')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_payments');
        Schema::dropIfExists('milk_sales');
        Schema::dropIfExists('buyers');
        Schema::dropIfExists('vehicles');
    }
};
