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
        if (Schema::hasTable('orders')) {
            if (!Schema::hasColumn('orders', 'payment_method')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->string('payment_method', 20)->default('cash')->after('total_amount');
                });
            }
        }

        if (Schema::hasTable('payments')) {
            if (!Schema::hasColumn('payments', 'payment_method')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->string('payment_method', 20)->default('cash')->after('amount');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            if (Schema::hasColumn('orders', 'payment_method')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->dropColumn('payment_method');
                });
            }
        }

        if (Schema::hasTable('payments')) {
            if (Schema::hasColumn('payments', 'payment_method')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->dropColumn('payment_method');
                });
            }
        }
    }
};
