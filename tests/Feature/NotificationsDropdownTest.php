<?php

namespace Tests\Feature;

use App\Livewire\NotificationsDropdown;
use App\Models\Category;
use App\Models\Item;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationsDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_notification_redirects_to_owner_rental_request_view_and_deletes_it(): void
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

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'total_price' => 100,
            'paid_amount' => 0,
            'payment_status' => 'outstanding',
            'status' => 'pending',
        ]);

        $notification = $owner->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\RentalRequestedNotification',
            'data' => [
                'title' => 'New rental request',
                'message' => 'Test message',
                'rental_id' => $rental->id,
            ],
        ]);

        $this->actingAs($owner);

        Livewire::test(NotificationsDropdown::class)
            ->call('openNotification', $notification->id)
            ->assertRedirect(route('rental-requests.show', ['rental' => $rental, 'portal' => 'lister']));

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_open_notification_without_rental_id_resolves_owner_rental_request_view(): void
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

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'total_price' => 100,
            'paid_amount' => 0,
            'payment_status' => 'outstanding',
            'status' => 'pending',
        ]);

        $notification = $owner->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\RentalRequestedNotification',
            'data' => [
                'title' => 'New rental request',
                'message' => 'Test message',
                'item_id' => $item->id,
                'renter_id' => $renter->id,
            ],
        ]);

        $this->actingAs($owner);

        Livewire::test(NotificationsDropdown::class)
            ->call('openNotification', $notification->id)
            ->assertRedirect(route('rental-requests.show', ['rental' => $rental, 'portal' => 'lister']));
    }

    public function test_open_notification_with_encrypted_payload_resolves_owner_rental_request_view(): void
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

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'total_price' => 100,
            'paid_amount' => 0,
            'payment_status' => 'outstanding',
            'status' => 'pending',
        ]);

        $notification = $owner->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\RentalRequestedNotification',
            'data' => [
                'title' => 'New rental request',
                'message' => 'Test message',
                'encrypted_item_id' => Crypt::encryptString((string) $item->id),
                'encrypted_renter_id' => Crypt::encryptString((string) $renter->id),
                'encrypted_rental_id' => Crypt::encryptString((string) $rental->id),
            ],
        ]);

        $this->actingAs($owner);

        Livewire::test(NotificationsDropdown::class)
            ->call('openNotification', $notification->id)
            ->assertRedirect(route('rental-requests.show', ['rental' => $rental, 'portal' => 'lister']));
    }

    public function test_open_notification_uses_explicit_url_for_renter_updates(): void
    {
        $renter = User::factory()->create();

        $notification = $renter->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\RentalRequestDecisionNotification',
            'data' => [
                'title' => 'Rental request update',
                'message' => 'Your request was approved.',
                'rental_id' => 4,
                'url' => route('my-rentals'),
            ],
        ]);

        $this->actingAs($renter);

        Livewire::test(NotificationsDropdown::class)
            ->call('openNotification', $notification->id)
            ->assertRedirect(route('my-rentals'));
    }

    public function test_open_message_notification_redirects_to_messages_anchor(): void
    {
        $renter = User::factory()->create();
        $messageUrl = route('rental-requests.show', 10).'#messages';

        $notification = $renter->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\RentalMessageSentNotification',
            'data' => [
                'title' => 'New message',
                'message' => 'Owner: Please bring an ID',
                'rental_id' => 10,
                'url' => $messageUrl,
            ],
        ]);

        $this->actingAs($renter);

        Livewire::test(NotificationsDropdown::class)
            ->call('openNotification', $notification->id)
            ->assertRedirect($messageUrl);

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

<<<<<<< HEAD
    public function test_admin_review_notification_redirects_to_reports_and_complaints(): void
    {
        $admin = User::factory()->admin()->create();

        $notification = $admin->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\AdminReviewQueueNotification',
            'data' => [
                'title' => 'New report submitted',
                'message' => 'A new report needs admin review.',
                'report_id' => 10,
                'url' => route('admin.reports', [], false),
            ],
        ]);

        $this->actingAs($admin);

        Livewire::test(NotificationsDropdown::class)
            ->assertSee(route('admin.reports', [], false));

        Livewire::test(NotificationsDropdown::class)
            ->call('openNotification', $notification->id)
            ->assertRedirect(route('admin.reports', [], false));

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
=======
    public function test_renter_rental_update_redirects_to_my_rentals_row_even_with_stale_url(): void
    {
        [$owner, $renter, $rental] = $this->createRentalForNotifications();

        $notification = $renter->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\RentalPaymentStatusNotification',
            'data' => [
                'title' => 'Payment confirmed',
                'message' => 'Your payment was confirmed.',
                'type' => 'payment_confirmed',
                'rental_id' => $rental->id,
                'url' => route('rental-requests.show', $rental),
            ],
        ]);

        $this->actingAs($renter);

        Livewire::test(NotificationsDropdown::class)
            ->call('openNotification', $notification->id)
            ->assertRedirect(route('renter.my-rentals', ['receipt' => $rental->id]).'#rental-'.$rental->id);

        $this->assertSame('renter', session('active_portal'));
        $this->assertNotSame($owner->id, $renter->id);
    }

    public function test_renter_message_notification_opens_renter_conversation(): void
    {
        [, $renter, $rental] = $this->createRentalForNotifications();

        $notification = $renter->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\RentalMessageSentNotification',
            'data' => [
                'title' => 'New message',
                'message' => 'Owner: Please bring an ID',
                'rental_id' => $rental->id,
                'url' => route('rental-requests.show', $rental).'#messages',
            ],
        ]);

        $this->actingAs($renter);

        Livewire::test(NotificationsDropdown::class)
            ->call('openNotification', $notification->id)
            ->assertRedirect(route('renter.messages', ['rental' => $rental->id]));

        $this->assertSame('renter', session('active_portal'));
    }

    public function test_lister_message_notification_opens_lister_conversation(): void
    {
        [$owner, , $rental] = $this->createRentalForNotifications();

        $notification = $owner->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\RentalMessageSentNotification',
            'data' => [
                'title' => 'New message',
                'message' => 'Renter: Thank you',
                'rental_id' => $rental->id,
                'url' => route('rental-requests.show', $rental).'#messages',
            ],
        ]);

        $this->actingAs($owner);

        Livewire::test(NotificationsDropdown::class)
            ->call('openNotification', $notification->id)
            ->assertRedirect(route('lister.messages', ['rental' => $rental->id]));

        $this->assertSame('lister', session('active_portal'));
    }

    /**
     * @return array{0: User, 1: User, 2: Rental}
     */
    private function createRentalForNotifications(): array
    {
        $owner = User::factory()->create();
        $renter = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Electronics',
            'slug' => 'electronics-'.Str::random(8),
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

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'total_price' => 100,
            'paid_amount' => 0,
            'payment_status' => 'outstanding',
            'status' => 'pending',
        ]);

        return [$owner, $renter, $rental];
>>>>>>> 52b7941cd76779bdee095a61ffbd968ea489ec59
    }
}
