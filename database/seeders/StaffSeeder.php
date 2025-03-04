<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    public function run()
    {
        $cmeRole = Role::where('name', 'CME')->first();
        $coachRole = Role::where('name', 'Coach')->first();

        // Créer un CME
        $cmeUser = User::create([
            'name' => 'CME Test',
            'username' => 'cme',
            'email' => 'cme@youcode.ma',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'is_verified' => true,
        ]);

        $cmeUser->roles()->attach($cmeRole);

        Staff::create([
            'user_id' => $cmeUser->id,
            'speciality' => 'Entretien CME',
        ]);

        // Créer un Coach
        $coachUser = User::create([
            'name' => 'Coach Test',
            'username' => 'coach',
            'email' => 'coach@youcode.ma',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'is_verified' => true,
        ]);

        $coachUser->roles()->attach($coachRole);

        Staff::create([
            'user_id' => $coachUser->id,
            'speciality' => 'Développement Web',
        ]);
    }
}