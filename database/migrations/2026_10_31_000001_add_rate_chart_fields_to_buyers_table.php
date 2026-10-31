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
        if (Schema::hasTable('buyers')) {
            Schema::table('buyers', function (Blueprint $table) {
                if (!Schema::hasColumn('buyers', 'cow_rate_chart_id')) {
                    $table->unsignedBigInteger('cow_rate_chart_id')->nullable()->after('cow_rate_mode');
                }
                if (!Schema::hasColumn('buyers', 'cow_rate_value')) {
                    $table->decimal('cow_rate_value', 10, 2)->nullable()->default(0.00)->after('cow_rate_chart_id');
                }
                if (!Schema::hasColumn('buyers', 'buffalo_rate_chart_id')) {
                    $table->unsignedBigInteger('buffalo_rate_chart_id')->nullable()->after('buffalo_rate_mode');
                }
                if (!Schema::hasColumn('buyers', 'buffalo_rate_value')) {
                    $table->decimal('buffalo_rate_value', 10, 2)->nullable()->default(0.00)->after('buffalo_rate_chart_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('buyers')) {
            Schema::table('buyers', function (Blueprint $table) {
                $columns = ['cow_rate_chart_id', 'cow_rate_value', 'buffalo_rate_chart_id', 'buffalo_rate_value'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('buyers', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
