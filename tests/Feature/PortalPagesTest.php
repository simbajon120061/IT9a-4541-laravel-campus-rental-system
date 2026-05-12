<?php

namespace Tests\Feature;

use App\Livewire\MessagesIndex;
use App\Models\Item;
use App\Models\Rental;
use App\Models\RentalMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PortalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_lister_sidebar_pages_show_expected_links_and_data(): void
    {
        $owner = User::factory()->create();
        $renter = User::factory()->create();
        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Graphing Calculator',
            'description' => 'For calculus class',
            'condition' => 'Good',
            'price' => 75,
            'status' => 'available',
        ]);

        Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 150,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_PENDING,
        ]);

        $this->actingAs($owner)
            ->get(route('lister.rental-requests'))
            ->assertOk()
            ->assertSee('Rental Requests')
            ->assertSee('Graphing Calculator')
            ->assertSee('Lister Dashboard')
            ->assertSee('Payments')
            ->assertSee('Messages');

        $this->actingAs($owner)
            ->get(route('lister.payments'))
            ->assertOk()
            ->assertSee('Payments')
            ->assertSee('Graphing Calculator')
            ->assertSee('Outstanding');
    }

    public function test_renter_messages_page_is_available_from_message_icon(): void
    {
        $owner = User::factory()->create();
        $renter = User::factory()->create();
        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Lab Coat',
            'description' => 'Medium size',
            'condition' => 'Good',
            'price' => 40,
            'status' => 'rented',
        ]);
        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'total_price' => 80,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_ACTIVE,
        ]);

        RentalMessage::query()->create([
            'rental_id' => $rental->id,
            'sender_id' => $owner->id,
            'body' => 'Please return tomorrow.',
        ]);

        $this->actingAs($renter)
            ->get(route('renter.dashboard'))
            ->assertOk()
            ->assertSee(route('renter.messages'), false);

        $this->actingAs($renter)
            ->get(route('renter.messages'))
            ->assertOk()
            ->assertSee('Conversations')
            ->assertSee('Unread Messages')
            ->assertSee('Sort by')
            ->assertSee('Open rental thread')
            ->assertSee('Lab Coat')
            ->assertSee('Please return tomorrow.');
    }

    public function test_messages_page_can_select_conversations_and_filter_unread(): void
    {
        $owner = User::factory()->create(['name' => 'Owner User']);
        $renter = User::factory()->create(['name' => 'Renter User']);
        $secondOwner = User::factory()->create(['name' => 'Second Owner']);

        $labCoat = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Lab Coat',
            'description' => 'Medium size',
            'condition' => 'Good',
            'price' => 40,
            'status' => 'rented',
        ]);
        $tripod = Item::query()->create([
            'user_id' => $secondOwner->id,
            'name' => 'Tripod',
            'description' => 'Camera tripod',
            'condition' => 'Good',
            'price' => 60,
            'status' => 'rented',
        ]);

        $labRental = Rental::query()->create([
            'item_id' => $labCoat->id,
            'renter_id' => $renter->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'total_price' => 80,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_ACTIVE,
        ]);
        $tripodRental = Rental::query()->create([
            'item_id' => $tripod->id,
            'renter_id' => $renter->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'total_price' => 120,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_ACTIVE,
        ]);

        RentalMessage::query()->create([
            'rental_id' => $labRental->id,
            'sender_id' => $owner->id,
            'body' => 'Please return tomorrow.',
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);
        RentalMessage::query()->create([
            'rental_id' => $tripodRental->id,
            'sender_id' => $renter->id,
            'body' => 'I will bring it later.',
            'created_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(5),
        ]);

        Livewire::actingAs($renter)
            ->test(MessagesIndex::class)
            ->assertSee('Tripod')
            ->assertSee('I will bring it later.')
            ->call('selectConversation', $labRental->id)
            ->assertSet('selectedRentalId', $labRental->id)
            ->assertSee('Lab Coat')
            ->assertSee('Please return tomorrow.')
            ->set('unreadOnly', true)
            ->assertSee('Lab Coat')
            ->assertDontSee('I will bring it later.');
    }
}
