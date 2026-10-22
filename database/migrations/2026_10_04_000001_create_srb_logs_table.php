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
        if (! Schema::hasTable('srb_logs')) {
            Schema::create('srb_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('sale_return_id')->nullable()->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('invoice_no')->nullable()->index();
            $table->tinyInteger('invoice_type')->default(1)->comment('1: Invoice, 2: Sale Return');
            $table->string('request_type', 30)->default('invoice')->comment('invoice, sale_return');
            $table->string('endpoint');
            $table->unsignedSmallInteger('attempt')->default(1);
            $table->integer('status_code')->nullable();
            $table->string('res_code', 30)->nullable()->index();
            $table->boolean('is_success')->default(false)->index();
            $table->string('srb_invoice_id')->nullable()->index();
            $table->text('srb_qr_code_link')->nullable();
            $table->longText('payload')->nullable();
            $table->longText('response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('srb_logs');
    }
};
