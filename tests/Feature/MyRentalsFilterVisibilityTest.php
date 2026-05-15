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

    public function test_my_rentals_sets_renter_portal_context(): void
    {
        $renter = User::factory()->create();

        $this
            ->actingAs($renter)
            ->withSession(['active_portal' => 'lister'])
            ->get(route('renter.my-rentals'))
            ->assertOk()
            ->assertSessionHas('active_portal', 'renter');
    }

    public function test_my_rentals_shows_rental_history_for_soft_deleted_items(): void
    {
        $renter = User::factory()->create();
        $owner = User::factory()->create(['name' => 'Listing Owner']);

        $category = Category::query()->firstOrCreate(
            ['slug' => 'archived-items'],
            ['name' => 'Archived Items', 'icon' => 'archive', 'is_active' => true]
        );

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Archived Projector',
            'description' => 'Projector kept for rental history',
            'price' => 75,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDay(),
            'total_price' => 150,
            'status' => Rental::STATUS_ACTIVE,
        ]);

        $item->delete();

        $this->actingAs($renter);

        Livewire::test(MyRentals::class)
            ->assertSee('Archived Projector')
            ->assertSee('Listing Owner')
            ->assertSee('Archived Items');
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

    public function test_cancelled_rental_days_left_displays_cancelled_not_overdue(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 08:00:00'));

        $renter = User::factory()->create();
        $owner = User::factory()->create();

        $category = Category::query()->firstOrCreate(
            ['slug' => 'formal-wear'],
            ['name' => 'Formal Wear', 'icon' => 'shirt', 'is_active' => true]
        );

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Light Gray Suit',
            'description' => 'Formal wear',
            'price' => 200,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->subDays(2),
            'end_date' => now()->subDay(),
            'total_price' => 200,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        $this->actingAs($renter);

        Livewire::test(MyRentals::class)
            ->assertSee('Light Gray Suit')
            ->assertSee('Cancelled')
            ->assertDontSee('Overdue')
            ->assertDontSee('bg-orange-50', false);

        Carbon::setTestNow();
    }

    public function test_active_rental_ending_today_displays_due_today_not_overdue(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 19:20:00'));

        $renter = User::factory()->create();
        $owner = User::factory()->create();

        $category = Category::query()->firstOrCreate(
            ['slug' => 'formal-wear-due-today'],
            ['name' => 'Formal Wear', 'icon' => 'shirt', 'is_active' => true]
        );

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Light Gray Suit',
            'description' => 'Formal wear',
            'price' => 200,
            'status' => 'rented',
            'category_id' => $category->id,
        ]);

        Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => Carbon::parse('2026-05-13')->startOfDay(),
            'end_date' => Carbon::parse('2026-05-14')->startOfDay(),
            'total_price' => 200,
            'paid_amount' => 200,
            'payment_status' => Rental::PAYMENT_STATUS_FULLY_PAID,
            'status' => Rental::STATUS_ACTIVE,
        ]);

        $this->actingAs($renter);

        Livewire::test(MyRentals::class)
            ->assertSee('Light Gray Suit')
            ->assertSee('Due Today')
            ->assertDontSee('Overdue');

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

    public function test_my_rentals_page_uses_responsive_filter_and_card_layout(): void
    {
        $renter = User::factory()->create();
        $owner = User::factory()->create();

        $category = Category::query()->firstOrCreate(
            ['slug' => 'responsive-rentals'],
            ['name' => 'Responsive Rentals', 'icon' => 'box', 'is_active' => true]
        );

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Light Gray Suit',
            'description' => 'Formal wear',
            'price' => 200,
            'status' => 'rented',
            'category_id' => $category->id,
        ]);

        Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'total_price' => 200,
            'paid_amount' => 100,
            'payment_status' => Rental::PAYMENT_STATUS_PARTIAL,
            'status' => Rental::STATUS_APPROVED,
        ]);

        $this->actingAs($renter);

        Livewire::test(MyRentals::class)
            ->assertSee('grid gap-3 lg:grid-cols-[minmax(16rem,24rem)_minmax(0,1fr)] lg:items-center', false)
            ->assertSee('grid w-full grid-cols-1 gap-2 min-[420px]:grid-cols-2 md:grid-cols-3 xl:grid-cols-5', false)
            ->assertSee('md:grid md:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)] md:gap-x-6 md:gap-y-3 xl:table-row', false)
            ->assertSee('md:col-span-2 xl:table-cell', false)
            ->assertDontSee('overflow-x-auto', false)
            ->assertDontSee('xl:min-w-[72rem]', false);
    }

    public function test_returned_rental_shows_delete_button_and_can_be_deleted_by_renter(): void
    {
        $renter = User::factory()->create();
        $owner = User::factory()->create();

        $category = Category::query()->firstOrCreate(
            ['slug' => 'returned-rentals'],
            ['name' => 'Returned Rentals', 'icon' => 'box', 'is_active' => true]
        );

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Returned Blazer',
            'description' => 'Formal wear',
            'price' => 150,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->subDays(3),
            'end_date' => now()->subDay(),
            'total_price' => 300,
            'paid_amount' => 300,
            'payment_status' => Rental::PAYMENT_STATUS_FULLY_PAID,
            'status' => Rental::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $this->actingAs($renter);

        Livewire::test(MyRentals::class)
            ->assertSee('Returned Blazer')
            ->assertSee('Returned')
            ->assertSee('Delete')
            ->call('deleteReturnedRental', $rental->id)
            ->assertSee('Returned rental deleted successfully.')
            ->assertDontSee('Returned Blazer');

        $this->assertSoftDeleted('rentals', ['id' => $rental->id]);
    }

    public function test_payment_confirmation_query_opens_receipt_modal(): void
    {
        [$renter, $rental] = $this->createPaidRentalForReceipt();

        $this->actingAs($renter);

        Livewire::withQueryParams(['receipt' => $rental->id])
            ->test(MyRentals::class)
            ->assertSet('receiptRentalId', $rental->id)
            ->assertSee('Digital Receipt')
            ->assertSee('Receipt #CR-'.$rental->id)
            ->assertSee('Save/Download')
            ->call('closeReceiptModal')
            ->assertSet('receiptRentalId', null);
    }

    public function test_paid_rental_receipt_can_be_revisited_from_my_rentals(): void
    {
        [$renter, $rental] = $this->createPaidRentalForReceipt();

        $this->actingAs($renter);

        Livewire::test(MyRentals::class)
            ->assertSee('View Receipt')
            ->assertSee(route('renter.my-rentals', ['receipt' => $rental->id]).'#rental-'.$rental->id, false);
    }

    public function test_renter_can_download_payment_receipt(): void
    {
        [$renter, $rental] = $this->createPaidRentalForReceipt();

        $this->actingAs($renter);

        Livewire::withQueryParams(['receipt' => $rental->id])
            ->test(MyRentals::class)
            ->call('downloadReceipt')
            ->assertFileDownloaded('campusrent-payment-receipt-'.$rental->id.'.jpg');
    }

    /**
     * @return array{0: User, 1: Rental}
     */
    private function createPaidRentalForReceipt(): array
    {
        $renter = User::factory()->create();
        $owner = User::factory()->create(['name' => 'Carine Magcantara']);

        $category = Category::query()->firstOrCreate(
            ['slug' => 'receipt-rentals'],
            ['name' => 'Receipt Rentals', 'icon' => 'box', 'is_active' => true]
        );

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Black Open-Front Blazer',
            'description' => 'Formal wear',
            'price' => 150,
            'status' => 'rented',
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'total_price' => 150,
            'paid_amount' => 100,
            'payment_status' => Rental::PAYMENT_STATUS_PARTIAL,
            'status' => Rental::STATUS_ACTIVE,
        ]);

        return [$renter, $rental];
    }
}
