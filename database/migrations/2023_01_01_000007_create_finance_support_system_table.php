<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->string('payment_mode')->default('cash'); // cash, upi, bank_transfer
            $table->string('vendor_name')->nullable();
            $table->text('description')->nullable();
            $table->string('receipt_file')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('status')->default('approved'); // pending, approved, rejected
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('farmer_id')->nullable()->constrained('farmers')->nullOnDelete();
            $table->string('category')->default('delivery'); // delivery, billing, milk_quality, product, general
            $table->string('subject');
            $table->text('description');
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('status')->default('open'); // open, in_progress, resolved, closed
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->text('message');
            $table->boolean('is_internal_note')->default(false);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title');
            $table->text('message');
            $table->string('channel')->default('web'); // web, sms, whatsapp, push
            $table->string('type')->default('info'); // info, success, warning, alert
            $table->string('recipient_phone')->nullable();
            $table->string('status')->default('sent'); // sent, failed, read
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // created, updated, deleted, status_changed, login
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('payload')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general'); // general, billing, dairy, rate, notification
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
