<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@pos.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir1@pos.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Supervisor',
            'email' => 'supervisor@pos.com',
            'password' => Hash::make('password'),
            'role' => 'supervisor',
            'is_active' => true,
        ]);
    }
}
