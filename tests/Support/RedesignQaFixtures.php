<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\{Branch, Category, Counter, Customer, Deal, Order, Product, Purchase, Role, Service, Setting, Supplier, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class RedesignQaFixtures
{
    public static function create(): array
    {
        if (!in_array(DB::connection()->getDatabaseName(), ['pos_service_redesign_test', 'pos_service_redesign_feature_test'], true)) {
            throw new \LogicException('QA fixtures require an explicitly guarded redesign test database.');
        }

        $settings = [
            'app_name' => 'Salevince POS', 'app_description' => 'SERVICES | DEALS | BILLING',
            'currency_symbol' => 'PKR', 'warning_quantity' => '10',
            'show_products' => '1', 'show_services' => '1', 'tax_enabled' => '1',
            'discount_enabled' => '1', 'editable_item_rate' => '1', 'srb_enabled' => '0',
        ];
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        config(['settings' => $settings]);

        $role = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'QA Administrator', 'is_system' => true]);
        $user = User::firstOrCreate(['email' => 'qa-redesign@example.test'], [
            'first_name' => 'Redesign', 'last_name' => 'QA', 'password' => Hash::make('RedesignQA!2026'), 'is_active' => true,
        ]);
        $user->roles()->syncWithoutDetaching([$role->id]);
        (new \Database\Seeders\PermissionSeeder())->run();
        $branch = Branch::firstOrCreate(['code' => 'QA-BRANCH'], [
            'name' => 'QA Design Studio', 'address' => 'Disposable test workspace', 'phone' => '03000000000',
            'password' => 'BranchQA!2026', 'is_active' => true,
        ]);
        $counter = Counter::firstOrCreate(['code' => 'QA-01'], [
            'branch_id' => $branch->id, 'name' => 'Studio Counter', 'password' => 'CounterQA!2026', 'is_active' => true,
        ]);
        $user->branches()->syncWithoutDetaching([$branch->id]);
        $user->counters()->syncWithoutDetaching([$counter->id]);
        $category = Category::firstOrCreate(['slug' => 'qa-essentials'], ['name' => 'Studio Essentials', 'sort_order' => 1, 'status' => true]);
        $service = Service::firstOrCreate(['barcode' => 'QA-SVC-01'], ['category_id' => $category->id, 'name' => 'Signature Consultation', 'rate' => 1500, 'status' => true]);
        $deal = Deal::firstOrCreate(['barcode' => 'QA-DEAL-01'], ['name' => 'Studio Starter Package', 'original_amount' => 1500, 'discounted_amount' => 1200, 'discount_percentage' => 20, 'status' => true]);
        $deal->services()->syncWithoutDetaching([$service->id]);
        $product = Product::firstOrCreate(['barcode' => 'QA-PROD-01'], [
            'name' => 'Studio Care Kit',
            'description' => 'QA catalog item',
            'price' => 500,
            'quantity' => 6,
            'status' => true,
        ]);
        if ($product->purchase_price === null) {
            $product->purchase_price = 300;
            $product->save();
        }
        $customer = Customer::firstOrCreate(['email' => 'customer-qa@example.test'], ['first_name' => 'Ayesha', 'last_name' => 'Khan', 'phone' => '03000000001', 'address' => 'QA Customer Address', 'user_id' => $user->id]);
        $supplier = Supplier::firstOrCreate(['email' => 'supplier-qa@example.test'], ['first_name' => 'Studio', 'last_name' => 'Supply Co', 'phone' => '03000000002', 'address' => 'QA Supplier Address']);
        $order = Order::firstOrCreate(['invoice_no' => 'QA-POS-0001'], [
            'customer_id' => $customer->id, 'user_id' => $user->id, 'branch_id' => $branch->id, 'counter_id' => $counter->id,
            'subtotal' => 2000, 'tax_percent' => 8, 'tax_amount' => 160, 'discount_percent' => 5, 'discount_amount' => 100, 'total_amount' => 2060,
        ]);
        if (!$order->items()->exists()) {
            $order->items()->create(['item_id' => $product->id, 'item_type' => 0, 'item_name' => $product->name, 'price' => 500, 'quantity' => 1]);
            $order->items()->create(['item_id' => $service->id, 'item_type' => 1, 'item_name' => $service->name, 'price' => 1500, 'quantity' => 1]);
            $order->payments()->create(['user_id' => $user->id, 'amount' => 1500]);
        }
        $purchase = Purchase::firstOrCreate(['notes' => 'QA inventory delivery'], [
            'supplier_id' => $supplier->id, 'user_id' => $user->id, 'purchase_date' => now()->toDateString(), 'total_amount' => 1500, 'status' => 'completed',
        ]);
        if (!$purchase->items()->exists()) {
            $purchase->items()->create(['product_id' => $product->id, 'quantity' => 5, 'purchase_price' => 300]);
        }

        return compact('user', 'role', 'branch', 'counter', 'category', 'service', 'deal', 'product', 'customer', 'supplier', 'order', 'purchase');
    }
}
