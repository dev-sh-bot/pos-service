<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('srb_invoice_id')->nullable()->unique()->after('total_amount');
            $table->text('srb_qr_code_link')->nullable()->after('srb_invoice_id');
            $table->string('srb_status', 20)->nullable()->after('srb_qr_code_link');
            $table->text('srb_response')->nullable()->after('srb_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['srb_invoice_id']);
            $table->dropColumn(['srb_invoice_id', 'srb_qr_code_link', 'srb_status', 'srb_response']);
        });
    }
};
