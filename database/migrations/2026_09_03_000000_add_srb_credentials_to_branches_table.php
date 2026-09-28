<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            $table->unsignedBigInteger('srb_pos_id')->nullable()->after('password');
            $table->string('srb_pos_user')->nullable()->after('srb_pos_id');
            $table->string('srb_pos_password')->nullable()->after('srb_pos_user');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            $table->dropColumn(['srb_pos_id', 'srb_pos_user', 'srb_pos_password']);
        });
    }
};