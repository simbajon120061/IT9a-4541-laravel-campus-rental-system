<?php

namespace Tests\Feature;

use App\Livewire\ListerPayments;
use App\Models\Category;
use App\Models\Item;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    /**
     * @return array{0: User, 1: User, 2: Rental}
     */
    private function createPaymentScenario(float $paidAmount = 0): array
    {
        $owner = User::factory()->create();
        $renter = User::factory()->create(['name' => 'Janeth Simbajon']);
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
            'name' => 'Black Open-Front Blazer',
            'description' => 'Formal blazer',
            'condition' => 'Good',
            'price' => 150,
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        $paymentStatus = $paidAmount > 0
            ? Rental::PAYMENT_STATUS_PARTIAL
            : Rental::PAYMENT_STATUS_OUTSTANDING;

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'total_price' => 150,
            'paid_amount' => $paidAmount,
            'payment_status' => $paymentStatus,
            'status' => Rental::STATUS_APPROVED,
        ]);

        return [$owner, $renter, $rental];
    }
}
