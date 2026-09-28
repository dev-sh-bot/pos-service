<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Brides & Party Makeup',
            'Bridal Spa Services',
            'Hair & Color',
            'Facials',
            'Hands & Feet Care',
            'Massage & Body Cleansing',
            'Wax & Threading',
            'ZA Nails',
            'Hydra Facials',
            'Senior Specialist Facials',
            'Hair & Scalp Spa I',
            'Hair & Scalp Spa II',
            'ZA Hammam Experience',
        ];

        foreach ($categories as $index => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'sort_order' => $index + 1,
                    'status' => true,
                ]
            );
        }
    }
}
