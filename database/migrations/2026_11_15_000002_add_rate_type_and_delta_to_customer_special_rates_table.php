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
        if (Schema::hasTable('customer_special_rates')) {
            Schema::table('customer_special_rates', function (Blueprint $table) {
                if (!Schema::hasColumn('customer_special_rates', 'rate_type')) {
                    $table->string('rate_type', 20)->default('fixed')->after('special_price'); // fixed, variable
                }
                if (!Schema::hasColumn('customer_special_rates', 'delta_amount')) {
                    $table->decimal('delta_amount', 10, 2)->default(0.00)->after('rate_type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('customer_special_rates')) {
            Schema::table('customer_special_rates', function (Blueprint $table) {
                $columns = ['rate_type', 'delta_amount'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('customer_special_rates', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
