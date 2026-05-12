<?php

namespace Tests\Feature;

use App\Livewire\OwnerRentalRequestView;
use App\Models\Category;
use App\Models\Item;
use App\Models\Rental;
use App\Models\User;
use App\Notifications\RentalMessageSentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RentalMessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_and_renter_can_send_messages_that_are_saved(): void
    {
        [$owner, $renter, $rental] = $this->createRentalRequest();

        $this->actingAs($owner);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental])
            ->set('messageText', 'Bring it tomorrow')
            ->call('sendMessage')
            ->assertSee('Message sent.')
            ->assertSee('shadow-lg', false)
            ->assertSee('Bring it tomorrow');

        $this->assertDatabaseHas('rental_messages', [
            'rental_id' => $rental->id,
            'sender_id' => $owner->id,
            'body' => 'Bring it tomorrow',
        ]);

        $ownerMessageNotification = $renter->notifications()->first();

        $this->assertNotNull($ownerMessageNotification);
        $this->assertSame(RentalMessageSentNotification::class, $ownerMessageNotification->type);
        $this->assertSame(
            route('rental-requests.show', $rental).'#messages',
            $ownerMessageNotification->data['url']
        );

        $this->actingAs($renter);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental])
            ->set('messageText', 'Okay, thank you')
            ->call('sendMessage')
            ->assertSee('Bring it tomorrow')
            ->assertSee('Okay, thank you');

        $this->assertDatabaseHas('rental_messages', [
            'rental_id' => $rental->id,
            'sender_id' => $renter->id,
            'body' => 'Okay, thank you',
        ]);

        $renterMessageNotification = $owner->notifications()->first();

        $this->assertNotNull($renterMessageNotification);
        $this->assertSame(RentalMessageSentNotification::class, $renterMessageNotification->type);
        $this->assertSame(
            route('rental-requests.show', $rental).'#messages',
            $renterMessageNotification->data['url']
        );
    }

    public function test_message_body_is_limited_to_forty_characters(): void
    {
        [$owner, , $rental] = $this->createRentalRequest();
        $this->actingAs($owner);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental])
            ->set('messageText', str_repeat('a', 41))
            ->call('sendMessage')
            ->assertHasErrors(['messageText' => 'max']);

        $this->assertDatabaseCount('rental_messages', 0);
    }

    public function test_other_users_cannot_open_or_message_a_rental_conversation(): void
    {
        [, , $rental] = $this->createRentalRequest();
        $this->actingAs(User::factory()->create());

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental])
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: User, 2: Rental}
     */
    private function createRentalRequest(): array
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
            'name' => 'Portable Projector',
            'description' => 'Compact projector',
            'price' => 100,
            'status' => 'available',
            'category' => 'electronics',
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(4),
            'total_price' => 300,
            'paid_amount' => 0,
            'payment_status' => 'outstanding',
            'status' => 'pending',
        ]);

        return [$owner, $renter, $rental];
    }
}
