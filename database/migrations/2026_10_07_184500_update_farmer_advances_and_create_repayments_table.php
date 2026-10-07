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
        if (Schema::hasTable('farmer_advances')) {
            Schema::table('farmer_advances', function (Blueprint $table) {
                if (!Schema::hasColumn('farmer_advances', 'voucher_no')) {
                    $table->string('voucher_no')->nullable()->after('advance_date');
                }
                if (!Schema::hasColumn('farmer_advances', 'interest_rate')) {
                    $table->decimal('interest_rate', 5, 2)->default(0.00)->after('amount');
                }
                if (!Schema::hasColumn('farmer_advances', 'paid_amount')) {
                    $table->decimal('paid_amount', 10, 2)->default(0.00)->after('deducted_amount');
                }
                if (!Schema::hasColumn('farmer_advances', 'paid_date')) {
                    $table->date('paid_date')->nullable()->after('paid_amount');
                }
                if (!Schema::hasColumn('farmer_advances', 'interest_balance')) {
                    $table->decimal('interest_balance', 10, 2)->default(0.00)->after('paid_date');
                }
                if (!Schema::hasColumn('farmer_advances', 'payment_mode')) {
                    $table->string('payment_mode')->default('Cash')->after('interest_balance');
                }
                if (!Schema::hasColumn('farmer_advances', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (!Schema::hasTable('farmer_advance_repayments')) {
            Schema::create('farmer_advance_repayments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('farmer_advance_id')->constrained('farmer_advances')->cascadeOnDelete();
                $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
                $table->date('repayment_date');
                $table->decimal('amount', 10, 2);
                $table->string('payment_mode')->default('Cash');
                $table->text('remark')->nullable();
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
        Schema::dropIfExists('farmer_advance_repayments');
    }
};
