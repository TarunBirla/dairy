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
        Schema::table('milk_dispatches', function (Blueprint $table) {
            // Header Parameters
            if (!Schema::hasColumn('milk_dispatches', 'from_date')) {
                $table->date('from_date')->nullable()->after('dispatch_number');
            }
            if (!Schema::hasColumn('milk_dispatches', 'from_shift')) {
                $table->string('from_shift', 20)->default('morning')->after('from_date');
            }
            if (!Schema::hasColumn('milk_dispatches', 'to_date')) {
                $table->date('to_date')->nullable()->after('from_shift');
            }
            if (!Schema::hasColumn('milk_dispatches', 'to_shift')) {
                $table->string('to_shift', 20)->default('morning')->after('to_date');
            }
            if (!Schema::hasColumn('milk_dispatches', 'challan_date')) {
                $table->date('challan_date')->nullable()->after('to_shift');
            }
            if (!Schema::hasColumn('milk_dispatches', 'challan_number')) {
                $table->string('challan_number', 50)->nullable()->after('challan_date');
            }
            if (!Schema::hasColumn('milk_dispatches', 'dispatch_type')) {
                $table->string('dispatch_type', 30)->default('Can')->comment('Can or Tanker')->after('challan_number');
            }
            if (!Schema::hasColumn('milk_dispatches', 'drop_location')) {
                $table->string('drop_location', 191)->nullable()->after('dispatch_type');
            }

            // Quality & Item Parameters
            if (!Schema::hasColumn('milk_dispatches', 'milk_type')) {
                $table->string('milk_type', 30)->default('Cow')->after('drop_location');
            }
            if (!Schema::hasColumn('milk_dispatches', 'purchase_qty')) {
                $table->decimal('purchase_qty', 10, 2)->default(0)->after('milk_type');
            }
            if (!Schema::hasColumn('milk_dispatches', 'milk_quality')) {
                $table->string('milk_quality', 30)->default('Good')->after('purchase_qty');
            }
            if (!Schema::hasColumn('milk_dispatches', 'quantity_ltr')) {
                $table->decimal('quantity_ltr', 10, 2)->default(0)->after('milk_quality');
            }
            if (!Schema::hasColumn('milk_dispatches', 'prev_balance')) {
                $table->decimal('prev_balance', 10, 2)->default(0)->after('quantity_ltr');
            }
            if (!Schema::hasColumn('milk_dispatches', 'balance')) {
                $table->decimal('balance', 10, 2)->default(0)->after('prev_balance');
            }
            if (!Schema::hasColumn('milk_dispatches', 'loss')) {
                $table->decimal('loss', 10, 2)->default(0)->after('balance');
            }
            if (!Schema::hasColumn('milk_dispatches', 'clr')) {
                $table->decimal('clr', 6, 2)->nullable()->after('snf');
            }
            if (!Schema::hasColumn('milk_dispatches', 'can_number')) {
                $table->string('can_number', 50)->nullable()->after('clr');
            }
            if (!Schema::hasColumn('milk_dispatches', 'acidity')) {
                $table->decimal('acidity', 5, 2)->nullable()->after('can_number');
            }
            if (!Schema::hasColumn('milk_dispatches', 'amount')) {
                $table->decimal('amount', 12, 2)->default(0)->after('acidity');
            }

            // Route & Vehicle Parameters
            if (!Schema::hasColumn('milk_dispatches', 'route_name')) {
                $table->string('route_name', 191)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('milk_dispatches', 'vehicle_in_time')) {
                $table->string('vehicle_in_time', 20)->nullable()->after('route_name');
            }
            if (!Schema::hasColumn('milk_dispatches', 'vehicle_out_time')) {
                $table->string('vehicle_out_time', 20)->nullable()->after('vehicle_in_time');
            }
            if (!Schema::hasColumn('milk_dispatches', 'seal_number')) {
                $table->string('seal_number', 50)->nullable()->after('vehicle_out_time');
            }
            if (!Schema::hasColumn('milk_dispatches', 'chamber_number')) {
                $table->string('chamber_number', 50)->nullable()->after('seal_number');
            }
            if (!Schema::hasColumn('milk_dispatches', 'headload_kms')) {
                $table->decimal('headload_kms', 8, 2)->default(0)->after('chamber_number');
            }
            if (!Schema::hasColumn('milk_dispatches', 'difference')) {
                $table->decimal('difference', 10, 2)->default(0)->after('headload_kms');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('milk_dispatches', function (Blueprint $table) {
            $table->dropColumn([
                'from_date', 'from_shift', 'to_date', 'to_shift', 'challan_date', 'challan_number',
                'dispatch_type', 'drop_location', 'milk_type', 'purchase_qty', 'milk_quality',
                'quantity_ltr', 'prev_balance', 'balance', 'loss', 'clr', 'can_number', 'acidity',
                'amount', 'route_name', 'vehicle_in_time', 'vehicle_out_time', 'seal_number',
                'chamber_number', 'headload_kms', 'difference'
            ]);
        });
    }
};
