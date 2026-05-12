<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_lister_dashboard_displays_summary_cards_quick_links_and_recent_activity(): void
    {
        $owner = User::factory()->create();
        $renter = User::factory()->create();

        $availableItem = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Biology Textbook',
            'description' => 'Reference textbook',
            'condition' => 'Good',
            'price' => 50,
            'status' => 'available',
        ]);

        $rentedItem = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Scientific Calculator',
            'description' => 'For engineering classes',
            'condition' => 'Like New',
            'price' => 80,
            'status' => 'rented',
        ]);

        Rental::query()->create([
            'item_id' => $rentedItem->id,
            'renter_id' => $renter->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 240,
            'paid_amount' => 100,
            'payment_status' => 'partial',
            'status' => 'active',
        ]);

        Rental::query()->create([
            'item_id' => $availableItem->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'total_price' => 50,
            'paid_amount' => 0,
            'payment_status' => 'outstanding',
            'status' => 'pending',
        ]);

        Rental::query()->create([
            'item_id' => $availableItem->id,
            'renter_id' => $renter->id,
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(7),
            'total_price' => 300,
            'paid_amount' => 300,
            'payment_status' => 'fully_paid',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($owner)->get(route('lister.dashboard'));

        $response->assertOk()
            ->assertSee('Lister Dashboard')
            ->assertSee('Manage listed items, rental requests, payments, and inventory activity.')
            ->assertSee('Total Listings')
            ->assertSee('Available Listings')
            ->assertSee('Pending Requests')
            ->assertSee('Total Earnings')
            ->assertSee('&#8369;400', false)
            ->assertSee('Lister Actions')
            ->assertSee('My Listings')
            ->assertSee('Inventory')
            ->assertSee('Scientific Calculator')
            ->assertSee('Biology Textbook');
    }
}
