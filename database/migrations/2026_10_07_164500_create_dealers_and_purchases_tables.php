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
        if (!Schema::hasTable('dealers')) {
            Schema::create('dealers', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('product_purchases')) {
            Schema::create('product_purchases', function (Blueprint $table) {
                $table->id();
                $table->string('purchase_number')->unique();
                $table->date('purchase_date');
                $table->string('shift')->default('morning'); // morning, evening
                $table->foreignId('dealer_id')->nullable()->constrained('dealers')->nullOnDelete();
                $table->string('dealer_code')->nullable();
                $table->string('dealer_name');
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('product_name');
                $table->string('unit')->default('kg');
                $table->decimal('quantity', 10, 2);
                $table->decimal('rate', 10, 2); // Purchase Rate
                $table->decimal('sale_rate', 10, 2)->nullable(); // Sale Rate
                $table->decimal('total_amount', 12, 2); // quantity * rate
                $table->decimal('paid_amount', 12, 2)->default(0.00);
                $table->decimal('remaining_amount', 12, 2)->default(0.00);
                $table->decimal('advance_amount', 12, 2)->default(0.00);
                $table->text('note')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_purchases');
        Schema::dropIfExists('dealers');
    }
};
