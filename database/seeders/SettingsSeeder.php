<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $data = [
            ['key' => 'app_name', 'value' => 'Salevince POS'],
            ['key' => 'app_description', 'value' => 'SERVICES | DEALS | BILLING'],
            ['key' => 'currency_symbol', 'value' => 'PKR'],
            ['key' => 'warning_quantity', 'value' => 10],
        ];

        foreach ($data as $value) {
            Setting::updateOrCreate([
                'key' => $value['key']
            ], [
                'value' => $value['value']
            ]);
        }
    }
}
