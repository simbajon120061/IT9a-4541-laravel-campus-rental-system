<?php

namespace Tests\Feature;

use App\Livewire\ListerPayments;
use App\Models\Category;
use App\Models\Item;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class ListerPaymentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_records_partial_payment_from_modal(): void
    {
        [$owner, , $rental] = $this->createPaymentScenario();

        Livewire::actingAs($owner)
            ->test(ListerPayments::class)
            ->assertSee('Add Payment')
            ->assertSee('Pay in Full')
            ->assertDontSee('placeholder="Amount"', false)
            ->call('openAddPaymentModal', $rental->id)
            ->assertSet('selectedPaymentId', $rental->id)
            ->assertSet('paymentMode', 'add')
            ->assertSee('Record Payment')
            ->set('paymentAmount', '45')
            ->call('savePayment')
            ->assertSet('selectedPaymentId', null)
            ->assertSee('Payment recorded successfully.');

        $rental->refresh();

        $this->assertSame(Rental::PAYMENT_STATUS_PARTIAL, $rental->payment_status);
        $this->assertSame(45.0, (float) $rental->paid_amount);
    }

    public function test_owner_can_confirm_pay_in_full_or_cancel_modal(): void
    {
        [$owner, , $rental] = $this->createPaymentScenario(paidAmount: 40);

        Livewire::actingAs($owner)
            ->test(ListerPayments::class)
            ->call('openFullPaymentModal', $rental->id)
            ->assertSet('paymentMode', 'full')
            ->assertSet('paymentAmount', '110.00')
            ->call('closePaymentModal')
            ->assertSet('selectedPaymentId', null)
            ->call('openFullPaymentModal', $rental->id)
            ->call('savePayment')
            ->assertSee('Payment recorded successfully.');

        $rental->refresh();

        $this->assertSame(Rental::PAYMENT_STATUS_FULLY_PAID, $rental->payment_status);
        $this->assertSame(150.0, (float) $rental->paid_amount);
    }

    public function test_payment_filter_query_parameter_opens_matching_status(): void
    {
        [$owner] = $this->createPaymentScenario();

        Livewire::actingAs($owner)
            ->withQueryParams(['filter' => Rental::PAYMENT_STATUS_OUTSTANDING])
            ->test(ListerPayments::class)
            ->assertSet('filterStatus', Rental::PAYMENT_STATUS_OUTSTANDING);
    }

    public function test_owner_filters_payments_by_search_status_and_due_date(): void
    {
        [$owner, , $rental] = $this->createPaymentScenario(
            itemName: 'Black Open-Front Blazer',
            paidAmount: 40,
            endDate: now()->addDays(3),
        );
        $this->createPaymentScenario(
            owner: $owner,
            itemName: 'Graphing Calculator',
            renterName: 'Maria Santos',
            paidAmount: 150,
            endDate: now()->addDays(10),
        );

        Livewire::actingAs($owner)
            ->test(ListerPayments::class)
            ->assertSee('Remaining Balance')
            ->assertSee('Due Soon')
            ->assertSee('&#8369;110.00', false)
            ->set('search', 'blazer')
            ->assertSee('Black Open-Front Blazer')
            ->assertDontSee('Graphing Calculator')
            ->call('setFilter', Rental::PAYMENT_STATUS_PARTIAL)
            ->assertSee('Black Open-Front Blazer')
            ->call('setFilter', Rental::PAYMENT_STATUS_FULLY_PAID)
            ->assertDontSee('Black Open-Front Blazer')
            ->call('setFilter', 'all')
            ->set('dueDate', $rental->end_date->toDateString())
            ->assertSee('Black Open-Front Blazer')
            ->assertDontSee('Graphing Calculator');
    }

    public function test_due_filters_detect_due_today_and_overdue_payments(): void
    {
        [$owner] = $this->createPaymentScenario(
            itemName: 'Due Today Item',
            endDate: now(),
        );
        $this->createPaymentScenario(
            owner: $owner,
            itemName: 'Overdue Item',
            endDate: now()->subDays(2),
        );
        $this->createPaymentScenario(
            owner: $owner,
            itemName: 'Future Item',
            endDate: now()->addDays(10),
        );

        Livewire::actingAs($owner)
            ->test(ListerPayments::class)
            ->assertSee('Overdue Payments')
            ->assertSee('Due Today')
            ->assertSee('Overdue')
            ->call('setDueFilter', 'due_today')
            ->assertSee('Due Today Item')
            ->assertDontSee('Overdue Item')
            ->call('setDueFilter', 'overdue')
            ->assertSee('Overdue Item')
            ->assertDontSee('Due Today Item');
    }

    public function test_owner_can_send_due_and_overdue_payment_reminders(): void
    {
        [$owner, $renter, $upcomingRental] = $this->createPaymentScenario(endDate: now()->addDays(2));
        [, $overdueRenter, $overdueRental] = $this->createPaymentScenario(
            owner: $owner,
            renterName: 'Overdue Renter',
            itemName: 'Overdue Item',
            endDate: now()->subDay(),
        );

        Livewire::actingAs($owner)
            ->test(ListerPayments::class)
            ->call('sendUpcomingDueReminder', $upcomingRental->id)
            ->assertSee('Upcoming due reminder sent.')
            ->call('sendOverduePaymentReminder', $overdueRental->id)
            ->assertSee('Overdue payment reminder sent.');

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $renter->id,
            'notifiable_type' => $renter->getMorphClass(),
        ]);
        $this->assertSame('upcoming_due', $renter->notifications()->first()->data['type']);
        $this->assertSame('overdue_payment', $overdueRenter->notifications()->first()->data['type']);
    }

    public function test_recording_payment_sends_payment_confirmed_notification(): void
    {
        [$owner, $renter, $rental] = $this->createPaymentScenario();

        Livewire::actingAs($owner)
            ->test(ListerPayments::class)
            ->call('openAddPaymentModal', $rental->id)
            ->set('paymentAmount', '25')
            ->call('savePayment');

        $notification = $renter->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame('payment_confirmed', $notification->data['type']);
        $this->assertEquals(125.0, $notification->data['remaining_balance']);
    }

    /**
     * @return array{0: User, 1: User, 2: Rental}
     */
    private function createPaymentScenario(
        ?User $owner = null,
        string $renterName = 'Janeth Simbajon',
        string $itemName = 'Black Open-Front Blazer',
        float $paidAmount = 0,
        mixed $endDate = null,
    ): array {
        $owner ??= User::factory()->create();
        $renter = User::factory()->create(['name' => $renterName]);
        $category = Category::query()->firstOrCreate(
            ['slug' => 'clothing-payments'],
            [
                'name' => 'Clothing',
                'icon' => 'shirt',
                'is_active' => true,
            ]
        );

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => $itemName,
            'description' => 'Formal blazer',
            'condition' => 'Good',
            'price' => 150,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        $paymentStatus = match (true) {
            $paidAmount >= 150 => Rental::PAYMENT_STATUS_FULLY_PAID,
            $paidAmount > 0 => Rental::PAYMENT_STATUS_PARTIAL,
            default => Rental::PAYMENT_STATUS_OUTSTANDING,
        };

        $rentalEndDate = $endDate ? Carbon::parse($endDate) : now()->addDays(2);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => $rentalEndDate->copy()->subDay(),
            'end_date' => $rentalEndDate,
            'total_price' => 150,
            'paid_amount' => $paidAmount,
            'payment_status' => $paymentStatus,
            'status' => Rental::STATUS_APPROVED,
        ]);

        return [$owner, $renter, $rental];
    }
}
