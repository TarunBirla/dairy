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
        // 1. Drivers Table (Matching media_1791389924187.png)
        if (!Schema::hasTable('drivers')) {
            Schema::create('drivers', function (Blueprint $table) {
                $table->id();
                $table->string('driver_code')->unique();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('license_number')->nullable();
                $table->string('aadhaar_card_no')->nullable();
                $table->string('pan_card_no')->nullable();
                $table->string('account_holder')->nullable();
                $table->string('account_number')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('ifsc_code')->nullable();
                $table->string('bank_branch')->nullable();
                $table->string('status')->default('active'); // active, inactive
                $table->text('address')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // Add driver_id to vehicles if not present
        if (Schema::hasTable('vehicles') && !Schema::hasColumn('vehicles', 'driver_id')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->foreignId('driver_id')->nullable()->after('vehicle_type')->constrained('drivers')->nullOnDelete();
            });
        }

        // 2. Vehicle Advances Table (Matching media_1791389941427.png & media_1791389958966.png)
        if (!Schema::hasTable('vehicle_advances')) {
            Schema::create('vehicle_advances', function (Blueprint $table) {
                $table->id();
                $table->string('voucher_no')->unique();
                $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
                $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
                $table->date('advance_date');
                $table->decimal('amount', 10, 2);
                $table->decimal('interest_rate', 5, 2)->default(0.00);
                $table->decimal('paid_amount', 10, 2)->default(0.00);
                $table->decimal('balance_amount', 10, 2)->default(0.00);
                $table->text('remarks')->nullable();
                $table->string('status')->default('active'); // active, settled
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 3. Vehicle Advance Repayments (Matching media_1791389941427.png "Receive" action)
        if (!Schema::hasTable('vehicle_advance_repayments')) {
            Schema::create('vehicle_advance_repayments', function (Blueprint $table) {
                $table->id();
                $table->string('receipt_no')->unique();
                $table->foreignId('vehicle_advance_id')->constrained('vehicle_advances')->cascadeOnDelete();
                $table->date('repayment_date');
                $table->decimal('amount', 10, 2);
                $table->string('payment_mode')->default('Cash'); // Cash, Bank Transfer, UPI, Cheque
                $table->text('remarks')->nullable();
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
        Schema::dropIfExists('vehicle_advance_repayments');
        Schema::dropIfExists('vehicle_advances');
        if (Schema::hasTable('vehicles') && Schema::hasColumn('vehicles', 'driver_id')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->dropForeign(['driver_id']);
                $table->dropColumn('driver_id');
            });
        }
        Schema::dropIfExists('drivers');
    }
};
