<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Counter;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Service;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Creates a small, linked set of clearly marked records for checking admin UIs.
 *
 * Run explicitly with: php artisan db:seed --class=UiTestDataSeeder
 * This seeder is intentionally not called by DatabaseSeeder and is safe to rerun.
 */
class UiTestDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $user = User::query()->orderBy('id')->first();
            $mainBranch = Branch::query()->where('is_active', true)->orderBy('id')->first();

            if (! $user || ! $mainBranch) {
                throw new RuntimeException('UI test data requires an existing user and active branch.');
            }

            $testBranch = Branch::updateOrCreate(
                ['code' => 'UIQA'],
                [
                    'name' => 'UI Test Branch',
                    'address' => 'UI testing location',
                    'phone' => '03000000001',
                    'password' => 'UiTest2026!',
                    'is_active' => true,
                ]
            );

            $mainCounter = Counter::query()->where('branch_id', $mainBranch->id)->orderBy('id')->first();
            if (! $mainCounter) {
                $mainCounter = Counter::updateOrCreate(
                    ['code' => 'UI-MAIN-01'],
                    [
                        'branch_id' => $mainBranch->id,
                        'name' => 'UI Test Counter',
                        'password' => 'UiTest2026!',
                        'is_active' => true,
                    ]
                );
            }

            $testCounter = Counter::updateOrCreate(
                ['code' => 'UI-QA-01'],
                [
                    'branch_id' => $testBranch->id,
                    'name' => 'UI Test Counter',
                    'password' => 'UiTest2026!',
                    'is_active' => true,
                ]
            );

            $category = Category::updateOrCreate(
                ['slug' => 'ui-test-category'],
                [
                    'name' => 'UI Test Category',
                    'description' => 'Sample category for admin UI checks.',
                    'sort_order' => 0,
                    'status' => true,
                ]
            );

            $customer = Customer::updateOrCreate(
                ['email' => 'ui-test-customer@example.test'],
                [
                    'first_name' => 'Jamie',
                    'last_name' => 'UI Tester',
                    'phone' => '03001230001',
                    'address' => 'UI testing address',
                    'user_id' => $user->id,
                ]
            );

            $supplier = Supplier::updateOrCreate(
                ['email' => 'ui-test-supplier@example.test'],
                [
                    'first_name' => 'Taylor',
                    'last_name' => 'Sample Supply',
                    'phone' => '03001230002',
                    'address' => 'UI testing supplier address',
                ]
            );

            $product = Product::updateOrCreate(
                ['barcode' => 'UI-TEST-10001'],
                [
                    'category_id' => $category->id,
                    'name' => 'UI Test Sample Product',
                    'description' => 'Clearly labeled sample product for UI testing.',
                    'price' => 150,
                    'purchase_price' => 100,
                    'quantity' => 50,
                    'status' => true,
                ]
            );

            $service = Service::updateOrCreate(
                ['barcode' => 'UI-TEST-SVC-001'],
                [
                    'category_id' => $category->id,
                    'name' => 'UI Test Sample Service',
                    'description' => 'Sample service linked to the UI test deal.',
                    'rate' => 250,
                    'status' => true,
                ]
            );

            $deal = Deal::updateOrCreate(
                ['barcode' => 'UI-TEST-DEAL-001'],
                [
                    'name' => 'UI Test Service Bundle',
                    'description' => 'Sample bundle for verifying deal list and details.',
                    'original_amount' => 500,
                    'discounted_amount' => 425,
                    'discount_percentage' => 15,
                    'status' => true,
                ]
            );
            $deal->services()->sync([$service->id]);

            PaymentMethod::updateOrCreate(
                ['code' => 'UI-TEST-CARD'],
                [
                    'name' => 'UI Test Card Terminal',
                    'type' => 'card',
                    'bank_name' => 'Sample Bank',
                    'account_title' => 'UI Test Merchant',
                    'account_number' => '1234567890',
                    'iban' => 'PK00UITEST0000000000001',
                    'branch_name' => 'UI Test Branch',
                    'branch_code' => '0001',
                    'swift_code' => 'UITESTPK',
                    'status' => true,
                    'notes' => 'UI-TEST sample payment method.',
                ]
            );

            $purchase = Purchase::updateOrCreate(
                ['purchase_no' => 'UI-TEST-PUR-001'],
                [
                    'supplier_id' => $supplier->id,
                    'user_id' => $user->id,
                    'branch_id' => $mainBranch->id,
                    'purchase_date' => now()->toDateString(),
                    'subtotal' => 1200,
                    'tax_percent' => 0,
                    'tax_amount' => 0,
                    'discount_percent' => 0,
                    'discount_amount' => 0,
                    'total_amount' => 1200,
                    'payment_method' => 'cash',
                    'status' => 'completed',
                    'notes' => 'UI-TEST sample purchase. Safe to remove by purchase number.',
                ]
            );

            $purchaseItem = PurchaseItem::updateOrCreate(
                ['purchase_id' => $purchase->id, 'product_id' => $product->id],
                ['quantity' => 12, 'purchase_price' => 100]
            );

            $purchaseReturn = PurchaseReturn::updateOrCreate(
                ['return_no' => 'UI-TEST-PRET-001'],
                [
                    'purchase_id' => $purchase->id,
                    'supplier_id' => $supplier->id,
                    'user_id' => $user->id,
                    'branch_id' => $mainBranch->id,
                    'return_date' => now()->toDateString(),
                    'subtotal' => 100,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'total_amount' => 100,
                    'refund_method' => 'cash',
                    'return_reason' => 'UI-TEST sample return',
                    'notes' => 'UI-TEST sample purchase return.',
                ]
            );

            PurchaseReturnItem::updateOrCreate(
                ['purchase_return_id' => $purchaseReturn->id, 'purchase_item_id' => $purchaseItem->id],
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'return_price' => 100,
                    'total_amount' => 100,
                ]
            );

            $order = Order::updateOrCreate(
                ['invoice_no' => 'UI-TEST-INV-001'],
                [
                    'customer_id' => $customer->id,
                    'user_id' => $user->id,
                    'branch_id' => $mainBranch->id,
                    'counter_id' => $mainCounter->id,
                    'subtotal' => 300,
                    'tax_percent' => 5,
                    'tax_amount' => 15,
                    'discount_percent' => 0,
                    'discount_amount' => 0,
                    'total_amount' => 315,
                    'payment_method' => 'cash',
                    'created_at' => now()->subHours(2),
                ]
            );

            $orderItem = OrderItem::updateOrCreate(
                ['order_id' => $order->id, 'item_id' => $product->id, 'item_type' => 0],
                [
                    'item_name' => $product->name,
                    'quantity' => 3,
                    'price' => 300,
                ]
            );

            DB::table('payments')->updateOrInsert(
                ['order_id' => $order->id, 'user_id' => $user->id],
                [
                    'amount' => 315,
                    'payment_method' => 'cash',
                    'card_holder_name' => null,
                    'card_last_four' => null,
                    'receiving_account_id' => null,
                    'created_at' => $order->created_at,
                    'updated_at' => now(),
                ]
            );

            $saleReturn = SaleReturn::updateOrCreate(
                ['return_no' => 'UI-TEST-SRET-001'],
                [
                    'order_id' => $order->id,
                    'customer_id' => $customer->id,
                    'user_id' => $user->id,
                    'branch_id' => $mainBranch->id,
                    'counter_id' => $mainCounter->id,
                    'refund_subtotal' => 100,
                    'refund_tax' => 5,
                    'refund_discount' => 0,
                    'refund_total' => 105,
                    'refund_method' => 'cash',
                    'return_reason' => 'UI-TEST sample return',
                    'notes' => 'UI-TEST sample sale return.',
                    'created_at' => now()->subHour(),
                ]
            );

            SaleReturnItem::updateOrCreate(
                ['sale_return_id' => $saleReturn->id, 'order_item_id' => $orderItem->id],
                [
                    'item_id' => $product->id,
                    'item_type' => 0,
                    'item_name' => $product->name,
                    'quantity' => 1,
                    'unit_price' => 100,
                    'refund_amount' => 105,
                ]
            );

            $stockRows = [
                [
                    'voucher_no' => 'UI-TEST-OPEN-001',
                    'voucher_type' => Stock::TYPE_OPENING,
                    'branch_id' => $mainBranch->id,
                    'from_branch_id' => null,
                    'to_branch_id' => null,
                    'qty' => 41,
                    'rate' => 100,
                    'amount' => 4100,
                    'supplier_id' => null,
                    'customer_id' => null,
                    'notes' => 'UI-TEST opening stock sample.',
                ],
                [
                    'voucher_no' => $purchase->purchase_no,
                    'voucher_type' => Stock::TYPE_PURCHASE,
                    'branch_id' => $mainBranch->id,
                    'from_branch_id' => null,
                    'to_branch_id' => null,
                    'qty' => 12,
                    'rate' => 100,
                    'amount' => 1200,
                    'supplier_id' => $supplier->id,
                    'customer_id' => null,
                    'notes' => 'UI-TEST purchase receipt sample.',
                ],
                [
                    'voucher_no' => $purchaseReturn->return_no,
                    'voucher_type' => Stock::TYPE_PURCHASE_RETURN,
                    'branch_id' => $mainBranch->id,
                    'from_branch_id' => null,
                    'to_branch_id' => null,
                    'qty' => 1,
                    'rate' => 100,
                    'amount' => 100,
                    'supplier_id' => $supplier->id,
                    'customer_id' => null,
                    'notes' => 'UI-TEST purchase return sample.',
                ],
                [
                    'voucher_no' => 'UI-TEST-SALE-001',
                    'voucher_type' => Stock::TYPE_SALE,
                    'branch_id' => $mainBranch->id,
                    'from_branch_id' => null,
                    'to_branch_id' => null,
                    'qty' => 3,
                    'rate' => 100,
                    'amount' => 300,
                    'supplier_id' => null,
                    'customer_id' => $customer->id,
                    'notes' => 'UI-TEST sale sample.',
                ],
                [
                    'voucher_no' => $saleReturn->return_no,
                    'voucher_type' => Stock::TYPE_SALE_RETURN,
                    'branch_id' => $mainBranch->id,
                    'from_branch_id' => null,
                    'to_branch_id' => null,
                    'qty' => 1,
                    'rate' => 100,
                    'amount' => 100,
                    'supplier_id' => null,
                    'customer_id' => $customer->id,
                    'notes' => 'UI-TEST sale return sample.',
                ],
                [
                    'voucher_no' => 'UI-TEST-TRF-001',
                    'voucher_type' => Stock::TYPE_STOCK_TRANSFER,
                    'branch_id' => null,
                    'from_branch_id' => $mainBranch->id,
                    'to_branch_id' => $testBranch->id,
                    'qty' => 6,
                    'rate' => 100,
                    'amount' => 600,
                    'supplier_id' => null,
                    'customer_id' => null,
                    'notes' => 'UI-TEST stock transfer sample.',
                ],
            ];

            foreach ($stockRows as $row) {
                Stock::updateOrCreate(
                    [
                        'voucher_no' => $row['voucher_no'],
                        'voucher_type' => $row['voucher_type'],
                        'item_id' => $product->id,
                    ],
                    $row + [
                        'item_id' => $product->id,
                        'voucher_date' => now()->toDateString(),
                        'status' => 1,
                    ]
                );
            }
        });

        $this->command?->info('UI test sample data is ready. All records are tagged UI-TEST and can be safely reseeded.');
    }
}
