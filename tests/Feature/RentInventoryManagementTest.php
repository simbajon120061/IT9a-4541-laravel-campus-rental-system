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
            ->assertSee('Starts '.$rental->start_date->format('M d, Y'));
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
