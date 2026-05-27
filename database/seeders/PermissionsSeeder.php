<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin_apd')->first();
        if ($adminRole) {
            $permissions = [
                'view items',
                'create items',
                'update items',
                'delete items',
                'view locations',
                'create locations',
                'update locations',
                'delete locations',
                'view cameras',
                'create cameras',
                'update cameras',
                'delete cameras',
                'view detections',
                'create detections',
                'update detections',
                'delete detections',
            ];
            foreach ($permissions as $permission) {
                Permission::updateOrCreate(['name' => $permission, 'guard_name' => 'web']);
                $adminRole->givePermissionTo($permission);
            }
        }
    }
}
