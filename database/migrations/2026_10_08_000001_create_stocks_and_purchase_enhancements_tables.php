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
        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->upForSqlite();

            return;
        }

        // 1. Add category_id to products using raw SQL to prevent generation_expression error on MariaDB
        $productCols = collect(DB::select("SHOW COLUMNS FROM products"))->pluck('Field')->all();
        if (! in_array('category_id', $productCols, true)) {
            DB::statement("ALTER TABLE `products` ADD COLUMN `category_id` BIGINT UNSIGNED NULL AFTER `id`, ADD INDEX `idx_products_category_id` (`category_id`)");
        }

        // 2. Add columns to purchases table
        $purchaseCols = collect(DB::select("SHOW COLUMNS FROM purchases"))->pluck('Field')->all();
        if (! in_array('purchase_no', $purchaseCols, true)) {
            DB::statement("ALTER TABLE `purchases` ADD COLUMN `purchase_no` VARCHAR(50) NULL AFTER `id`, ADD INDEX `idx_purchases_purchase_no` (`purchase_no`)");
        }
        if (! in_array('branch_id', $purchaseCols, true)) {
            DB::statement("ALTER TABLE `purchases` ADD COLUMN `branch_id` BIGINT UNSIGNED NULL AFTER `user_id`, ADD INDEX `idx_purchases_branch_id` (`branch_id`)");
        }
        if (! in_array('subtotal', $purchaseCols, true)) {
            DB::statement("ALTER TABLE `purchases` ADD COLUMN `subtotal` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `purchase_date`");
        }
        if (! in_array('tax_percent', $purchaseCols, true)) {
            DB::statement("ALTER TABLE `purchases` ADD COLUMN `tax_percent` DECIMAL(8, 2) NOT NULL DEFAULT 0.00 AFTER `subtotal`");
        }
        if (! in_array('tax_amount', $purchaseCols, true)) {
            DB::statement("ALTER TABLE `purchases` ADD COLUMN `tax_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `tax_percent`");
        }
        if (! in_array('discount_percent', $purchaseCols, true)) {
            DB::statement("ALTER TABLE `purchases` ADD COLUMN `discount_percent` DECIMAL(8, 2) NOT NULL DEFAULT 0.00 AFTER `tax_amount`");
        }
        if (! in_array('discount_amount', $purchaseCols, true)) {
            DB::statement("ALTER TABLE `purchases` ADD COLUMN `discount_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `discount_percent`");
        }
        if (! in_array('payment_method', $purchaseCols, true)) {
            DB::statement("ALTER TABLE `purchases` ADD COLUMN `payment_method` VARCHAR(30) NOT NULL DEFAULT 'cash' AFTER `total_amount`");
        }

        // 3. Create stocks table
        if (! Schema::hasTable('stocks')) {
            DB::statement("
                CREATE TABLE IF NOT EXISTS `stocks` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `item_id` BIGINT UNSIGNED NOT NULL,
                    `voucher_no` VARCHAR(100) NOT NULL,
                    `voucher_type` TINYINT NOT NULL COMMENT '0: Opening, 1: Purchase, 2: Purchase Return, 3: Sale, 4: Sale Return, 5: Stock Transfer',
                    `voucher_date` DATE NOT NULL,
                    `customer_id` BIGINT UNSIGNED NULL,
                    `supplier_id` BIGINT UNSIGNED NULL,
                    `branch_id` BIGINT UNSIGNED NULL,
                    `from_branch_id` BIGINT UNSIGNED NULL,
                    `to_branch_id` BIGINT UNSIGNED NULL,
                    `qty` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
                    `rate` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
                    `amount` DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
                    `status` TINYINT NOT NULL DEFAULT 1 COMMENT '1: Active, 0: Void',
                    `notes` TEXT NULL,
                    `created_at` TIMESTAMP NULL DEFAULT NULL,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL,
                    INDEX `idx_stocks_item_id` (`item_id`),
                    INDEX `idx_stocks_voucher_no` (`voucher_no`),
                    INDEX `idx_stocks_voucher_type` (`voucher_type`),
                    INDEX `idx_stocks_voucher_date` (`voucher_date`),
                    INDEX `idx_stocks_customer_id` (`customer_id`),
                    INDEX `idx_stocks_supplier_id` (`supplier_id`),
                    INDEX `idx_stocks_branch_id` (`branch_id`),
                    INDEX `idx_stocks_from_branch_id` (`from_branch_id`),
                    INDEX `idx_stocks_to_branch_id` (`to_branch_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 4. Create purchase_returns table
        if (! Schema::hasTable('purchase_returns')) {
            DB::statement("
                CREATE TABLE IF NOT EXISTS `purchase_returns` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `purchase_id` BIGINT UNSIGNED NULL,
                    `supplier_id` BIGINT UNSIGNED NULL,
                    `user_id` BIGINT UNSIGNED NOT NULL,
                    `branch_id` BIGINT UNSIGNED NULL,
                    `return_no` VARCHAR(100) NOT NULL UNIQUE,
                    `return_date` DATE NOT NULL,
                    `subtotal` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
                    `tax_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
                    `discount_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
                    `total_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
                    `refund_method` VARCHAR(30) NOT NULL DEFAULT 'cash',
                    `return_reason` TEXT NULL,
                    `notes` TEXT NULL,
                    `created_at` TIMESTAMP NULL DEFAULT NULL,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL,
                    INDEX `idx_purchase_returns_purchase_id` (`purchase_id`),
                    INDEX `idx_purchase_returns_supplier_id` (`supplier_id`),
                    INDEX `idx_purchase_returns_user_id` (`user_id`),
                    INDEX `idx_purchase_returns_branch_id` (`branch_id`),
                    INDEX `idx_purchase_returns_return_date` (`return_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 5. Create purchase_return_items table
        if (! Schema::hasTable('purchase_return_items')) {
            DB::statement("
                CREATE TABLE IF NOT EXISTS `purchase_return_items` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `purchase_return_id` BIGINT UNSIGNED NOT NULL,
                    `purchase_item_id` BIGINT UNSIGNED NULL,
                    `product_id` BIGINT UNSIGNED NOT NULL,
                    `quantity` DECIMAL(12, 2) NOT NULL DEFAULT 1.00,
                    `return_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
                    `total_amount` DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
                    `created_at` TIMESTAMP NULL DEFAULT NULL,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL,
                    INDEX `idx_pri_purchase_return_id` (`purchase_return_id`),
                    INDEX `idx_pri_purchase_item_id` (`purchase_item_id`),
                    INDEX `idx_pri_product_id` (`product_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->downForSqlite();

            return;
        }

        DB::statement("DROP TABLE IF EXISTS `purchase_return_items`");
        DB::statement("DROP TABLE IF EXISTS `purchase_returns`");
        DB::statement("DROP TABLE IF EXISTS `stocks`");

        $purchaseCols = collect(DB::select("SHOW COLUMNS FROM purchases"))->pluck('Field')->all();
        $colsToDrop = array_intersect([
            'purchase_no',
            'branch_id',
            'subtotal',
            'tax_percent',
            'tax_amount',
            'discount_percent',
            'discount_amount',
            'payment_method',
        ], $purchaseCols);

        if (! empty($colsToDrop)) {
            $dropStr = implode(', ', array_map(fn($col) => "DROP COLUMN `{$col}`", $colsToDrop));
            DB::statement("ALTER TABLE `purchases` {$dropStr}");
        }

        $productCols = collect(DB::select("SHOW COLUMNS FROM products"))->pluck('Field')->all();
        if (in_array('category_id', $productCols, true)) {
            DB::statement("ALTER TABLE `products` DROP COLUMN `category_id`");
        }
    }

    private function upForSqlite(): void
    {
        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->nullable()->after('id')->index('idx_products_category_id');
            });
        }

        if (Schema::hasTable('purchases')) {
            $columns = Schema::getColumnListing('purchases');
            Schema::table('purchases', function (Blueprint $table) use ($columns) {
                if (! in_array('purchase_no', $columns, true)) {
                    $table->string('purchase_no', 50)->nullable()->after('id')->index('idx_purchases_purchase_no');
                }
                if (! in_array('branch_id', $columns, true)) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('user_id')->index('idx_purchases_branch_id');
                }
                if (! in_array('subtotal', $columns, true)) {
                    $table->decimal('subtotal', 12, 2)->default(0)->after('purchase_date');
                }
                if (! in_array('tax_percent', $columns, true)) {
                    $table->decimal('tax_percent', 8, 2)->default(0)->after('subtotal');
                }
                if (! in_array('tax_amount', $columns, true)) {
                    $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percent');
                }
                if (! in_array('discount_percent', $columns, true)) {
                    $table->decimal('discount_percent', 8, 2)->default(0)->after('tax_amount');
                }
                if (! in_array('discount_amount', $columns, true)) {
                    $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_percent');
                }
                if (! in_array('payment_method', $columns, true)) {
                    $table->string('payment_method', 30)->default('cash')->after('total_amount');
                }
            });
        }

        if (! Schema::hasTable('stocks')) {
            Schema::create('stocks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('item_id');
                $table->string('voucher_no', 100);
                $table->unsignedTinyInteger('voucher_type');
                $table->date('voucher_date');
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('from_branch_id')->nullable();
                $table->unsignedBigInteger('to_branch_id')->nullable();
                $table->decimal('qty', 12, 2)->default(0);
                $table->decimal('rate', 12, 2)->default(0);
                $table->decimal('amount', 14, 2)->default(0);
                $table->unsignedTinyInteger('status')->default(1);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index('item_id', 'idx_stocks_item_id');
                $table->index('voucher_no', 'idx_stocks_voucher_no');
                $table->index('voucher_type', 'idx_stocks_voucher_type');
                $table->index('voucher_date', 'idx_stocks_voucher_date');
                $table->index('customer_id', 'idx_stocks_customer_id');
                $table->index('supplier_id', 'idx_stocks_supplier_id');
                $table->index('branch_id', 'idx_stocks_branch_id');
                $table->index('from_branch_id', 'idx_stocks_from_branch_id');
                $table->index('to_branch_id', 'idx_stocks_to_branch_id');
            });
        }

        if (! Schema::hasTable('purchase_returns')) {
            Schema::create('purchase_returns', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_id')->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('return_no', 100)->unique();
                $table->date('return_date');
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->string('refund_method', 30)->default('cash');
                $table->text('return_reason')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index('purchase_id', 'idx_purchase_returns_purchase_id');
                $table->index('supplier_id', 'idx_purchase_returns_supplier_id');
                $table->index('user_id', 'idx_purchase_returns_user_id');
                $table->index('branch_id', 'idx_purchase_returns_branch_id');
                $table->index('return_date', 'idx_purchase_returns_return_date');
            });
        }

        if (! Schema::hasTable('purchase_return_items')) {
            Schema::create('purchase_return_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_return_id');
                $table->unsignedBigInteger('purchase_item_id')->nullable();
                $table->unsignedBigInteger('product_id');
                $table->decimal('quantity', 12, 2)->default(1);
                $table->decimal('return_price', 12, 2)->default(0);
                $table->decimal('total_amount', 14, 2)->default(0);
                $table->timestamps();
                $table->index('purchase_return_id', 'idx_pri_purchase_return_id');
                $table->index('purchase_item_id', 'idx_pri_purchase_item_id');
                $table->index('product_id', 'idx_pri_product_id');
            });
        }
    }

    private function downForSqlite(): void
    {
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
        Schema::dropIfExists('stocks');

        if (Schema::hasTable('purchases')) {
            $columns = array_intersect([
                'purchase_no',
                'branch_id',
                'subtotal',
                'tax_percent',
                'tax_amount',
                'discount_percent',
                'discount_amount',
                'payment_method',
            ], Schema::getColumnListing('purchases'));

            if ($columns) {
                Schema::table('purchases', fn (Blueprint $table) => $table->dropColumn(array_values($columns)));
            }
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', fn (Blueprint $table) => $table->dropColumn('category_id'));
        }
    }
};
