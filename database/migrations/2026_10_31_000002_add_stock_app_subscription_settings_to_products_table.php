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
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                // Stock Management
                if (!Schema::hasColumn('products', 'manage_stock')) {
                    $table->boolean('manage_stock')->default(true)->after('current_stock');
                }

                // Counter Sales
                if (!Schema::hasColumn('products', 'allow_counter_sales')) {
                    $table->boolean('allow_counter_sales')->default(true)->after('in_stock');
                }

                // Customer App Settings
                if (!Schema::hasColumn('products', 'show_on_customer_app')) {
                    $table->boolean('show_on_customer_app')->default(true)->after('allow_counter_sales');
                }
                if (!Schema::hasColumn('products', 'allow_onetime_orders')) {
                    $table->boolean('allow_onetime_orders')->default(true)->after('show_on_customer_app');
                }
                if (!Schema::hasColumn('products', 'schedule_booking_cutoff')) {
                    $table->boolean('schedule_booking_cutoff')->default(false)->after('allow_onetime_orders');
                }
                if (!Schema::hasColumn('products', 'booking_cutoff_morning_day')) {
                    $table->string('booking_cutoff_morning_day', 50)->nullable()->after('schedule_booking_cutoff');
                }
                if (!Schema::hasColumn('products', 'booking_cutoff_morning_time')) {
                    $table->string('booking_cutoff_morning_time', 20)->nullable()->after('booking_cutoff_morning_day');
                }
                if (!Schema::hasColumn('products', 'booking_cutoff_evening_day')) {
                    $table->string('booking_cutoff_evening_day', 50)->nullable()->after('booking_cutoff_morning_time');
                }
                if (!Schema::hasColumn('products', 'booking_cutoff_evening_time')) {
                    $table->string('booking_cutoff_evening_time', 20)->nullable()->after('booking_cutoff_evening_day');
                }

                // Subscription Settings
                if (!Schema::hasColumn('products', 'allow_subscription')) {
                    $table->boolean('allow_subscription')->default(true)->after('booking_cutoff_evening_time');
                }
                if (!Schema::hasColumn('products', 'allow_subscription_change')) {
                    $table->boolean('allow_subscription_change')->default(true)->after('allow_subscription');
                }
                if (!Schema::hasColumn('products', 'allow_subscription_cancellation')) {
                    $table->boolean('allow_subscription_cancellation')->default(true)->after('allow_subscription_change');
                }
                if (!Schema::hasColumn('products', 'allow_booking_after_subscription')) {
                    $table->boolean('allow_booking_after_subscription')->default(true)->after('allow_subscription_cancellation');
                }
                if (!Schema::hasColumn('products', 'subscription_cutoff_morning_day')) {
                    $table->string('subscription_cutoff_morning_day', 50)->nullable()->after('allow_booking_after_subscription');
                }
                if (!Schema::hasColumn('products', 'subscription_cutoff_morning_time')) {
                    $table->string('subscription_cutoff_morning_time', 20)->nullable()->after('subscription_cutoff_morning_day');
                }
                if (!Schema::hasColumn('products', 'subscription_cutoff_evening_day')) {
                    $table->string('subscription_cutoff_evening_day', 50)->nullable()->after('subscription_cutoff_morning_time');
                }
                if (!Schema::hasColumn('products', 'subscription_cutoff_evening_time')) {
                    $table->string('subscription_cutoff_evening_time', 20)->nullable()->after('subscription_cutoff_evening_day');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $columns = [
                    'manage_stock',
                    'allow_counter_sales',
                    'show_on_customer_app',
                    'allow_onetime_orders',
                    'schedule_booking_cutoff',
                    'booking_cutoff_morning_day',
                    'booking_cutoff_morning_time',
                    'booking_cutoff_evening_day',
                    'booking_cutoff_evening_time',
                    'allow_subscription',
                    'allow_subscription_change',
                    'allow_subscription_cancellation',
                    'allow_booking_after_subscription',
                    'subscription_cutoff_morning_day',
                    'subscription_cutoff_morning_time',
                    'subscription_cutoff_evening_day',
                    'subscription_cutoff_evening_time',
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('products', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
