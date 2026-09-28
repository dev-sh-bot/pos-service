<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds all permissions defined in config/permissions.php and
 * grants them all to the 'admin' system role.
 *
 * Safe to run multiple times (updateOrCreate).
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $groups = config('permissions', []);

        $allIds = [];

        foreach ($groups as $group => $names) {
            foreach ($names as $name) {
                $permission = Permission::updateOrCreate(
                    ['name' => $name],
                    ['group_name' => $group]
                );
                $allIds[] = $permission->id;
            }
        }

        // Grant every permission to the admin role
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->sync($allIds);
        }
    }
}
