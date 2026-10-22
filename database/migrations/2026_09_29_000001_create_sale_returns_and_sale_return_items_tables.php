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
        if (!Schema::hasTable('sale_returns')) {
            Schema::create('sale_returns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('counter_id')->nullable()->constrained('counters')->nullOnDelete();
                $table->string('return_no')->unique();
                $table->decimal('refund_subtotal', 10, 2)->default(0);
                $table->decimal('refund_tax', 10, 2)->default(0);
                $table->decimal('refund_discount', 10, 2)->default(0);
                $table->decimal('refund_total', 10, 2)->default(0);
                $table->string('refund_method')->default('cash');
                $table->string('return_reason')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sale_return_items')) {
            Schema::create('sale_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_return_id')->constrained('sale_returns')->onDelete('cascade');
                $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
                $table->unsignedBigInteger('item_id')->nullable();
                $table->unsignedTinyInteger('item_type')->default(0); // 0=product, 1=service, 2=deal
                $table->string('item_name');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('refund_amount', 10, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
    }
};
