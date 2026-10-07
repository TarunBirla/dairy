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
        Schema::table('farmers', function (Blueprint $table) {
            if (!Schema::hasColumn('farmers', 'name_hi')) {
                $table->string('name_hi')->nullable()->after('name');
            }
            if (!Schema::hasColumn('farmers', 'photo')) {
                $table->string('photo')->nullable()->after('name_hi');
            }
            if (!Schema::hasColumn('farmers', 'vehicle')) {
                $table->string('vehicle')->nullable()->after('address');
            }
            if (!Schema::hasColumn('farmers', 'route_id')) {
                $table->unsignedBigInteger('route_id')->nullable()->after('vehicle');
            }
            if (!Schema::hasColumn('farmers', 'cow_milk_rate')) {
                $table->decimal('cow_milk_rate', 8, 2)->nullable()->after('custom_rate_override');
            }
            if (!Schema::hasColumn('farmers', 'buffalo_milk_rate')) {
                $table->decimal('buffalo_milk_rate', 8, 2)->nullable()->after('cow_milk_rate');
            }
            if (!Schema::hasColumn('farmers', 'branch_name')) {
                $table->string('branch_name')->nullable()->after('animal_type');
            }
            if (!Schema::hasColumn('farmers', 'anamat')) {
                $table->decimal('anamat', 10, 2)->default(0.00)->after('current_balance');
            }
            if (!Schema::hasColumn('farmers', 'building_fund')) {
                $table->decimal('building_fund', 10, 2)->default(0.00)->after('anamat');
            }
            if (!Schema::hasColumn('farmers', 'installment')) {
                $table->decimal('installment', 10, 2)->default(0.00)->after('building_fund');
            }
            if (!Schema::hasColumn('farmers', 'etc_amount')) {
                $table->decimal('etc_amount', 10, 2)->default(0.00)->after('installment');
            }
            if (!Schema::hasColumn('farmers', 'grant_amount')) {
                $table->decimal('grant_amount', 10, 2)->default(0.00)->after('etc_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $cols = [
                'name_hi', 'photo', 'vehicle', 'route_id',
                'cow_milk_rate', 'buffalo_milk_rate', 'branch_name',
                'anamat', 'building_fund', 'installment', 'etc_amount', 'grant_amount'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('farmers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
