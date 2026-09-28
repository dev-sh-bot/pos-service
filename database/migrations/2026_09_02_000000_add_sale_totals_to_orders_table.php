<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('subtotal', 12, 2)->default(0)->after('counter_id');
            $table->decimal('tax_percent', 8, 2)->default(0)->after('subtotal');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percent');
            $table->decimal('discount_percent', 8, 2)->default(0)->after('tax_amount');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_percent');
            $table->decimal('total_amount', 12, 2)->default(0)->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['subtotal', 'tax_percent', 'tax_amount', 'discount_percent', 'discount_amount', 'total_amount']);
        });
    }
};