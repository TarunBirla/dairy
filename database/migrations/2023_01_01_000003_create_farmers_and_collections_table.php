<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->string('farmer_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('collection_center_id')->nullable()->constrained('collection_centers')->nullOnDelete();
            $table->foreignId('rate_chart_id')->nullable()->constrained('rate_charts')->nullOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('village')->nullable();
            $table->text('address')->nullable();
            $table->string('supplier_type')->default('farmer'); // farmer, bulk_supplier
            $table->string('animal_type')->default('cow'); // cow, buffalo, mixed
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('upi_id')->nullable();
            $table->decimal('custom_rate_override', 8, 2)->nullable();
            $table->decimal('current_balance', 12, 2)->default(0.00); // positive means dairy owes farmer
            $table->string('status')->default('active'); // active, inactive, blocked
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('milk_collections', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
            $table->foreignId('collection_center_id')->nullable()->constrained('collection_centers')->nullOnDelete();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->date('collection_date');
            $table->string('shift'); // morning, evening
            $table->string('milk_type')->default('cow'); // cow, buffalo, mixed
            $table->decimal('quantity_liters', 8, 2);
            $table->decimal('fat', 4, 2)->default(4.00);
            $table->decimal('snf', 4, 2)->default(8.50);
            $table->decimal('clr', 5, 2)->nullable();
            $table->decimal('calculated_rate', 8, 2);
            $table->decimal('applied_rate', 8, 2);
            $table->decimal('gross_amount', 10, 2);
            $table->decimal('bonus', 8, 2)->default(0.00);
            $table->decimal('deduction', 8, 2)->default(0.00);
            $table->decimal('net_amount', 10, 2);
            $table->string('payment_status')->default('pending'); // pending, settled
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('farmer_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('advance_date');
            $table->string('purpose')->nullable();
            $table->decimal('deducted_amount', 10, 2)->default(0.00);
            $table->string('status')->default('pending'); // pending, partially_deducted, recovered
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('farmer_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
            $table->decimal('principal_amount', 12, 2);
            $table->integer('installments_count')->default(1);
            $table->decimal('installment_amount', 10, 2)->default(0.00);
            $table->decimal('total_repaid', 12, 2)->default(0.00);
            $table->decimal('remaining_amount', 12, 2);
            $table->date('start_date');
            $table->string('status')->default('active'); // active, closed
            $table->timestamps();
        });

        Schema::create('farmer_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_number')->unique();
            $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_liters', 10, 2);
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('bonus_amount', 10, 2)->default(0.00);
            $table->decimal('deduction_amount', 10, 2)->default(0.00);
            $table->decimal('advance_recovered', 10, 2)->default(0.00);
            $table->decimal('net_payable', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->string('payment_mode')->nullable(); // Cash, UPI, Bank
            $table->string('payment_reference')->nullable();
            $table->string('status')->default('unsettled'); // unsettled, partial, paid
            $table->unsignedBigInteger('settled_by')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('farmer_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->string('type'); // credit (milk value), debit (settlement payment, advance)
            $table->decimal('amount', 12, 2);
            $table->decimal('balance', 12, 2);
            $table->string('reference_type')->nullable(); // milk_collection, advance, settlement
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_ledgers');
        Schema::dropIfExists('farmer_settlements');
        Schema::dropIfExists('farmer_loans');
        Schema::dropIfExists('farmer_advances');
        Schema::dropIfExists('milk_collections');
        Schema::dropIfExists('farmers');
    }
};
