<?php

namespace Tests\Feature;

use App\Livewire\MessagesIndex;
use App\Models\Item;
use App\Models\Rental;
use App\Models\RentalMessage;
use App\Models\User;
use App\Notifications\RentalMessageSentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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
            ->assertSee('Pending Rental Requests')
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
            ->assertSee('Chats')
            ->assertSee('Search Messages')
            ->assertSee('All')
            ->assertSee('Unread')
            ->assertSee('Date')
            ->assertSee('Item')
            ->assertSee('Name')
            ->assertSee('h-[calc(100vh-4rem)]', false)
            ->assertSee('grid-rows-[auto_minmax(0,1fr)]', false)
            ->assertSee('md:grid-cols-[minmax(18rem,34vw)_minmax(0,1fr)]', false)
            ->assertSee('max-h-24 space-y-1 overflow-y-auto', false)
            ->assertSee('md:max-h-none md:flex-1', false)
            ->assertSee('hidden md:flex', false)
            ->assertDontSee('University of Mindanao community marketplace')
            ->assertSee('Select a conversation.')
            ->assertSee('Click a rental conversation to read the full thread.')
            ->assertDontSee('Open rental thread')
            ->assertDontSee(route('rental-requests.show', ['rental' => $rental, 'portal' => 'renter']).'#messages', false)
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
            ->assertSee('Select a conversation.')
            ->assertDontSee('Open rental thread')
            ->call('selectConversation', $labRental->id)
            ->assertSet('selectedRentalId', $labRental->id)
            ->assertSee('Open rental thread')
            ->assertSee('Back to conversations')
            ->assertSee('hidden md:flex', false)
            ->assertSee('min-h-0 flex-1 space-y-3 overflow-y-auto', false)
            ->assertSee('Lab Coat')
            ->assertSee('Please return tomorrow.')
            ->call('closeConversation')
            ->assertSet('selectedRentalId', null)
            ->assertSee('Select a conversation.')
            ->set('unreadOnly', true)
            ->assertSee('No conversations found.')
            ->assertDontSee('I will bring it later.');
    }

    public function test_messages_page_can_send_messages_with_success_indication(): void
    {
        Notification::fake();

        [$owner, $renter, $rental] = $this->createMessageScenario();

        RentalMessage::query()->create([
            'rental_id' => $rental->id,
            'sender_id' => $owner->id,
            'body' => 'Please return tomorrow.',
        ]);

        Livewire::actingAs($renter)
            ->test(MessagesIndex::class)
            ->call('selectConversation', $rental->id)
            ->assertSee('Please return tomorrow.')
            ->assertSee('Send')
            ->assertSee('flex items-center gap-2 sm:gap-3', false)
            ->assertSee('rows="1"', false)
            ->set('messageText', 'I will bring it.')
            ->call('sendMessage')
            ->assertSet('messageText', '')
            ->assertDontSee('Message sent successfully.')
            ->assertSee('I will bring it.')
            ->assertSee('Sent');

        $this->assertDatabaseHas('rental_messages', [
            'rental_id' => $rental->id,
            'sender_id' => $renter->id,
            'body' => 'I will bring it.',
        ]);

        Notification::assertSentTo(
            $owner,
            RentalMessageSentNotification::class,
            fn (RentalMessageSentNotification $notification): bool => $notification->rentalId === $rental->id
                && $notification->messageBody === 'I will bring it.'
        );
    }

    public function test_messages_are_marked_seen_when_recipient_opens_conversation(): void
    {
        [$owner, $renter, $rental] = $this->createMessageScenario();

        $message = RentalMessage::query()->create([
            'rental_id' => $rental->id,
            'sender_id' => $owner->id,
            'body' => 'Please return tomorrow.',
        ]);

        Livewire::actingAs($renter)
            ->test(MessagesIndex::class)
            ->call('selectConversation', $rental->id)
            ->assertSee('Please return tomorrow.');

        $this->assertNotNull($message->fresh()->read_at);

        Livewire::actingAs($owner)
            ->test(MessagesIndex::class)
            ->call('selectConversation', $rental->id)
            ->assertSee('Please return tomorrow.')
            ->assertSee('Seen');
    }

    public function test_messages_page_keeps_renter_and_lister_portal_conversations_separate(): void
    {
        $user = User::factory()->create();
        $otherRenter = User::factory()->create();
        $otherOwner = User::factory()->create();

        $ownedItem = Item::query()->create([
            'user_id' => $user->id,
            'name' => 'Owned Microscope',
            'description' => 'Campus lab item',
            'condition' => 'Good',
            'price' => 90,
            'status' => 'rented',
        ]);

        $rentedItem = Item::query()->create([
            'user_id' => $otherOwner->id,
            'name' => 'Borrowed Tripod',
            'description' => 'Camera tripod',
            'condition' => 'Good',
            'price' => 60,
            'status' => 'rented',
        ]);

        $listerRental = Rental::query()->create([
            'item_id' => $ownedItem->id,
            'renter_id' => $otherRenter->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'total_price' => 180,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_ACTIVE,
        ]);

        $renterRental = Rental::query()->create([
            'item_id' => $rentedItem->id,
            'renter_id' => $user->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'total_price' => 120,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_ACTIVE,
        ]);

        RentalMessage::query()->create([
            'rental_id' => $listerRental->id,
            'sender_id' => $otherRenter->id,
            'body' => 'Lister portal only.',
        ]);

        RentalMessage::query()->create([
            'rental_id' => $renterRental->id,
            'sender_id' => $user->id,
            'body' => 'Renter portal only.',
        ]);

        $this->actingAs($user)
            ->get(route('lister.messages'))
            ->assertOk()
            ->assertSee('Owned Microscope')
            ->assertSee('Lister portal only.')
            ->assertDontSee('Borrowed Tripod')
            ->assertDontSee('Renter portal only.');

        $this->actingAs($user)
            ->get(route('renter.messages'))
            ->assertOk()
            ->assertSee('Borrowed Tripod')
            ->assertSee('Renter portal only.')
            ->assertDontSee('Owned Microscope')
            ->assertDontSee('Lister portal only.');
    }

    /**
     * @return array{0: User, 1: User, 2: Rental}
     */
    private function createMessageScenario(): array
    {
        $owner = User::factory()->create(['name' => 'Owner User']);
        $renter = User::factory()->create(['name' => 'Renter User']);

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

        return [$owner, $renter, $rental];
    }
}
