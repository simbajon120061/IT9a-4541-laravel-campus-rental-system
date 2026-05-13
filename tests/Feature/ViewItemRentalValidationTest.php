<?php

namespace Tests\Feature;

use App\Livewire\ViewItem;
use App\Models\Category;
use App\Models\Item;
use App\Models\Rental;
use App\Models\User;
use App\Notifications\RentalRequestedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ViewItemRentalValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_rental_rejects_past_start_date(): void
    {
        [, $renter, $item] = $this->createItemScenario();

        $this->actingAs($renter);

        Livewire::test(ViewItem::class, ['id' => $item->id])
            ->set('startDate', now()->subDay()->toDateString())
            ->set('endDate', now()->addDay()->toDateString())
            ->call('requestRental')
            ->assertHasErrors(['startDate' => ['after_or_equal']]);
    }

    public function test_request_rental_rejects_end_date_before_start_date(): void
    {
        [, $renter, $item] = $this->createItemScenario();

        $this->actingAs($renter);

        Livewire::test(ViewItem::class, ['id' => $item->id])
            ->set('startDate', now()->addDays(3)->toDateString())
            ->set('endDate', now()->addDay()->toDateString())
            ->call('requestRental')
            ->assertHasErrors(['endDate' => ['after_or_equal']]);
    }

    public function test_request_rental_rejects_rental_period_longer_than_six_months(): void
    {
        [, $renter, $item] = $this->createItemScenario();

        $this->actingAs($renter);

        Livewire::test(ViewItem::class, ['id' => $item->id])
            ->set('startDate', now()->addDay()->toDateString())
            ->set('endDate', now()->addDay()->addMonthsNoOverflow(6)->addDay()->toDateString())
            ->call('requestRental')
            ->assertHasErrors(['endDate' => ['before_or_equal']])
            ->assertSee('Rentals can only be requested for up to 6 months.');
    }

    public function test_request_rental_date_inputs_disable_past_dates_and_limit_end_date(): void
    {
        [, $renter, $item] = $this->createItemScenario();
        $startDate = now()->addDays(2)->toDateString();
        $maximumEndDate = now()->addDays(2)->addMonthsNoOverflow(6)->toDateString();

        $this->actingAs($renter);

        Livewire::test(ViewItem::class, ['id' => $item->id])
            ->assertSee('min="'.now()->toDateString().'"', false)
            ->set('startDate', $startDate)
            ->assertSee('min="'.$startDate.'"', false)
            ->assertSee('max="'.$maximumEndDate.'"', false);
    }

    public function test_request_rental_shows_floating_confirmation_after_success(): void
    {
        Notification::fake();

        [, $renter, $item] = $this->createItemScenario();

        $this->actingAs($renter);

        Livewire::test(ViewItem::class, ['id' => $item->id])
            ->set('startDate', now()->addDay()->toDateString())
            ->set('endDate', now()->addDays(3)->toDateString())
            ->call('requestRental')
            ->assertSee('Rental request sent successfully!')
            ->assertSee('left-1/2', false)
            ->assertSee('shadow-2xl', false);

        Notification::assertSentTo($item->user, RentalRequestedNotification::class);
    }

    public function test_item_page_does_not_show_separate_owner_message_box(): void
    {
        [, $renter, $item] = $this->createItemScenario();

        $this->actingAs($renter);

        Livewire::test(ViewItem::class, ['id' => $item->id])
            ->assertDontSee('Message Owner')
            ->assertDontSee('Ask about this item...');
    }

    public function test_item_page_shows_available_owner_information(): void
    {
        [$owner, $renter, $item] = $this->createItemScenario();
        $owner->update([
            'phone_number' => '09123456789',
            'secondary_phone_number' => '09987654321',
            'student_id' => 'STU-2026-001',
            'department' => 'Computing Education',
            'course' => 'BS Information Technology',
            'year_level' => '3rd Year',
            'bio' => 'Keeps rentals clean and ready for campus pickup.',
            'is_verified_student' => true,
        ]);

        $this->actingAs($renter);

        Livewire::test(ViewItem::class, ['id' => $item->id])
            ->assertSee($owner->email)
            ->assertSee('09123456789')
            ->assertSee('09987654321')
            ->assertSee('STU-2026-001')
            ->assertSee('Computing Education')
            ->assertSee('BS Information Technology')
            ->assertSee('3rd Year')
            ->assertSee('Keeps rentals clean and ready for campus pickup.')
            ->assertSee('Verified student');
    }

    public function test_item_detail_page_includes_dark_mode_styles(): void
    {
        [, $renter, $item] = $this->createItemScenario();

        $this->actingAs($renter);

        Livewire::test(ViewItem::class, ['id' => $item->id])
            ->assertSee('dark:from-slate-950 dark:to-slate-900', false)
            ->assertSee('dark:border-slate-800 dark:bg-slate-900', false)
            ->assertSee('dark:bg-slate-950/40', false)
            ->assertSee('dark:border-blue-900/60 dark:bg-blue-950/30', false)
            ->assertSee('dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100', false)
            ->assertSee('dark:text-slate-300', false)
            ->assertSee('dark:bg-rose-950/20', false);
    }

    public function test_request_rental_message_is_saved_to_conversation(): void
    {
        [$owner, $renter, $item] = $this->createItemScenario();

        Notification::fake();
        $this->actingAs($renter);

        Livewire::test(ViewItem::class, ['id' => $item->id])
            ->set('startDate', now()->addDay()->toDateString())
            ->set('endDate', now()->addDays(3)->toDateString())
            ->set('rentalMessage', 'Can I pick this up at noon?')
            ->call('requestRental')
            ->assertSee('Rental request sent successfully!')
            ->assertSee('Message sent with your rental request.');

        $rental = Rental::query()->where('item_id', $item->id)
            ->where('renter_id', $renter->id)
            ->firstOrFail();

        $this->assertDatabaseHas('rental_messages', [
            'rental_id' => $rental->id,
            'sender_id' => $renter->id,
            'body' => 'Can I pick this up at noon?',
        ]);

        Notification::assertSentTo(
            $owner,
            RentalRequestedNotification::class,
            fn (RentalRequestedNotification $notification): bool => $notification->rentalId === $rental->id
                && $notification->additionalNotes === 'Can I pick this up at noon?'
        );
    }

    public function test_restricted_owner_listing_cannot_be_opened_by_other_users(): void
    {
        [$owner, $renter, $item] = $this->createItemScenario();
        $owner->forceFill(['restricted_at' => now()])->save();

        $this->actingAs($renter)
            ->get(route('item.view', $item->id))
            ->assertNotFound();
    }

    /**
     * @return array{0: User, 1: User, 2: Item}
     */
    private function createItemScenario(): array
    {
        $owner = User::factory()->create();
        $renter = User::factory()->create();

        $category = Category::query()->create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'icon' => 'chip',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Portable Speaker',
            'description' => 'Bluetooth speaker',
            'price' => 50,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        return [$owner, $renter, $item];
    }
}
