<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            if (!Schema::hasColumn('farmers', 'pricing_type')) {
                $table->string('pricing_type', 30)->default('rate_chart')->after('rate_chart_id');
            }
            if (!Schema::hasColumn('farmers', 'rate_chart_id')) {
                $table->foreignId('rate_chart_id')->nullable()->after('collection_center_id')->constrained('rate_charts')->nullOnDelete();
            }
            if (!Schema::hasColumn('farmers', 'cow_milk_rate')) {
                $table->decimal('cow_milk_rate', 8, 2)->nullable()->after('pricing_type');
            }
            if (!Schema::hasColumn('farmers', 'buffalo_milk_rate')) {
                $table->decimal('buffalo_milk_rate', 8, 2)->nullable()->after('cow_milk_rate');
            }
        });

        Schema::table('milk_collections', function (Blueprint $table) {
            if (!Schema::hasColumn('milk_collections', 'pricing_type')) {
                $table->string('pricing_type', 30)->nullable()->after('applied_rate');
            }
            if (!Schema::hasColumn('milk_collections', 'rate_chart_id')) {
                $table->foreignId('rate_chart_id')->nullable()->after('pricing_type')->constrained('rate_charts')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('milk_collections', function (Blueprint $table) {
            if (Schema::hasColumn('milk_collections', 'rate_chart_id')) {
                $table->dropConstrainedForeignId('rate_chart_id');
            }
            if (Schema::hasColumn('milk_collections', 'pricing_type')) {
                $table->dropColumn('pricing_type');
            }
        });

        Schema::table('farmers', function (Blueprint $table) {
            if (Schema::hasColumn('farmers', 'pricing_type')) {
                $table->dropColumn('pricing_type');
            }
        });
    }
};
