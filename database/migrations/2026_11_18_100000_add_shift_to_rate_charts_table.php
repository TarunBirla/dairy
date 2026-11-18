<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rate_charts')) {
            Schema::table('rate_charts', function (Blueprint $table) {
                if (!Schema::hasColumn('rate_charts', 'shift')) {
                    $table->string('shift')->default('both')->nullable()->after('milk_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rate_charts')) {
            Schema::table('rate_charts', function (Blueprint $table) {
                if (Schema::hasColumn('rate_charts', 'shift')) {
                    $table->dropColumn('shift');
                }
            });
        }
    }
};
