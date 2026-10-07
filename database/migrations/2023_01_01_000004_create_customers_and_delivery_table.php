<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('area_name')->nullable();
            $table->unsignedBigInteger('delivery_boy_id')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('route_id')->nullable()->constrained('delivery_routes')->nullOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('locality')->nullable();
            $table->string('category')->default('household'); // household, retail, hotel, shop, institution
            $table->decimal('credit_limit', 10, 2)->default(1000.00);
            $table->decimal('current_balance', 12, 2)->default(0.00); // positive means customer owes dairy
            $table->integer('delivery_sequence')->default(1);
            $table->text('delivery_instructions')->nullable();
            $table->string('status')->default('active'); // active, paused, inactive, blocked
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('address_type')->default('home'); // home, office, other
            $table->text('address_line');
            $table->string('landmark')->nullable();
            $table->string('pincode')->nullable();
            $table->boolean('is_default')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('subscription_code')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id');
            $table->foreignId('route_id')->nullable()->constrained('delivery_routes')->nullOnDelete();
            $table->decimal('quantity', 6, 2)->default(1.00);
            $table->string('frequency')->default('daily'); // daily, alternate_day, custom_days
            $table->json('custom_days')->nullable(); // [1,2,3,4,5,6,7]
            $table->string('shift')->default('morning'); // morning, evening, both
            $table->decimal('unit_price', 8, 2);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active'); // active, paused, cancelled
            $table->date('pause_from')->nullable();
            $table->date('pause_until')->nullable();
            $table->timestamps();
        });

        Schema::create('daily_deliveries', function (Blueprint $table) {
            $table->id();
            $table->date('delivery_date');
            $table->string('shift')->default('morning'); // morning, evening
            $table->foreignId('route_id')->nullable()->constrained('delivery_routes')->nullOnDelete();
            $table->unsignedBigInteger('delivery_boy_id')->nullable();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 6, 2);
            $table->decimal('extra_quantity', 6, 2)->default(0.00);
            $table->decimal('delivered_quantity', 6, 2)->default(0.00);
            $table->decimal('unit_price', 8, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending'); // pending, delivered, skipped, failed
            $table->string('failure_reason')->nullable();
            $table->decimal('cash_collected', 10, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->string('type'); // debit (invoice, delivery charges), credit (payments, discounts)
            $table->decimal('amount', 12, 2);
            $table->decimal('balance', 12, 2);
            $table->string('reference_type')->nullable(); // invoice, delivery, payment, refund
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_ledgers');
        Schema::dropIfExists('daily_deliveries');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('delivery_routes');
    }
};
