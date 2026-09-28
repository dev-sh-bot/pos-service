<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['invoice_no']);
        });

        DB::statement("UPDATE order_items SET item_type = CASE WHEN item_type = 'service' OR item_type = '1' THEN 1 ELSE 0 END");
        DB::statement("ALTER TABLE order_items MODIFY item_type TINYINT UNSIGNED NOT NULL DEFAULT 0");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE order_items MODIFY item_type VARCHAR(20) NOT NULL DEFAULT 'product'");
        DB::statement("UPDATE order_items SET item_type = CASE WHEN item_type = '1' THEN 'service' ELSE 'product' END");

        Schema::table('orders', function (Blueprint $table): void {
            $table->unique('invoice_no');
        });
    }
};
