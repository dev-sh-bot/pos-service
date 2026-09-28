<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_cart', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->renameColumn('product_id', 'item_id');
            $table->unsignedTinyInteger('item_type')->default(0)->after('item_id');
            $table->index(['user_id', 'item_id', 'item_type']);
        });
    }

    public function down(): void
    {
        Schema::table('user_cart', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'item_id', 'item_type']);
            $table->dropColumn('item_type');
            $table->renameColumn('item_id', 'product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
};
