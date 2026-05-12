<?php

namespace Tests\Feature;

use App\Livewire\MyRentals;
use App\Models\Category;
use App\Models\Item;
use App\Models\Rental;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MyRentalsFilterVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_stay_visible_when_selected_filter_has_no_results(): void
    {
        $renter = User::factory()->create();
        $owner = User::factory()->create();

        $category = Category::query()->firstOrCreate(
            ['slug' => 'books'],
            ['name' => 'Books', 'icon' => 'book', 'is_active' => true]
        );

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Sample Item',
            'description' => 'Sample Description',
            'price' => 50,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now(),
            'end_date' => now()->addDays(2),
            'total_price' => 100,
            'status' => 'pending',
        ]);

        $this->actingAs($renter);

        Livewire::test(MyRentals::class)
            ->call('setFilter', 'due_soon')
            ->assertSee('All Rentals')
            ->assertSee('Pending Request')
            ->assertSee('Approved Request')
            ->assertSee('Due Soon')
            ->assertSee('Active Loan')
            ->assertSee('No rentals for this filter');
    }

    public function test_days_left_displays_whole_days_for_partial_day_difference(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-25 08:00:00'));

        $renter = User::factory()->create();
        $owner = User::factory()->create();

        $category = Category::query()->firstOrCreate(
            ['slug' => 'books'],
            ['name' => 'Books', 'icon' => 'book', 'is_active' => true]
        );

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Sample Item',
            'description' => 'Sample Description',
            'price' => 50,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(3)->subHours(4),
            'total_price' => 100,
            'status' => 'active',
        ]);

        $this->actingAs($renter);

        Livewire::test(MyRentals::class)
            ->assertSee('3 days');

        Carbon::setTestNow();
    }

    public function test_future_start_active_rental_is_shown_as_on_process_and_not_due_soon(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-25 08:00:00'));

        $renter = User::factory()->create();
        $owner = User::factory()->create();

        $category = Category::query()->firstOrCreate(
            ['slug' => 'books'],
            ['name' => 'Books', 'icon' => 'book', 'is_active' => true]
        );

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Sample Item',
            'description' => 'Sample Description',
            'price' => 50,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDays(3),
            'end_date' => now()->addDays(5),
            'total_price' => 100,
            'status' => 'active',
        ]);

        $this->actingAs($renter);

        Livewire::test(MyRentals::class)
            ->assertSee('On Process')
            ->assertDontSee('1 rental(s) due soon!');

        Carbon::setTestNow();
    }

    public function test_pending_and_approved_filters_are_separated(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-25 08:00:00'));

        $renter = User::factory()->create();
        $owner = User::factory()->create();

        $category = Category::query()->firstOrCreate(
            ['slug' => 'books'],
            ['name' => 'Books', 'icon' => 'book', 'is_active' => true]
        );

        $pendingItem = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Pending Rental Item',
            'description' => 'Sample Description',
            'price' => 50,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        $approvedItem = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Approved Rental Item',
            'description' => 'Sample Description',
            'price' => 50,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        Rental::query()->create([
            'item_id' => $pendingItem->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(5),
            'total_price' => 100,
            'status' => 'pending',
        ]);

        Rental::query()->create([
            'item_id' => $approvedItem->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDays(3),
            'end_date' => now()->addDays(6),
            'total_price' => 120,
            'status' => 'approved',
        ]);

        $this->actingAs($renter);

        Livewire::test(MyRentals::class)
            ->call('setFilter', 'pending')
            ->assertSee('Pending Rental Item')
            ->assertDontSee('Approved Rental Item')
            ->call('setFilter', 'approved')
            ->assertSee('Approved Rental Item')
            ->assertDontSee('Pending Rental Item');

        Carbon::setTestNow();
    }

    public function test_search_filters_by_item_name_or_owner_name(): void
    {
        $renter = User::factory()->create();
        $targetOwner = User::factory()->create(['name' => 'Maria Santos']);
        $otherOwner = User::factory()->create(['name' => 'John Cruz']);

        $category = Category::query()->firstOrCreate(
            ['slug' => 'books'],
            ['name' => 'Books', 'icon' => 'book', 'is_active' => true]
        );

        $matchingItem = Item::query()->create([
            'user_id' => $targetOwner->id,
            'name' => 'Biology Reviewer',
            'description' => 'Sample Description',
            'price' => 50,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        $otherItem = Item::query()->create([
            'user_id' => $otherOwner->id,
            'name' => 'Physics Kit',
            'description' => 'Sample Description',
            'price' => 60,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        Rental::query()->create([
            'item_id' => $matchingItem->id,
            'renter_id' => $renter->id,
            'start_date' => now(),
            'end_date' => now()->addDays(2),
            'total_price' => 100,
            'status' => 'active',
        ]);

        Rental::query()->create([
            'item_id' => $otherItem->id,
            'renter_id' => $renter->id,
            'start_date' => now(),
            'end_date' => now()->addDays(3),
            'total_price' => 120,
            'status' => 'active',
        ]);

        $this->actingAs($renter);

        Livewire::test(MyRentals::class)
            ->set('search', 'biology')
            ->assertSee('Biology Reviewer')
            ->assertDontSee('Physics Kit')
            ->set('search', 'maria')
            ->assertSee('Maria Santos')
            ->assertDontSee('John Cruz');
    }

    public function test_my_rentals_table_shows_payment_status_and_balance(): void
    {
        $renter = User::factory()->create();
        $owner = User::factory()->create();

        $category = Category::query()->firstOrCreate(
            ['slug' => 'electronics-payment-status'],
            ['name' => 'Electronics', 'icon' => 'chip', 'is_active' => true]
        );

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Nikon DSLR Camera',
            'description' => 'Camera rental',
            'price' => 200,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(4),
            'total_price' => 600,
            'paid_amount' => 200,
            'payment_status' => Rental::PAYMENT_STATUS_PARTIAL,
            'status' => Rental::STATUS_APPROVED,
        ]);

        $this->actingAs($renter);

        Livewire::test(MyRentals::class)
            ->assertSee('Payment Status')
            ->assertSee('Partial')
            ->assertSee('Balance:')
            ->assertSee('400.00')
            ->assertSee('200.00')
            ->assertSee('600.00');
    }
}
