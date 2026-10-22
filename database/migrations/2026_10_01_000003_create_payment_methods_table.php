<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->string('type', 30)->default('bank'); // cash, bank, card, wallet, other
                $table->string('bank_name')->nullable();
                $table->string('account_title')->nullable();
                $table->string('account_number')->nullable();
                $table->string('iban')->nullable();
                $table->string('branch_name')->nullable();
                $table->string('branch_code')->nullable();
                $table->string('swift_code')->nullable();
                $table->boolean('status')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();
            });

            // Seed default payment methods
            DB::table('payment_methods')->insert([
                [
                    'name'           => 'Cash Drawer',
                    'code'           => 'CASH',
                    'type'           => 'cash',
                    'bank_name'      => null,
                    'account_title'  => 'Store Cash Counter',
                    'account_number' => null,
                    'iban'           => null,
                    'branch_name'    => 'Main Store',
                    'branch_code'    => null,
                    'swift_code'     => null,
                    'status'         => true,
                    'notes'          => 'Default cash drawer for in-store transactions.',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ],
                [
                    'name'           => 'Meezan Bank - Main Business',
                    'code'           => 'MEEZAN-01',
                    'type'           => 'bank',
                    'bank_name'      => 'Meezan Bank Ltd',
                    'account_title'  => 'Zargham POS Operations',
                    'account_number' => '010203040506',
                    'iban'           => 'PK36MEZN0001020304050601',
                    'branch_name'    => 'Commercial Branch',
                    'branch_code'    => '0102',
                    'swift_code'     => 'MEZNPKKA',
                    'status'         => true,
                    'notes'          => 'Primary corporate checking account for vendor & bank settlements.',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ],
                [
                    'name'           => 'Bank Alfalah POS Terminal',
                    'code'           => 'CARD-ALFALAH',
                    'type'           => 'card',
                    'bank_name'      => 'Bank Alfalah Ltd',
                    'account_title'  => 'Zargham POS Merchant',
                    'account_number' => '100234567890',
                    'iban'           => 'PK12ALFH0001002345678901',
                    'branch_name'    => 'Merchant Terminal #1',
                    'branch_code'    => '0234',
                    'swift_code'     => 'ALFHPKKA',
                    'status'         => true,
                    'notes'          => 'POS Card Swipe / Tap Terminal for Visa & MasterCard.',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ],
                [
                    'name'           => 'JazzCash Merchant Account',
                    'code'           => 'JAZZCASH',
                    'type'           => 'wallet',
                    'bank_name'      => 'Mobilink Microfinance Bank',
                    'account_title'  => 'Zargham POS',
                    'account_number' => '03001234567',
                    'iban'           => null,
                    'branch_name'    => 'Digital Wallet',
                    'branch_code'    => null,
                    'swift_code'     => null,
                    'status'         => true,
                    'notes'          => 'Direct mobile wallet QR & till transfers.',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ],
            ]);
        }

        // Add bank & payment permissions if table exists
        if (Schema::hasTable('permissions')) {
            $paymentPerms = [
                'payment_methods.view',
                'payment_methods.edit',
                'payment_methods.delete',
            ];

            foreach ($paymentPerms as $p) {
                $existing = DB::table('permissions')->where('name', $p)->first();
                if (!$existing) {
                    DB::table('permissions')->insert([
                        'name'       => $p,
                        'group_name' => 'payment_methods',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('permissions')->where('name', $p)->update([
                        'group_name' => 'payment_methods',
                        'updated_at' => now(),
                    ]);
                }
            }

            // Assign to Admin role in permission_role
            $adminRoles = DB::table('roles')->whereIn('slug', ['admin', 'Admin'])->orWhereIn('name', ['admin', 'Admin'])->get();
            $permIds = DB::table('permissions')->whereIn('name', $paymentPerms)->pluck('id');

            foreach ($adminRoles as $admRole) {
                if (Schema::hasTable('permission_role')) {
                    foreach ($permIds as $pId) {
                        if (!DB::table('permission_role')->where(['permission_id' => $pId, 'role_id' => $admRole->id])->exists()) {
                            DB::table('permission_role')->insert([
                                'permission_id' => $pId,
                                'role_id'       => $admRole->id,
                            ]);
                        }
                    }
                }
                if (Schema::hasTable('role_has_permissions')) {
                    foreach ($permIds as $pId) {
                        if (!DB::table('role_has_permissions')->where(['permission_id' => $pId, 'role_id' => $admRole->id])->exists()) {
                            DB::table('role_has_permissions')->insert([
                                'permission_id' => $pId,
                                'role_id'       => $admRole->id,
                            ]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
