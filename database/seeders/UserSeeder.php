<?php

namespace Database\Seeders;

use App\Enums\Users\RoleEnum;
use App\Enums\Users\PermissionEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = RoleEnum::cases();
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r->value]);
        }

        $permissions = PermissionEnum::cases();
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p->value]);
        }

        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@admin.ru'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ],  
        );
        
        $hr = User::firstOrCreate(
            ['email' => 'hr@hr.ru'],
            [
                'name' => 'HR',
                'password' => bcrypt('password'),
            ],
        );
        if (!$superAdmin->hasRole(RoleEnum::Admin->value)) {
            $superAdmin->assignRole(RoleEnum::Admin->value);
        }
        if (!$hr->hasRole(RoleEnum::HR->value)) {
            $hr->assignRole(RoleEnum::HR->value);
        }
    }
}
