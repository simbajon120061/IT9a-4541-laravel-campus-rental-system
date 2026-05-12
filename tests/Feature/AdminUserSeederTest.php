<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_seeder_creates_a_working_admin_account(): void
    {
        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', AdminUserSeeder::Email)->firstOrFail();

        $this->assertTrue($admin->isAdministrator());
        $this->assertTrue(Hash::check('password', $admin->password));
    }

    public function test_admin_user_seeder_can_reset_an_existing_admin_account(): void
    {
        User::factory()->create([
            'email' => AdminUserSeeder::Email,
            'password' => Hash::make('old-password'),
            'is_admin' => false,
        ]);

        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', AdminUserSeeder::Email)->firstOrFail();

        $this->assertTrue($admin->isAdministrator());
        $this->assertTrue(Hash::check('password', $admin->password));
    }

    public function test_database_seeder_uses_umindanao_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => AdminUserSeeder::Email,
            'is_admin' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@umindanao.edu.ph',
        ]);
    }
}
