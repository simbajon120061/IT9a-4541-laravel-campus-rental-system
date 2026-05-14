<?php

namespace Tests\Feature;

use App\Livewire\ListerRentalLogs;
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

    public function test_lister_rental_requests_page_links_to_rental_logs(): void
    {
        $owner = User::factory()->create();

        Livewire::actingAs($owner)
            ->test(ListerRentalRequests::class)
            ->assertSee('View Rental Logs')
            ->assertSee(route('lister.rental-logs'), false);
    }

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

    public function test_lister_can_view_rental_logs_grouped_by_item_and_open_item_history(): void
    {
        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();
        $renter = User::factory()->create(['name' => 'Janeth Simbajon']);
        $secondRenter = User::factory()->create(['name' => 'Juan Dela Cruz']);

        $category = Category::query()->firstOrCreate(
            ['slug' => 'formal-wear'],
            [
                'name' => 'Formal Wear',
                'icon' => 'shirt',
                'is_active' => true,
            ]
        );

        $blazer = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Black Open-Front Blazer',
            'description' => 'Formal blazer',
            'condition' => 'Good',
            'price' => 150,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        $suit = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Light Gray Suit',
            'description' => 'Formal suit',
            'condition' => 'Good',
            'price' => 200,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        $otherOwnerItem = Item::query()->create([
            'user_id' => $otherOwner->id,
            'name' => 'Other Owner Gown',
            'description' => 'Should not appear',
            'condition' => 'Good',
            'price' => 300,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        Rental::query()->create([
            'item_id' => $blazer->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'total_price' => 150,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_APPROVED,
        ]);

        Rental::query()->create([
            'item_id' => $blazer->id,
            'renter_id' => $secondRenter->id,
            'start_date' => now()->addDays(3),
            'end_date' => now()->addDays(4),
            'total_price' => 150,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_CANCELLED,
        ]);

        Rental::query()->create([
            'item_id' => $suit->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(6),
            'total_price' => 200,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_PENDING,
        ]);

        Rental::query()->create([
            'item_id' => $otherOwnerItem->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'total_price' => 300,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_PENDING,
        ]);

        Livewire::actingAs($owner)
            ->test(ListerRentalLogs::class)
            ->assertSee('Rental Logs')
            ->assertSee('Black Open-Front Blazer')
            ->assertSee('Light Gray Suit')
            ->assertSeeInOrder(['Black Open-Front Blazer', 'Light Gray Suit'])
            ->assertSee('Formal Wear')
            ->assertSee('View History')
            ->assertSee(route('rental-requests.item', $blazer), false)
            ->assertSee(route('rental-requests.item', $suit), false)
            ->assertDontSee('Other Owner Gown')
            ->set('search', 'Gray')
            ->assertSee('Light Gray Suit')
            ->assertDontSee('Black Open-Front Blazer');
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
