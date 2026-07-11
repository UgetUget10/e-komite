<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'Kepala Madrasah')->first();
        if (!$role) {
            Role::create([
                'id' => Str::uuid(),
                'name' => 'Kepala Madrasah',
                'guard_name' => 'web',
            ]);
        }
    }
}
