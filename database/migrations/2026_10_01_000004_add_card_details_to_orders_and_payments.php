<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add card details + receiving account to orders
        if (Schema::hasTable('orders')) {
            $cols = Schema::getColumnListing('orders');
            Schema::table('orders', function (Blueprint $table) use ($cols) {
                if (!in_array('card_holder_name', $cols, true)) {
                    $table->string('card_holder_name')->nullable()->after('payment_method');
                }
                if (!in_array('card_last_four', $cols, true)) {
                    $table->string('card_last_four', 4)->nullable()->after('card_holder_name');
                }
                if (!in_array('receiving_account_id', $cols, true)) {
                    $table->unsignedBigInteger('receiving_account_id')->nullable()->after('card_last_four');
                }
            });
        }

        // Add same to payments table
        if (Schema::hasTable('payments')) {
            $pcols = Schema::getColumnListing('payments');
            Schema::table('payments', function (Blueprint $table) use ($pcols) {
                if (!in_array('card_holder_name', $pcols, true)) {
                    $table->string('card_holder_name')->nullable()->after('payment_method');
                }
                if (!in_array('card_last_four', $pcols, true)) {
                    $table->string('card_last_four', 4)->nullable()->after('card_holder_name');
                }
                if (!in_array('receiving_account_id', $pcols, true)) {
                    $table->unsignedBigInteger('receiving_account_id')->nullable()->after('card_last_four');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            $columns = array_intersect(['card_holder_name', 'card_last_four', 'receiving_account_id'], Schema::getColumnListing('orders'));
            if ($columns) {
                Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(array_values($columns)));
            }
        }
        if (Schema::hasTable('payments')) {
            $columns = array_intersect(['card_holder_name', 'card_last_four', 'receiving_account_id'], Schema::getColumnListing('payments'));
            if ($columns) {
                Schema::table('payments', fn (Blueprint $table) => $table->dropColumn(array_values($columns)));
            }
        }
    }
};
