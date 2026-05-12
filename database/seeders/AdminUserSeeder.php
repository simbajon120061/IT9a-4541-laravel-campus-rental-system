<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    public const string Email = 'admin@umindanao.edu.ph';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => self::Email,
        ], [
            'name' => 'Admin User',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);
    }
}
