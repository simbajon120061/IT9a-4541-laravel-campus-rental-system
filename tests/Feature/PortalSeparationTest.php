<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_options_page_displays_renter_and_lister_portals(): void
    {
        $response = $this->get(route('login.options'));

        $response->assertOk()
            ->assertSee('Lister Portal')
            ->assertSee('Manage items you want to rent out.')
            ->assertSee('add listings, track rental requests, manage your inventory')
            ->assertSee('Renter Portal')
            ->assertSee('Find and rent campus items.')
            ->assertSee('browse the marketplace, request rentals, check your active bookings')
            ->assertSee(route('login', ['portal' => 'lister']), false)
            ->assertSee(route('login', ['portal' => 'renter']), false);
    }

    public function test_lister_portal_login_redirects_to_lister_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'lister@umindanao.edu.ph',
        ]);

        $this->get(route('login', ['portal' => 'lister']))
            ->assertOk()
            ->assertSee('Access your Lister Account');

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('lister.dashboard', absolute: false));
    }

    public function test_renter_and_lister_navigation_are_separated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('renter.dashboard'))
            ->assertOk()
            ->assertSee('Renter Dashboard')
            ->assertSee('Marketplace')
            ->assertSee('My Rentals')
            ->assertDontSee('My Listings')
            ->assertDontSee('Inventory');

        $this->actingAs($user)
            ->get(route('lister.dashboard'))
            ->assertOk()
            ->assertSee('Lister Dashboard')
            ->assertSee('My Listings')
            ->assertSee('Inventory')
            ->assertDontSee('Marketplace')
            ->assertDontSee('My Rentals');
    }
}
