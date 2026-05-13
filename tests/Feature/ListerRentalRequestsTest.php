<?php

namespace Tests\Feature;

use App\Livewire\ListerRentalRequests;
use App\Models\Category;
use App\Models\Item;
use App\Models\Rental;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListerRentalRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_lister_can_filter_group_sort_and_open_item_history_from_requests_table(): void
    {
        $owner = User::factory()->create();
        $recentRenter = User::factory()->create(['name' => 'Recent Renter']);
        $oldRenter = User::factory()->create(['name' => 'Old Renter']);
        $clothingRenter = User::factory()->create(['name' => 'Clothing Renter']);

        $electronics = Category::query()->firstOrCreate(
            ['slug' => 'electronics'],
            [
                'name' => 'Electronics',
                'icon' => 'chip',
                'is_active' => true,
            ]
        );

        $clothing = Category::query()->firstOrCreate(
            ['slug' => 'clothing'],
            [
                'name' => 'Clothing',
                'icon' => 'shirt',
                'is_active' => true,
            ]
        );

        $camera = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Campus Camera',
            'description' => 'Mirrorless camera',
            'condition' => 'Good',
            'price' => 250,
            'status' => 'available',
            'category_id' => $electronics->id,
        ]);

        $tripod = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Studio Tripod',
            'description' => 'Adjustable tripod',
            'condition' => 'Good',
            'price' => 90,
            'status' => 'available',
            'category_id' => $electronics->id,
        ]);

        $coat = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Formal Coat',
            'description' => 'Event coat',
            'condition' => 'Good',
            'price' => 120,
            'status' => 'available',
            'category_id' => $clothing->id,
        ]);

        $recentRental = $this->createPendingRental($tripod, $recentRenter, now()->subHour());
        $oldRental = $this->createPendingRental($camera, $oldRenter, now()->subDays(2));
        $this->createPendingRental($coat, $clothingRenter, now()->subMinutes(10));

        Livewire::actingAs($owner)
            ->test(ListerRentalRequests::class)
            ->assertSee('All categories')
            ->assertSee('Grouped by item')
            ->assertSee('Newest first')
            ->assertSee('History')
            ->assertSee(route('rental-requests.item', $recentRental->item), false)
            ->assertSeeInOrder(['Clothing Renter', 'Recent Renter', 'Old Renter'])
            ->set('dateSort', 'oldest')
            ->assertSeeInOrder(['Old Renter', 'Recent Renter', 'Clothing Renter'])
            ->set('categoryFilter', (string) $electronics->id)
            ->assertSee('Recent Renter')
            ->assertSee('Old Renter')
            ->assertDontSee('Clothing Renter')
            ->assertSee(route('rental-requests.item', $oldRental->item), false)
            ->set('itemGrouping', 'grouped')
            ->assertSee('bg-violet-50/70', false)
            ->assertSee('Campus Camera')
            ->assertSee('Studio Tripod');
    }

    private function createPendingRental(Item $item, User $renter, DateTimeInterface $createdAt): Rental
    {
        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 180,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_PENDING,
        ]);

        $rental->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        return $rental;
    }
}
