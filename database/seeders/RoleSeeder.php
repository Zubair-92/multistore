<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator'],
            ['name' => 'sub_admin', 'display_name' => 'Sub Administrator'],
            ['name' => 'store', 'display_name' => 'Store Owner'],
            ['name' => 'staff', 'display_name' => 'Staff'],
            ['name' => 'user', 'display_name' => 'User'], // instead of 'customer'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
