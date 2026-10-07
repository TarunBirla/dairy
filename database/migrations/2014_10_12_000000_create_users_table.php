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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('role')->default('customer'); // super_admin, dairy_admin, branch_manager, collection_manager, collection_operator, accountant, delivery_manager, delivery_boy, sales_operator, inventory_manager, farmer, customer, support_staff
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('status')->default('active'); // active, inactive, blocked
            $table->string('avatar')->nullable();
            $table->json('permissions')->nullable();
            $table->string('two_factor_otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
