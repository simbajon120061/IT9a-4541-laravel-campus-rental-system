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

        $onProcessRental = Rental::query()->create([
            'item_id' => $availableItem->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(4),
            'total_price' => 100,
            'paid_amount' => 100,
            'payment_status' => 'fully_paid',
            'status' => 'approved',
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
            ->assertSee('Due Soon Alert')
            ->assertSee('1 rental due within 7 days.')
            ->assertSee('Nearest return: 3 days left.')
            ->assertSee('Total Listings')
            ->assertSee('Available Listings')
            ->assertSee('Pending Requests')
            ->assertSee('On Process')
            ->assertSee('Message renters and monitor handoff.')
            ->assertSee('Due Soon')
            ->assertSee('Total Earnings')
            ->assertSee('&#8369;500', false)
            ->assertSee('href="'.route('lister.my-listings').'"', false)
            ->assertSee('href="'.route('lister.rental-requests').'"', false)
            ->assertSee('href="'.route('lister.inventory', ['filter' => 'approved']).'#rental-'.$onProcessRental->id.'"', false)
            ->assertSee('href="'.route('lister.inventory').'"', false)
            ->assertSee('href="'.route('lister.payments').'"', false)
            ->assertSee('Lister Actions')
            ->assertSee('My Listings')
            ->assertSee('Inventory')
            ->assertSee('Scientific Calculator')
            ->assertSee('Biology Textbook');
    }

    public function test_renter_dashboard_displays_due_soon_alert_and_clickable_summary_cards(): void
    {
        $owner = User::factory()->create();
        $renter = User::factory()->create();

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Camera Tripod',
            'description' => 'For media class',
            'condition' => 'Good',
            'price' => 70,
            'status' => 'rented',
        ]);

        Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(2),
            'total_price' => 140,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_ACTIVE,
        ]);

        Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(5),
            'total_price' => 210,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_PENDING,
        ]);

        $this->actingAs($renter)
            ->get(route('renter.dashboard'))
            ->assertOk()
            ->assertSee('Renter Dashboard')
            ->assertSee('Due Soon Alert')
            ->assertSee('1 rental due within 7 days.')
            ->assertSee('Nearest return: 2 days left.')
            ->assertSee('Active Rentals')
            ->assertSee('Pending Requests')
            ->assertSee('Approved Rentals')
            ->assertSee('Due Soon')
            ->assertSee('href="'.route('renter.my-rentals').'"', false)
            ->assertSee('Camera Tripod');
    }
}
