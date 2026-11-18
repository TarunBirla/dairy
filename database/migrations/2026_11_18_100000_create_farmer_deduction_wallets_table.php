<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Farmer Wallets Table
        if (!Schema::hasTable('farmer_wallets')) {
            Schema::create('farmer_wallets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('farmer_id')->unique()->constrained('farmers')->cascadeOnDelete();
                $table->decimal('balance', 12, 2)->default(0.00); // Total outstanding deductions across all categories
                $table->timestamps();
            });
        }

        // 2. Farmer Wallet Transactions Table (Immutable Financial Ledger)
        if (!Schema::hasTable('farmer_wallet_transactions')) {
            Schema::create('farmer_wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('wallet_id')->constrained('farmer_wallets')->cascadeOnDelete();
                $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
                $table->foreignId('farmer_invoice_id')->nullable()->constrained('farmer_invoices')->nullOnDelete();
                $table->string('transaction_type'); // 'credit' (adds outstanding balance), 'debit' (deducts from balance), 'reversal' (reverses prior transaction)
                $table->string('category')->default('General'); // Cattle Feed, Advance / Loan, Doctor Fee, Stationary, Management Charges, Medicine, Other
                $table->decimal('amount', 12, 2);
                $table->decimal('balance_before', 12, 2)->default(0.00);
                $table->decimal('balance_after', 12, 2)->default(0.00);
                $table->string('reference_type')->nullable(); // 'farmer_invoice', 'manual_adjustment', 'farmer_deduction', 'reversal'
                $table->string('reference_id')->nullable();
                $table->unsignedBigInteger('reversal_of_transaction_id')->nullable();
                $table->text('remarks')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['farmer_id', 'category']);
                $table->index(['wallet_id', 'created_at']);
                $table->index('farmer_invoice_id');
            });
        }

        // 3. Add Wallet Columns to farmer_invoices table
        if (Schema::hasTable('farmer_invoices')) {
            Schema::table('farmer_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('farmer_invoices', 'opening_wallet_balance')) {
                    $table->decimal('opening_wallet_balance', 12, 2)->default(0.00)->after('stationary_deduction');
                }
                if (!Schema::hasColumn('farmer_invoices', 'wallet_deducted_amount')) {
                    $table->decimal('wallet_deducted_amount', 12, 2)->default(0.00)->after('opening_wallet_balance');
                }
                if (!Schema::hasColumn('farmer_invoices', 'closing_wallet_balance')) {
                    $table->decimal('closing_wallet_balance', 12, 2)->default(0.00)->after('wallet_deducted_amount');
                }
                if (!Schema::hasColumn('farmer_invoices', 'wallet_deductions_breakdown')) {
                    $table->longText('wallet_deductions_breakdown')->nullable()->after('closing_wallet_balance');
                }
            });
        }

        // 4. Add farmer_invoice_id to milk_collections table
        if (Schema::hasTable('milk_collections')) {
            Schema::table('milk_collections', function (Blueprint $table) {
                if (!Schema::hasColumn('milk_collections', 'farmer_invoice_id')) {
                    $table->foreignId('farmer_invoice_id')->nullable()->after('payment_status')->constrained('farmer_invoices')->nullOnDelete();
                    $table->index('farmer_invoice_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('milk_collections') && Schema::hasColumn('milk_collections', 'farmer_invoice_id')) {
            Schema::table('milk_collections', function (Blueprint $table) {
                $table->dropForeign(['farmer_invoice_id']);
                $table->dropColumn('farmer_invoice_id');
            });
        }

        if (Schema::hasTable('farmer_invoices')) {
            Schema::table('farmer_invoices', function (Blueprint $table) {
                $cols = ['opening_wallet_balance', 'wallet_deducted_amount', 'closing_wallet_balance', 'wallet_deductions_breakdown'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('farmer_invoices', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('farmer_wallet_transactions');
        Schema::dropIfExists('farmer_wallets');
    }
};
