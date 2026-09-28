<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Counter;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DashboardDemoSeeder extends Seeder
{
    /**
     * Seed comprehensive realistic demo data for the ultimate dashboard experience.
     */
    public function run(): void
    {
        $admin = User::first() ?? User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'admin',
            'email' => 'admin@gmail.com',
        ]);

        $branch = Branch::first() ?? Branch::create(['name' => 'Main Branch', 'code' => 'BR001', 'password' => '1234', 'status' => true]);
        $counter = Counter::first() ?? Counter::create(['name' => 'Counter 1', 'code' => 'CNT-01', 'branch_id' => $branch->id, 'password' => '1234', 'status' => true]);

        // 1. Seed realistic customers
        $customersData = [
            ['first_name' => 'Muhammad', 'last_name' => 'Ali', 'email' => 'ali.khan@gmail.com', 'phone' => '03001234567', 'address' => 'Clifton Block 5, Karachi', 'user_id' => $admin->id],
            ['first_name' => 'Usman', 'last_name' => 'Tariq', 'email' => 'usman.tariq@gmail.com', 'phone' => '03219876543', 'address' => 'DHA Phase 6, Lahore', 'user_id' => $admin->id],
            ['first_name' => 'Bilal', 'last_name' => 'Ahmed', 'email' => 'bilal.ahmed@yahoo.com', 'phone' => '03335554433', 'address' => 'F-8/3, Islamabad', 'user_id' => $admin->id],
            ['first_name' => 'Ayesha', 'last_name' => 'Siddiqui', 'email' => 'ayesha.s@outlook.com', 'phone' => '03451122334', 'address' => 'Gulberg III, Lahore', 'user_id' => $admin->id],
            ['first_name' => 'Hamza', 'last_name' => 'Sheikh', 'email' => 'hamza.sheikh@gmail.com', 'phone' => '03124455667', 'address' => 'Bahria Town Phase 4, Rawalpindi', 'user_id' => $admin->id],
            ['first_name' => 'Fatima', 'last_name' => 'Zahra', 'email' => 'fatima.zahra@gmail.com', 'phone' => '03017788990', 'address' => 'PECHS Block 2, Karachi', 'user_id' => $admin->id],
            ['first_name' => 'Zain', 'last_name' => 'Ul Abidin', 'email' => 'zain.pos@gmail.com', 'phone' => '03348899001', 'address' => 'North Nazimabad, Karachi', 'user_id' => $admin->id],
            ['first_name' => 'Sara', 'last_name' => 'Malik', 'email' => 'sara.malik@hotmail.com', 'phone' => '03223344556', 'address' => 'Model Town, Lahore', 'user_id' => $admin->id],
        ];

        foreach ($customersData as $c) {
            Customer::updateOrCreate(['email' => $c['email']], $c);
        }
        $customers = Customer::all();

        // 2. Ensure products and services exist
        $this->call(ProductSeeder::class);
        $products = Product::all();
        $services = Service::all();

        if ($products->isEmpty()) {
            return;
        }

        // 3. Clear older demo orders to avoid duplication
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        OrderItem::truncate();
        Payment::truncate();
        Order::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 4. Generate orders across 12 months with realistic dates & amounts
        $now = Carbon::now();

        // Monthly order distribution
        $monthDistribution = [
            11 => 15, // 11 months ago
            10 => 18,
            9  => 22,
            8  => 20,
            7  => 26,
            6  => 30,
            5  => 28,
            4  => 35,
            3  => 40,
            2  => 45,
            1  => 52, // Last month
            0  => 38, // Current month
        ];

        $orderIndex = 1001;

        foreach ($monthDistribution as $monthsAgo => $numOrders) {
            $baseMonth = $now->copy()->subMonths($monthsAgo);
            $daysInMonth = $monthsAgo === 0 ? $now->day : $baseMonth->daysInMonth;

            for ($i = 0; $i < $numOrders; $i++) {
                $day = rand(1, max(1, $daysInMonth));
                $hour = rand(9, 22);
                $minute = rand(0, 59);
                $createdAt = $baseMonth->copy()->day($day)->hour($hour)->minute($minute);

                $customer = $customers->random();
                $orderNumber = 'ORD-' . $orderIndex++;

                // Pick 1 to 4 random products
                $orderProducts = $products->random(rand(1, min(4, $products->count())));
                $subtotal = 0;
                $itemsToCreate = [];

                foreach ($orderProducts as $prod) {
                    $qty = rand(1, 3);
                    $price = (float) $prod->price;
                    $subtotal += ($price * $qty);
                    $itemsToCreate[] = [
                        'item_type' => 0,
                        'item_name' => $prod->name,
                        'item_id' => $prod->id,
                        'price' => $price,
                        'quantity' => $qty,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ];
                }

                // Occasionally add a service item
                if ($services->isNotEmpty() && rand(1, 3) === 1) {
                    $srv = $services->random();
                    $srvPrice = (float) (($srv->rate ?? $srv->price ?? 0) > 0 ? ($srv->rate ?? $srv->price) : rand(200, 800));
                    $subtotal += $srvPrice;
                    $itemsToCreate[] = [
                        'item_type' => 1,
                        'item_name' => $srv->name,
                        'item_id' => $srv->id,
                        'price' => $srvPrice,
                        'quantity' => 1,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ];
                }

                $taxPercent = 5.0;
                $taxAmount = round(($subtotal * $taxPercent) / 100, 2);
                $discountAmount = (rand(1, 4) === 1) ? round(rand(50, 200), 2) : 0.0;
                $totalAmount = max(0, round($subtotal + $taxAmount - $discountAmount, 2));

                $order = Order::create([
                    'customer_id' => $customer->id,
                    'user_id' => $admin->id,
                    'branch_id' => $branch->id,
                    'counter_id' => $counter->id,
                    'subtotal' => $subtotal,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'discount_percent' => $discountAmount > 0 ? 5 : 0,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'srb_invoice_id' => 'SRB-' . rand(10000000, 99999999),
                    'srb_status' => 'success',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                foreach ($itemsToCreate as $item) {
                    $item['order_id'] = $order->id;
                    OrderItem::create($item);
                }

                Payment::create([
                    'amount' => $totalAmount,
                    'order_id' => $order->id,
                    'user_id' => $admin->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }

        // 5. Ensure multiple orders for today so Today's KPI is vibrant
        for ($t = 0; $t < 6; $t++) {
            $todayTime = $now->copy()->subHours(rand(1, 8))->subMinutes(rand(5, 50));
            $customer = $customers->random();
            $orderProducts = $products->random(rand(2, 4));
            $subtotal = 0;
            $itemsToCreate = [];

            foreach ($orderProducts as $prod) {
                $qty = rand(1, 2);
                $price = (float) $prod->price;
                $subtotal += ($price * $qty);
                $itemsToCreate[] = [
                    'item_type' => 0,
                    'item_name' => $prod->name,
                    'item_id' => $prod->id,
                    'price' => $price,
                    'quantity' => $qty,
                    'created_at' => $todayTime,
                    'updated_at' => $todayTime,
                ];
            }

            $taxAmount = round(($subtotal * 5) / 100, 2);
            $totalAmount = round($subtotal + $taxAmount, 2);

            $order = Order::create([
                'customer_id' => $customer->id,
                'user_id' => $admin->id,
                'branch_id' => $branch->id,
                'counter_id' => $counter->id,
                'subtotal' => $subtotal,
                'tax_percent' => 5,
                'tax_amount' => $taxAmount,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'srb_invoice_id' => 'SRB-' . rand(10000000, 99999999),
                'srb_status' => 'success',
                'created_at' => $todayTime,
                'updated_at' => $todayTime,
            ]);

            foreach ($itemsToCreate as $item) {
                $item['order_id'] = $order->id;
                OrderItem::create($item);
            }

            Payment::create([
                'amount' => $totalAmount,
                'order_id' => $order->id,
                'user_id' => $admin->id,
                'created_at' => $todayTime,
                'updated_at' => $todayTime,
            ]);
        }
    }
}
