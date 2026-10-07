<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rate_charts', function (Blueprint $table) {
            if (!Schema::hasColumn('rate_charts', 'category')) {
                $table->string('category')->default('collection')->after('name'); // collection, milk_sale, chilling_center
            }
            if (!Schema::hasColumn('rate_charts', 'format')) {
                $table->string('format')->default('fat_snf')->after('category'); // fat_snf, fat_only, fixed_rate
            }
            if (!Schema::hasColumn('rate_charts', 'type')) {
                $table->string('type')->default('increase_per_point')->after('format'); // increase_per_point, matrix_slab, flat
            }
            if (!Schema::hasColumn('rate_charts', 'starting_amount')) {
                $table->decimal('starting_amount', 8, 2)->default(0.00)->after('type');
            }
            if (!Schema::hasColumn('rate_charts', 'fixed_rate')) {
                $table->decimal('fixed_rate', 8, 2)->default(0.00)->after('starting_amount');
            }
            if (!Schema::hasColumn('rate_charts', 'fat_steps')) {
                $table->json('fat_steps')->nullable()->after('fixed_rate');
            }
            if (!Schema::hasColumn('rate_charts', 'snf_steps')) {
                $table->json('snf_steps')->nullable()->after('fat_steps');
            }
            if (!Schema::hasColumn('rate_charts', 'fat_rules')) {
                $table->json('fat_rules')->nullable()->after('snf_steps');
            }
            if (!Schema::hasColumn('rate_charts', 'snf_rules')) {
                $table->json('snf_rules')->nullable()->after('fat_rules');
            }
        });

        Schema::table('collection_centers', function (Blueprint $table) {
            if (!Schema::hasColumn('collection_centers', 'rate_chart_id')) {
                $table->foreignId('rate_chart_id')->nullable()->after('branch_id')->constrained('rate_charts')->nullOnDelete();
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'rate_chart_id')) {
                $table->foreignId('rate_chart_id')->nullable()->after('branch_id')->constrained('rate_charts')->nullOnDelete();
            }
        });

        if (!Schema::hasTable('rate_corrections')) {
            Schema::create('rate_corrections', function (Blueprint $table) {
                $table->id();
                $table->string('filter_type')->default('collection'); // collection, milk_sale, chilling_center
                $table->string('apply_to')->default('farmer'); // farmer, customer
                $table->date('from_date');
                $table->date('to_date');
                $table->string('shift')->default('morning_evening'); // morning, evening, morning_evening
                $table->string('milk_type')->default('cow'); // cow, buffalo, mixed, all
                $table->foreignId('rate_chart_id')->constrained('rate_charts')->cascadeOnDelete();
                $table->json('selected_ids')->nullable(); // null = all people
                $table->integer('records_updated')->default(0);
                $table->decimal('total_difference', 12, 2)->default(0.00);
                $table->foreignId('applied_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_corrections');

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'rate_chart_id')) {
                $table->dropForeign(['rate_chart_id']);
                $table->dropColumn('rate_chart_id');
            }
        });

        Schema::table('collection_centers', function (Blueprint $table) {
            if (Schema::hasColumn('collection_centers', 'rate_chart_id')) {
                $table->dropForeign(['rate_chart_id']);
                $table->dropColumn('rate_chart_id');
            }
        });

        Schema::table('rate_charts', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'format',
                'type',
                'starting_amount',
                'fixed_rate',
                'fat_steps',
                'snf_steps',
                'fat_rules',
                'snf_rules',
            ]);
        });
    }
};
