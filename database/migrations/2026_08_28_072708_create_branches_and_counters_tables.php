<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Branches ──────────────────────────────────────────
        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->comment('Short branch code, e.g. MB01');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('password'); // bcrypt-hashed
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Counters ──────────────────────────────────────────
        Schema::create('counters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->comment('Short counter code, e.g. C1');
            $table->string('password'); // bcrypt-hashed
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['branch_id', 'code']);
        });

        // ── User ↔ Branch pivot ────────────────────────────────
        Schema::create('branch_user', function (Blueprint $table): void {
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['branch_id', 'user_id']);
        });

        // ── User ↔ Counter pivot ───────────────────────────────
        Schema::create('counter_user', function (Blueprint $table): void {
            $table->foreignId('counter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['counter_id', 'user_id']);
        });

        // ── Add branch_id + counter_id to orders ──────────────
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('branch_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('counter_id')
                ->nullable()
                ->after('branch_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['counter_id']);
            $table->dropColumn(['branch_id', 'counter_id']);
        });

        Schema::dropIfExists('counter_user');
        Schema::dropIfExists('branch_user');
        Schema::dropIfExists('counters');
        Schema::dropIfExists('branches');
    }
};
