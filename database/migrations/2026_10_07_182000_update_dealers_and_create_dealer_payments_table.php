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
        if (!Schema::hasTable('dealers')) {
            Schema::create('dealers', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->text('details')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('account_number')->nullable();
                $table->string('ifsc_code')->nullable();
                $table->string('branch')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('dealers', function (Blueprint $table) {
                if (!Schema::hasColumn('dealers', 'details')) {
                    $table->text('details')->nullable()->after('address');
                }
                if (!Schema::hasColumn('dealers', 'bank_name')) {
                    $table->string('bank_name')->nullable()->after('details');
                }
                if (!Schema::hasColumn('dealers', 'account_number')) {
                    $table->string('account_number')->nullable()->after('bank_name');
                }
                if (!Schema::hasColumn('dealers', 'ifsc_code')) {
                    $table->string('ifsc_code')->nullable()->after('account_number');
                }
                if (!Schema::hasColumn('dealers', 'branch')) {
                    $table->string('branch')->nullable()->after('ifsc_code');
                }
            });
        }

        if (!Schema::hasTable('dealer_payments')) {
            Schema::create('dealer_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dealer_id')->constrained('dealers')->cascadeOnDelete();
                $table->string('dealer_code')->nullable();
                $table->string('dealer_name');
                $table->date('payment_date');
                $table->decimal('dues_amount', 12, 2)->default(0.00);
                $table->decimal('pay_amount', 12, 2)->default(0.00);
                $table->decimal('remaining_dues', 12, 2)->default(0.00);
                $table->string('payment_mode')->default('UPI'); // UPI, Cash, Bank Transfer, Cheque
                $table->text('comment')->nullable();
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
        Schema::dropIfExists('dealer_payments');
    }
};
