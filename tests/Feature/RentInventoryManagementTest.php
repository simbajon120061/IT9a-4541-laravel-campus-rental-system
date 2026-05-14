<?php

namespace Tests\Feature;

use App\Livewire\RentInventoryManagement;
use App\Models\Category;
use App\Models\Item;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class RentInventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_approve_pending_request_and_record_payment(): void
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'available',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 100,
            'payment_status' => 'outstanding',
            'status' => 'pending',
        ]);

        $this->actingAs($owner);

        Livewire::test(RentInventoryManagement::class)
            ->assertSee('Rent Inventory Management')
            ->assertSee('Pending Request')
            ->call('approveRequest', $rental->id)
            ->assertSee('Rental request approved.')
            ->set("paymentAmounts.{$rental->id}", '40')
            ->call('recordPayment', $rental->id)
            ->assertSee('Payment recorded successfully.');

        $rental->refresh();
        $item->refresh();

        $this->assertSame('partial', $rental->payment_status);
        $this->assertSame('approved', $rental->status);
        $this->assertSame('available', $item->status);
        $this->assertSame(40.0, (float) $rental->paid_amount);
    }

    public function test_owner_can_fill_remaining_payment_amount(): void
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'available',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 100,
            'paid_amount' => 40,
            'payment_status' => 'partial',
            'status' => 'approved',
        ]);

        $this->actingAs($owner);

        Livewire::test(RentInventoryManagement::class)
            ->call('fillFullPaymentAmount', $rental->id)
            ->assertSet("paymentAmounts.{$rental->id}", '60.00');
    }

    public function test_owner_cannot_record_payment_above_remaining_balance(): void
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'available',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 100,
            'paid_amount' => 40,
            'payment_status' => 'partial',
            'status' => 'approved',
        ]);

        $this->actingAs($owner);

        Livewire::test(RentInventoryManagement::class)
            ->set("paymentAmounts.{$rental->id}", '70')
            ->call('recordPayment', $rental->id)
            ->assertHasErrors(["paymentAmounts.{$rental->id}" => 'max']);

        $rental->refresh();

        $this->assertSame('partial', $rental->payment_status);
        $this->assertSame(40.0, (float) $rental->paid_amount);
    }

    public function test_item_image_is_shown_in_rent_inventory_table(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('item-photos/book.jpg', 'fake image contents');

        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'available',
            'category' => 'books',
            'category_id' => $category->id,
            'image_path' => 'item-photos/book.jpg',
        ]);

        Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 100,
            'paid_amount' => 0,
            'payment_status' => 'outstanding',
            'status' => 'approved',
        ]);

        $this->actingAs($owner);

        Livewire::test(RentInventoryManagement::class)
            ->assertSee('/storage/item-photos/book.jpg', false)
            ->assertSee('Linear Algebra Book');
    }

    public function test_on_process_actions_only_show_view_request_and_mark_as_rented(): void
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'available',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 100,
            'paid_amount' => 40,
            'payment_status' => Rental::PAYMENT_STATUS_PARTIAL,
            'status' => Rental::STATUS_APPROVED,
        ]);

        $this->actingAs($owner);

        Livewire::test(RentInventoryManagement::class)
            ->assertSee('On Process')
            ->assertSee('View Request')
            ->assertSee('Mark as Rented')
            ->assertSee(route('rental-requests.show', $rental), false)
            ->assertDontSee(route('lister.messages', ['rental' => $rental->id]), false)
            ->assertDontSee('Message')
            ->assertDontSee('Delete')
            ->call('markAsRented', $rental->id)
            ->assertSee('Rental status updated to Rented.');

        $rental->refresh();
        $item->refresh();

        $this->assertSame(Rental::STATUS_ACTIVE, $rental->status);
        $this->assertSame('rented', $item->status);
    }

    public function test_owner_can_mark_approved_rental_as_rented(): void
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'available',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 100,
            'paid_amount' => 100,
            'payment_status' => 'fully_paid',
            'status' => 'approved',
        ]);

        $this->actingAs($owner);

        Livewire::test(RentInventoryManagement::class)
            ->assertSee('Mark as Rented')
            ->call('markAsRented', $rental->id)
            ->assertSee('Rental status updated to Rented.');

        $rental->refresh();
        $item->refresh();

        $this->assertSame('active', $rental->status);
        $this->assertSame('rented', $item->status);
    }

    public function test_future_start_active_rental_is_shown_as_on_process(): void
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'available',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(4),
            'total_price' => 100,
            'paid_amount' => 100,
            'payment_status' => 'fully_paid',
            'status' => 'active',
        ]);

        $this->actingAs($owner);

        Livewire::test(RentInventoryManagement::class)
            ->assertSee('Approved Request')
            ->assertSee('On Process')
            ->assertSee('Starts '.$rental->start_date->format('M d, Y'))
            ->assertSee(route('rental-requests.show', $rental), false)
            ->assertSee('Mark as Rented')
            ->assertDontSee(route('lister.messages', ['rental' => $rental->id]), false)
            ->assertDontSee('Message')
            ->assertDontSee('Delete');
    }

    public function test_due_soon_rental_can_be_marked_as_returned(): void
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'rented',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'total_price' => 100,
            'paid_amount' => 100,
            'payment_status' => Rental::PAYMENT_STATUS_FULLY_PAID,
            'status' => Rental::STATUS_ACTIVE,
        ]);

        $this->actingAs($owner);

        Livewire::test(RentInventoryManagement::class)
            ->assertSee('Due Soon')
            ->assertSee('1 day(s) left')
            ->assertSee(route('rental-requests.show', $rental), false)
            ->assertSee('Mark as returned')
            ->assertSee(route('lister.messages', ['rental' => $rental->id]), false)
            ->call('markAsReturned', $rental->id)
            ->assertSee('Rental status updated to Returned.')
            ->assertSee('Returned')
            ->assertSee('Delete');

        $rental->refresh();
        $item->refresh();

        $this->assertSame(Rental::STATUS_COMPLETED, $rental->status);
        $this->assertNotNull($rental->completed_at);
        $this->assertSame('available', $item->status);
    }

    public function test_active_rental_with_zero_days_left_is_due_now_with_return_action(): void
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'rented',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->subDays(2),
            'end_date' => now()->subMinute(),
            'total_price' => 100,
            'paid_amount' => 100,
            'payment_status' => Rental::PAYMENT_STATUS_FULLY_PAID,
            'status' => Rental::STATUS_ACTIVE,
        ]);

        $this->actingAs($owner);

        Livewire::test(RentInventoryManagement::class)
            ->assertSee('Due Now')
            ->assertSee('0 day(s) left')
            ->assertSee('bg-rose-100', false)
            ->assertSee(route('rental-requests.show', $rental), false)
            ->assertSee(route('lister.messages', ['rental' => $rental->id]), false)
            ->assertSee('Mark as returned')
            ->assertDontSee('>Active Loan</span>', false)
            ->call('markAsReturned', $rental->id)
            ->assertSee('Rental status updated to Returned.')
            ->assertSee('Returned')
            ->assertSee('Delete');

        $rental->refresh();
        $item->refresh();

        $this->assertSame(Rental::STATUS_COMPLETED, $rental->status);
        $this->assertSame('available', $item->status);
    }

    public function test_overdue_rental_can_be_marked_as_returned(): void
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'rented',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->subDays(4),
            'end_date' => now()->subDay(),
            'total_price' => 100,
            'paid_amount' => 100,
            'payment_status' => Rental::PAYMENT_STATUS_FULLY_PAID,
            'status' => Rental::STATUS_ACTIVE,
        ]);

        $this->actingAs($owner);

        Livewire::test(RentInventoryManagement::class)
            ->assertSee('Overdue')
            ->assertSee('Mark as returned')
            ->call('markAsReturned', $rental->id)
            ->assertSee('Returned')
            ->assertSee('Delete')
            ->assertDontSee('Mark as returned');

        $rental->refresh();
        $item->refresh();

        $this->assertSame(Rental::STATUS_COMPLETED, $rental->status);
        $this->assertSame('available', $item->status);
    }

    public function test_cancelled_rental_shows_delete_action_in_inventory(): void
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'available',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->subDays(2),
            'end_date' => now()->subDay(),
            'total_price' => 100,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        $this->actingAs($owner);

        Livewire::test(RentInventoryManagement::class)
            ->assertSee('Cancelled')
            ->assertSee(route('rental-requests.show', $rental), false)
            ->assertSee('Delete')
            ->assertDontSee('Mark as returned')
            ->call('confirmDeleteRental', $rental->id)
            ->assertSet('showDeleteModal', true)
            ->assertSee('Confirm Delete');
    }

    public function test_inventory_can_open_directly_to_approved_filter(): void
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $approvedItem = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'available',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        $pendingItem = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Physics Reviewer',
            'description' => 'Printed notes',
            'price' => 40,
            'status' => 'available',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        Rental::query()->create([
            'item_id' => $approvedItem->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 100,
            'paid_amount' => 0,
            'payment_status' => 'outstanding',
            'status' => 'approved',
        ]);

        Rental::query()->create([
            'item_id' => $pendingItem->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 80,
            'paid_amount' => 0,
            'payment_status' => 'outstanding',
            'status' => 'pending',
        ]);

        $this->actingAs($owner);

        Livewire::withQueryParams(['filter' => 'approved'])
            ->test(RentInventoryManagement::class)
            ->assertSet('filterStatus', 'approved')
            ->assertSee('Linear Algebra Book')
            ->assertDontSee('Physics Reviewer');
    }

    public function test_on_process_notice_links_to_payments_without_inline_payment_controls(): void
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'available',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 100,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_APPROVED,
        ]);

        $this->actingAs($owner);

        Livewire::test(RentInventoryManagement::class)
            ->assertSee(route('lister.payments', ['filter' => 'outstanding']), false)
            ->assertDontSee('Pay in Full')
            ->assertDontSee('wire:click="recordPayment', false)
            ->assertDontSee('placeholder="Amount"', false);
    }

    public function test_fully_paid_active_due_soon_rental_replaces_on_process_card_with_record_link(): void
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'rented',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(2),
            'total_price' => 100,
            'paid_amount' => 100,
            'payment_status' => Rental::PAYMENT_STATUS_FULLY_PAID,
            'status' => Rental::STATUS_ACTIVE,
        ]);

        $this->actingAs($owner);

        Livewire::test(RentInventoryManagement::class)
            ->assertSee('Due Soon Alert')
            ->assertSee(route('lister.inventory', ['filter' => 'due_soon']).'#rental-'.$rental->id, false)
            ->assertSee('id="rental-'.$rental->id.'"', false)
            ->assertDontSee('1 active loan(s) are nearing return date.')
            ->assertDontSee('approved request(s) are waiting for payment confirmation.');
    }

    public function test_on_process_card_mentions_partial_payments_before_rental_handoff(): void
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'available',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 100,
            'paid_amount' => 40,
            'payment_status' => Rental::PAYMENT_STATUS_PARTIAL,
            'status' => Rental::STATUS_APPROVED,
        ]);

        $this->actingAs($owner);

        Livewire::test(RentInventoryManagement::class)
            ->assertSee('On Process')
            ->assertSee('request(s) are approved but not rented yet.')
            ->assertSee('have partial payment recorded.');
    }

    public function test_owner_can_search_rentals_using_search_button(): void
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create();
        $category = Category::query()->firstOrCreate([
            'slug' => 'books',
        ], [
            'name' => 'Books',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $matchingItem = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Linear Algebra Book',
            'description' => 'Hardbound copy',
            'price' => 50,
            'status' => 'available',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        $otherItem = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Physics Reviewer',
            'description' => 'Printed notes',
            'price' => 40,
            'status' => 'available',
            'category' => 'books',
            'category_id' => $category->id,
        ]);

        Rental::query()->create([
            'item_id' => $matchingItem->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 100,
            'paid_amount' => 0,
            'payment_status' => 'outstanding',
            'status' => 'pending',
        ]);

        Rental::query()->create([
            'item_id' => $otherItem->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 80,
            'paid_amount' => 0,
            'payment_status' => 'outstanding',
            'status' => 'pending',
        ]);

        $this->actingAs($owner);

        Livewire::test(RentInventoryManagement::class)
            ->set('search', 'Linear')
            ->call('applySearch')
            ->assertSee('Linear Algebra Book')
            ->assertDontSee('Physics Reviewer');
    }
}
