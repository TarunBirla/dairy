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
        if (!Schema::hasTable('farmer_emergency_funds')) {
            Schema::create('farmer_emergency_funds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
                $table->string('request_no')->nullable()->unique();
                $table->date('request_date');
                $table->decimal('amount', 10, 2);
                $table->text('reason')->nullable();
                $table->string('status')->default('approved'); // pending, approved, disbursed, rejected
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('disbursed_at')->nullable();
                $table->decimal('recovered_amount', 10, 2)->default(0.00);
                $table->string('payment_mode')->default('Cash');
                $table->text('notes')->nullable();
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
        Schema::dropIfExists('farmer_emergency_funds');
    }
};
