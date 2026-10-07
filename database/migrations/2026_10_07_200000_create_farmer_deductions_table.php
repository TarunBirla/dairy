<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('farmer_deductions')) {
            Schema::create('farmer_deductions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('farmer_id')->constrained('farmers')->cascadeOnDelete();
                $table->date('entry_date');
                $table->string('deduction_type')->default('cattle_feed'); // cattle_feed, medicine, ghee_butter, doctor_fee, insurance, advance_recovery, store_purchase, equipment, other
                $table->string('transaction_type')->default('given'); // given (debit), received (credit)
                $table->decimal('amount', 10, 2);
                $table->text('comments')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_deductions');
    }
};
