<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Pepsi 1.5L',
                'description' => 'Chilled 1.5 Liter Soft Drink Bottle',
                'barcode' => '100001',
                'price' => 180.00,
                'purchase_price' => 140.00,
                'quantity' => 100,
                'status' => true,
            ],
            [
                'name' => 'Coca Cola 1.5L',
                'description' => 'Refreshing 1.5 Liter Cola Bottle',
                'barcode' => '100002',
                'price' => 180.00,
                'purchase_price' => 140.00,
                'quantity' => 100,
                'status' => true,
            ],
            [
                'name' => 'Lays Potato Chips Large',
                'description' => 'Masala Flavor Crispy Potato Chips',
                'barcode' => '100003',
                'price' => 100.00,
                'purchase_price' => 75.00,
                'quantity' => 150,
                'status' => true,
            ],
            [
                'name' => 'Nestle Pure Life Water 1.5L',
                'description' => 'Purified Mineral Drinking Water',
                'barcode' => '100004',
                'price' => 90.00,
                'purchase_price' => 65.00,
                'quantity' => 200,
                'status' => true,
            ],
            [
                'name' => 'Cadbury Dairy Milk Silk',
                'description' => 'Premium Chocolate Bar 150g',
                'barcode' => '100005',
                'price' => 350.00,
                'purchase_price' => 280.00,
                'quantity' => 80,
                'status' => true,
            ],
            [
                'name' => 'Olpers Milk 1L',
                'description' => 'Full Cream UHT Milk Pack 1 Liter',
                'barcode' => '100006',
                'price' => 290.00,
                'purchase_price' => 250.00,
                'quantity' => 120,
                'status' => true,
            ],
            [
                'name' => 'Nescafe Gold Coffee 100g',
                'description' => 'Rich & Aromatic Instant Coffee Jar',
                'barcode' => '100007',
                'price' => 1450.00,
                'purchase_price' => 1200.00,
                'quantity' => 40,
                'status' => true,
            ],
            [
                'name' => 'Lipton Yellow Label Tea 450g',
                'description' => 'Black Tea Leaves Box',
                'barcode' => '100008',
                'price' => 950.00,
                'purchase_price' => 800.00,
                'quantity' => 60,
                'status' => true,
            ],
            [
                'name' => 'Dawn Bread Large',
                'description' => 'Fresh White Sandwich Bread Pack',
                'barcode' => '100009',
                'price' => 160.00,
                'purchase_price' => 130.00,
                'quantity' => 50,
                'status' => true,
            ],
            [
                'name' => 'Tapal Danedar Tea 450g',
                'description' => 'Premium Strong Danedar Tea Pack',
                'barcode' => '100010',
                'price' => 920.00,
                'purchase_price' => 780.00,
                'quantity' => 70,
                'status' => true,
            ],
            [
                'name' => 'Shan Biryani Masala',
                'description' => 'Authentic Spice Mix for Chicken Biryani',
                'barcode' => '100011',
                'price' => 130.00,
                'purchase_price' => 100.00,
                'quantity' => 200,
                'status' => true,
            ],
            [
                'name' => 'Surf Excel Powder 1kg',
                'description' => 'Washing Powder Pack 1 kg',
                'barcode' => '100012',
                'price' => 680.00,
                'purchase_price' => 570.00,
                'quantity' => 90,
                'status' => true,
            ],
            [
                'name' => 'Lux Soap Bar 140g',
                'description' => 'Fragrant Rose Beauty Soap',
                'barcode' => '100013',
                'price' => 150.00,
                'purchase_price' => 115.00,
                'quantity' => 180,
                'status' => true,
            ],
            [
                'name' => 'Head & Shoulders Shampoo 360ml',
                'description' => 'Anti-Dandruff Smooth & Silky Shampoo',
                'barcode' => '100014',
                'price' => 790.00,
                'purchase_price' => 650.00,
                'quantity' => 60,
                'status' => true,
            ],
            [
                'name' => 'Colgate Toothpaste 150g',
                'description' => 'MaxFresh Peppermint Toothpaste',
                'barcode' => '100015',
                'price' => 280.00,
                'purchase_price' => 220.00,
                'quantity' => 110,
                'status' => true,
            ],
        ];

        foreach ($products as $item) {
            Product::updateOrCreate(['barcode' => $item['barcode']], $item);
        }

        $services = [
            [
                'name' => 'Home Delivery Service',
                'description' => 'Express doorstep delivery',
                'barcode' => '200001',
                'rate' => 200.00,
                'status' => true,
            ],
            [
                'name' => 'Gift Wrapping Service',
                'description' => 'Decorative paper & ribbon gift wrap',
                'barcode' => '200002',
                'rate' => 100.00,
                'status' => true,
            ],
        ];

        foreach ($services as $srv) {
            Service::updateOrCreate(['barcode' => $srv['barcode']], $srv);
        }
    }
}
