<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Counter;
use App\Models\User;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = Branch::updateOrCreate([
            'code' => 'BR001',
        ], [
            'name' => 'Main Branch',
            'address' => 'Head Office',
            'phone' => '03001234567',
            'password' => 'MB01',
            'is_active' => true,
        ]);

        $counter = Counter::updateOrCreate([
            'branch_id' => $branch->id,
            'code' => 'C001',
        ], [
            'name' => 'Counter 1',
            'password' => 'CT01',
            'is_active' => true,
        ]);

        $users = User::all();
        foreach ($users as $user) {
            $user->branches()->syncWithoutDetaching([$branch->id]);
            $user->counters()->syncWithoutDetaching([$counter->id]);
        }
    }
}
