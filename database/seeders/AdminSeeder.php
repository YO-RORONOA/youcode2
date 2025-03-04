<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('name', 'Admin')->first();

        $admin = User::create([
            'name' => 'Administrateur',
            'username' => 'admin',
            'email' => 'admin@youcode.ma',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'is_verified' => true,
        ]);

        $admin->roles()->attach($adminRole);
    }
}