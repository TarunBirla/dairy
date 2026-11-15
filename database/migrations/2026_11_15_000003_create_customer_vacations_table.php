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
        if (!Schema::hasTable('customer_vacations')) {
            Schema::create('customer_vacations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->date('from_date');
                $table->date('to_date');
                $table->string('shift', 20)->default('full_day'); // morning, evening, full_day
                $table->string('reason')->nullable();
                $table->string('added_by', 50)->default('admin'); // customer, admin
                $table->string('status', 20)->default('active'); // active, cancelled
                $table->timestamps();

                $table->index(['customer_id', 'status']);
                $table->index(['from_date', 'to_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_vacations');
    }
};
