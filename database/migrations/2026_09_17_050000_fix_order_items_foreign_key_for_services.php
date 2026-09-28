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
        Schema::table('order_items', function (Blueprint $table): void {
            // Drop the existing foreign key constraint
            $table->dropForeign(['product_id']);
            
            // Rename product_id to item_id to be consistent with cart table
            $table->renameColumn('product_id', 'item_id');
            
            // Make item_id nullable since services don't need to reference products table
            $table->unsignedBigInteger('item_id')->nullable()->change();
            
            // Add index for better performance when querying by item_id and item_type
            $table->index(['item_id', 'item_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            // Drop the index
            $table->dropIndex(['item_id', 'item_type']);
            
            // Rename back to product_id
            $table->renameColumn('item_id', 'product_id');
            
            // Add back the foreign key constraint (but this will fail if there are services in the table)
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
};