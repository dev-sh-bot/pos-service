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
        if (Schema::hasTable('sale_returns')) {
            $existingCols = Schema::getColumnListing('sale_returns');
            Schema::table('sale_returns', function (Blueprint $table) use ($existingCols) {
                if (!in_array('srb_invoice_id', $existingCols, true)) {
                    $table->string('srb_invoice_id')->nullable()->unique()->after('refund_total');
                }
                if (!in_array('srb_qr_code_link', $existingCols, true)) {
                    $table->text('srb_qr_code_link')->nullable()->after('srb_invoice_id');
                }
                if (!in_array('srb_status', $existingCols, true)) {
                    $table->string('srb_status', 20)->nullable()->after('srb_qr_code_link');
                }
                if (!in_array('srb_response', $existingCols, true)) {
                    $table->text('srb_response')->nullable()->after('srb_status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sale_returns')) {
            $existingCols = Schema::getColumnListing('sale_returns');
            Schema::table('sale_returns', function (Blueprint $table) use ($existingCols) {
                if (in_array('srb_invoice_id', $existingCols, true)) {
                    $table->dropUnique(['srb_invoice_id']);
                }
                $drop = array_intersect(['srb_invoice_id', 'srb_qr_code_link', 'srb_status', 'srb_response'], $existingCols);
                if (!empty($drop)) {
                    $table->dropColumn(array_values($drop));
                }
            });
        }
    }
};
