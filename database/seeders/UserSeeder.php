<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@superadmin.com',
            'password' => Hash::make('@admin123'),
            'is_admin' => true,
        ]);
        $role = Role::findById(1);
        $user->assignRole($role);
    }
}
