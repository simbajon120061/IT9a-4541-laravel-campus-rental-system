<?php

namespace Tests\Feature;

use App\Livewire\OwnerRentalRequestView;
use App\Models\Category;
use App\Models\Item;
use App\Models\Rental;
use App\Models\User;
use App\Notifications\RentalRequestDecisionNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class OwnerRentalRequestViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_request_details_and_grant_request(): void
    {
        Notification::fake();

        [$owner, $borrower, $rental] = $this->createRentalRequest();
        $this->actingAs($owner);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental])
            ->assertSee('View Request')
            ->assertSee($borrower->name)
            ->assertSee($borrower->email)
            ->call('grantRequest')
            ->assertSee('Rental request granted.');

        $rental->refresh();

        $this->assertSame('approved', $rental->status);

        Notification::assertSentTo(
            [$borrower],
            RentalRequestDecisionNotification::class,
            function (RentalRequestDecisionNotification $notification) use ($rental): bool {
                return $notification->rentalId === $rental->id
                    && $notification->decision === 'approved';
            }
        );
    }

    public function test_owner_can_reject_request(): void
    {
        Notification::fake();

        [$owner, $borrower, $rental] = $this->createRentalRequest();
        $this->actingAs($owner);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental])
            ->call('rejectRequest')
            ->assertSee('Rental request rejected.');

        $rental->refresh();

        $this->assertSame('cancelled', $rental->status);

        Notification::assertSentTo(
            [$borrower],
            RentalRequestDecisionNotification::class,
            function (RentalRequestDecisionNotification $notification) use ($rental): bool {
                return $notification->rentalId === $rental->id
                    && $notification->decision === 'rejected';
            }
        );
    }

    public function test_renter_can_open_request_view_in_read_only_mode(): void
    {
        [$owner, $borrower, $rental] = $this->createRentalRequest();
        $this->actingAs($borrower);

        $this->get(route('rental-requests.show', $rental))
            ->assertOk()
            ->assertSee('Only the item owner can approve or reject this request.')
            ->assertDontSee('Edit Dates');
    }

    public function test_renter_opening_request_thread_keeps_renter_portal_navigation(): void
    {
        [, $borrower, $rental] = $this->createRentalRequest();
        $this->actingAs($borrower);

        $this->get(route('rental-requests.show', ['rental' => $rental, 'portal' => 'renter']))
            ->assertOk()
            ->assertSee('Renter Dashboard')
            ->assertSee('My Rentals')
            ->assertDontSee('Lister Dashboard');
    }

    public function test_owner_can_update_pending_request_dates_and_total_amount(): void
    {
        [$owner, , $rental] = $this->createRentalRequest();
        $this->actingAs($owner);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental])
            ->call('editSchedule')
            ->assertSet('isEditingSchedule', true)
            ->set('editableStartDate', now()->addDays(2)->toDateString())
            ->set('editableEndDate', now()->addDays(5)->toDateString())
            ->call('updateSchedule')
            ->assertSet('isEditingSchedule', false)
            ->assertSee('Rental dates updated.');

        $rental->refresh();

        $this->assertSame(now()->addDays(2)->toDateString(), $rental->start_date->toDateString());
        $this->assertSame(now()->addDays(5)->toDateString(), $rental->end_date->toDateString());
        $this->assertSame('300.00', (string) $rental->total_price);
    }

    public function test_owner_cannot_set_end_date_before_start_date(): void
    {
        [$owner, , $rental] = $this->createRentalRequest();
        $this->actingAs($owner);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental])
            ->call('editSchedule')
            ->set('editableStartDate', now()->addDays(4)->toDateString())
            ->set('editableEndDate', now()->addDays(3)->toDateString())
            ->call('updateSchedule')
            ->assertHasErrors(['editableEndDate' => ['after']]);
    }

    public function test_request_view_shows_the_rented_item_image(): void
    {
        Storage::fake('public');

        [, $borrower, $rental] = $this->createRentalRequest();
        Storage::disk('public')->put('item-photos/projector.jpg', 'image-content');
        $rental->item->update(['image_path' => 'item-photos/projector.jpg']);

        $this->actingAs($borrower);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental])
            ->assertSee('Portable Projector')
            ->assertSee('/storage/item-photos/projector.jpg', false)
            ->assertSee('Portable Projector rental item image');
    }

    public function test_due_tomorrow_alert_is_visible_for_active_rental_with_one_day_left(): void
    {
        [$owner, $borrower, $rental] = $this->createRentalRequest();
        $this->actingAs($owner);

        $rental->update([
            'status' => 'active',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental->fresh()])
            ->assertSee('Due soon:')
            ->assertSee('This rental is due in 1 day.')
            ->assertSee('Due Soon')
            ->assertSee('1 day(s) left')
            ->assertDontSee('>Active Loan</span>', false);
    }

    public function test_request_view_status_matches_inventory_for_due_now_active_rental(): void
    {
        [$owner, , $rental] = $this->createRentalRequest();
        $this->actingAs($owner);

        $rental->update([
            'status' => Rental::STATUS_ACTIVE,
            'start_date' => now()->subDays(2),
            'end_date' => now()->subMinute(),
        ]);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental->fresh()])
            ->assertSee('Due Now')
            ->assertSee('0 day(s) left')
            ->assertSee('bg-rose-100', false)
            ->assertDontSee('>Active Loan</span>', false);
    }

    public function test_request_view_status_matches_inventory_for_future_active_rental(): void
    {
        [$owner, , $rental] = $this->createRentalRequest();
        $this->actingAs($owner);

        $rental->update([
            'status' => Rental::STATUS_ACTIVE,
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(4),
        ]);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental->fresh()])
            ->assertSee('On Process')
            ->assertSee('Starts '.$rental->fresh()->start_date->format('M d, Y'))
            ->assertDontSee('>Active Loan</span>', false);
    }

    public function test_fully_paid_owner_request_view_replaces_add_payment_with_disabled_paid_button(): void
    {
        [$owner, , $rental] = $this->createRentalRequest();
        $this->actingAs($owner);

        $rental->update([
            'paid_amount' => $rental->total_price,
            'payment_status' => Rental::PAYMENT_STATUS_FULLY_PAID,
            'status' => Rental::STATUS_ACTIVE,
        ]);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental->fresh()])
            ->assertSee('Open Chat')
            ->assertSee('Paid')
            ->assertSee('disabled', false)
            ->assertSee('bg-emerald-600', false)
            ->assertDontSee('Add Payment')
            ->assertDontSee(route('lister.payments', ['filter' => Rental::PAYMENT_STATUS_FULLY_PAID]).'#payment-'.$rental->id, false);
    }

    public function test_cancelled_owner_request_view_hides_add_payment_button(): void
    {
        [$owner, , $rental] = $this->createRentalRequest();
        $this->actingAs($owner);

        $rental->update([
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental->fresh()])
            ->assertSee('Open Chat')
            ->assertSee('Rejected')
            ->assertDontSee('Add Payment')
            ->assertDontSee(route('lister.payments', ['filter' => Rental::PAYMENT_STATUS_OUTSTANDING]).'#payment-'.$rental->id, false);
    }

    public function test_owner_records_partial_payment_from_request_view_modal_and_redirects_to_payment_row(): void
    {
        [$owner, , $rental] = $this->createRentalRequest();
        $this->actingAs($owner);

        $rental->update([
            'status' => Rental::STATUS_APPROVED,
            'total_price' => 300,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
        ]);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental->fresh()])
            ->assertSee('Add Payment')
            ->call('openAddPaymentModal')
            ->assertSet('showPaymentModal', true)
            ->assertSee('Save Payment')
            ->set('paymentAmount', '75')
            ->call('savePayment')
            ->assertRedirect(route('lister.payments', ['filter' => Rental::PAYMENT_STATUS_PARTIAL]).'#payment-'.$rental->id);

        $rental->refresh();

        $this->assertSame(Rental::PAYMENT_STATUS_PARTIAL, $rental->payment_status);
        $this->assertSame(75.0, (float) $rental->paid_amount);
    }

    public function test_owner_can_pay_in_full_from_request_view_modal(): void
    {
        [$owner, , $rental] = $this->createRentalRequest();
        $this->actingAs($owner);

        $rental->update([
            'status' => Rental::STATUS_APPROVED,
            'total_price' => 300,
            'paid_amount' => 100,
            'payment_status' => Rental::PAYMENT_STATUS_PARTIAL,
        ]);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental->fresh()])
            ->call('openAddPaymentModal')
            ->call('fillFullPaymentAmount')
            ->assertSet('paymentAmount', '200.00')
            ->call('savePayment')
            ->assertRedirect(route('lister.payments', ['filter' => Rental::PAYMENT_STATUS_FULLY_PAID]).'#payment-'.$rental->id);

        $rental->refresh();

        $this->assertSame(Rental::PAYMENT_STATUS_FULLY_PAID, $rental->payment_status);
        $this->assertSame(300.0, (float) $rental->paid_amount);
    }

    /**
     * @return array{0: User, 1: User, 2: Rental}
     */
    private function createRentalRequest(): array
    {
        $owner = User::factory()->create();
        $borrower = User::factory()->create([
            'phone_number' => '09912345678',
            'secondary_phone_number' => '09876543210',
            'course' => 'Computing Education',
            'year_level' => '3rd Year',
        ]);

        $category = Category::query()->create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'icon' => 'chip',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Portable Projector',
            'description' => 'Compact projector',
            'price' => 100,
            'status' => 'available',
            'category' => 'electronics',
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $borrower->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(4),
            'total_price' => 300,
            'paid_amount' => 0,
            'payment_status' => 'outstanding',
            'status' => 'pending',
        ]);

        return [$owner, $borrower, $rental];
    }
}
