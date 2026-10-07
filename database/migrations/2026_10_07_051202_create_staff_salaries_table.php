<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('salary_month', 7); // YYYY-MM
            $table->decimal('base_salary', 10, 2);
            $table->integer('total_days')->default(30);
            $table->integer('present_days')->default(30);
            $table->decimal('allowances', 10, 2)->default(0);
            $table->decimal('advance_deduction', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);
            $table->decimal('net_payable', 10, 2);
            $table->string('payment_status')->default('paid'); // paid, pending, partial
            $table->date('disbursed_at')->nullable();
            $table->string('payment_mode')->default('bank_transfer');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'salary_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_salaries');
    }
};
