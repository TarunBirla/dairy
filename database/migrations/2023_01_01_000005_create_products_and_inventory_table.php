<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('product_type')->default('milk'); // milk, curd, paneer, ghee, butter, beverage, feed, custom
            $table->string('unit')->default('liter'); // liter, kg, piece, bottle, pack
            $table->string('pack_size')->nullable(); // 500ml, 1L, 200g, 1kg
            $table->decimal('price', 8, 2);
            $table->decimal('subscription_price', 8, 2)->nullable();
            $table->decimal('cost_price', 8, 2)->default(0.00);
            $table->decimal('tax_percent', 5, 2)->default(0.00);
            $table->decimal('current_stock', 10, 2)->default(0.00);
            $table->decimal('min_stock_alert', 10, 2)->default(5.00);
            $table->boolean('in_stock')->default(true);
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('transaction_type'); // purchase_inward, production_inward, sale_outward, delivery_dispatch, wastage, transfer_in, transfer_out, adjustment
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 8, 2)->default(0.00);
            $table->decimal('balance_after', 10, 2)->default(0.00);
            $table->string('reference_type')->nullable(); // pos_order, delivery_dispatch, wastage, transfer
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('from_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('to_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->string('status')->default('completed'); // pending, completed, cancelled
            $table->unsignedBigInteger('dispatched_by')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bottle_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->integer('issued_count')->default(0);
            $table->integer('returned_count')->default(0);
            $table->integer('broken_count')->default(0);
            $table->decimal('deposit_rate_per_bottle', 8, 2)->default(50.00);
            $table->decimal('total_deposit_amount', 10, 2)->default(0.00);
            $table->integer('balance_bottles')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bottle_trackings');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
