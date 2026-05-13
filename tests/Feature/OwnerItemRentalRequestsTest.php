<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerItemRentalRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_all_requests_for_a_specific_item(): void
    {
        $owner = User::factory()->create();
        $renterOne = User::factory()->create();
        $renterTwo = User::factory()->create();

        $category = Category::query()->firstOrCreate(
            ['slug' => 'clothing'],
            [
                'name' => 'Clothing',
                'icon' => 'shirt',
                'is_active' => true,
            ]
        );

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Black Textured Two-Piece Suit',
            'description' => 'Formal suit',
            'condition' => 'Good',
            'price' => 500,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        $otherItem = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Backup Item',
            'description' => 'Not part of request list',
            'condition' => 'Good',
            'price' => 100,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        $requestOne = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renterOne->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 1000,
            'paid_amount' => 0,
            'payment_status' => 'outstanding',
            'status' => 'pending',
        ]);

        $requestTwo = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renterTwo->id,
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(4),
            'total_price' => 1000,
            'paid_amount' => 0,
            'payment_status' => 'outstanding',
            'status' => 'approved',
        ]);

        Rental::query()->create([
            'item_id' => $otherItem->id,
            'renter_id' => $renterTwo->id,
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(4),
            'total_price' => 200,
            'paid_amount' => 0,
            'payment_status' => 'outstanding',
            'status' => 'pending',
        ]);

        $this->actingAs($owner)
            ->get(route('rental-requests.item', $item))
            ->assertOk()
            ->assertSee('Item Rental History')
            ->assertSee($item->name)
            ->assertSee($renterOne->name)
            ->assertSee($renterTwo->name)
            ->assertSee('Manage in Inventory')
            ->assertSee(route('lister.inventory', ['filter' => 'approved']))
            ->assertSee(route('rental-requests.show', $requestOne))
            ->assertSee(route('rental-requests.show', $requestTwo))
            ->assertDontSee('Backup Item');
    }

    public function test_non_owner_cannot_view_item_request_list_page(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Projector',
            'description' => 'Portable projector',
            'condition' => 'Good',
            'price' => 100,
            'status' => 'available',
        ]);

        $this->actingAs($otherUser)
            ->get(route('rental-requests.item', $item))
            ->assertForbidden();
    }
}
