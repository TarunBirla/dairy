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
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (!Schema::hasColumn('customers', 'billing_period')) {
                    $table->string('billing_period', 50)->default('monthly')->after('category');
                }
                if (!Schema::hasColumn('customers', 'account_type')) {
                    $table->string('account_type', 50)->default('postpaid')->after('billing_period');
                }
                if (!Schema::hasColumn('customers', 'stop_delivery_on_min_balance')) {
                    $table->boolean('stop_delivery_on_min_balance')->default(false)->after('credit_limit');
                }
                if (!Schema::hasColumn('customers', 'min_balance')) {
                    $table->decimal('min_balance', 10, 2)->default(0.00)->after('stop_delivery_on_min_balance');
                }
            });
        }

        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                if (!Schema::hasColumn('subscriptions', 'custom_days')) {
                    $table->json('custom_days')->nullable()->after('frequency');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $columns = ['billing_period', 'account_type', 'stop_delivery_on_min_balance', 'min_balance'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('customers', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
