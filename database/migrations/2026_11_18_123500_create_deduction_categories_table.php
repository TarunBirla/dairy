<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('deduction_categories')) {
            Schema::create('deduction_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('name_hi')->nullable();
                $table->string('code')->unique();
                $table->text('description')->nullable();
                $table->string('status', 20)->default('active'); // active, inactive
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });

            // Seed initial default categories
            $defaults = [
                ['name' => 'Cattle Feed', 'name_hi' => 'पशु आहार', 'code' => 'cattle_feed', 'description' => 'Khalli, chokar, cattle feed rations', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Medicine', 'name_hi' => 'दवाई', 'code' => 'medicine', 'description' => 'Veterinary medicines and supplements', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Ghee / Butter', 'name_hi' => 'घी / मक्खन', 'code' => 'ghee_butter', 'description' => 'Dairy products taken on credit', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Doctor Fee', 'name_hi' => 'डॉक्टर शुल्क', 'code' => 'doctor_fee', 'description' => 'Veterinary visit and treatment fees', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Insurance', 'name_hi' => 'बीमा', 'code' => 'insurance', 'description' => 'Cattle and livestock insurance deduction', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Advance Recovery', 'name_hi' => 'अग्रिम वसूली', 'code' => 'advance_recovery', 'description' => 'Installment and advance recovery deduction', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Store Purchase', 'name_hi' => 'स्टोर खरीद', 'code' => 'store_purchase', 'description' => 'Purchases from dairy cooperative store', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Equipment', 'name_hi' => 'उपकरण / बर्तन', 'code' => 'equipment', 'description' => 'Milk cans, strainers, and dairy equipments', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ];

            DB::table('deduction_categories')->insert($defaults);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deduction_categories');
    }
};
