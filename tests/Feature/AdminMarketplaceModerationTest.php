<?php

namespace Tests\Feature;

use App\Livewire\AdminMarketplace;
use App\Livewire\AdminReportsComplaints;
use App\Livewire\MyListings;
use App\Livewire\ViewItem;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemAppeal;
use App\Models\User;
use App\Notifications\AdminReviewQueueNotification;
use App\Notifications\AppealDecisionNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AdminMarketplaceModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_remove_item_and_owner_can_appeal_then_admin_can_restore_it(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $item = $this->createItemFor($owner);

        $this->actingAs($admin);

        Livewire::test(AdminMarketplace::class)
            ->call('confirmRemoveItem', $item->id)
            ->set('removalReason', 'Prohibited listing')
            ->set('removalDetails', 'The listing violates marketplace rules.')
            ->call('removeItem')
            ->assertSee('Item removed from the marketplace.');

        $this->assertSoftDeleted($item);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'admin_removed_by' => $admin->id,
            'admin_removal_reason' => 'Prohibited listing',
        ]);

        Notification::fake();
        $this->actingAs($owner);

        Livewire::test(MyListings::class)
            ->call('openAppeal', $item->id)
            ->set('appealReason', 'The item is allowed')
            ->set('appealDetails', 'I can provide proof that it follows policy.')
            ->call('submitAppeal')
            ->assertSee('Appeal submitted for admin review.');

        $appeal = ItemAppeal::query()->firstOrFail();

        $this->assertSame(ItemAppeal::STATUS_PENDING, $appeal->status);
        Notification::assertSentTo(
            $admin,
            AdminReviewQueueNotification::class,
            function (AdminReviewQueueNotification $notification) use ($admin): bool {
                $payload = $notification->toArray($admin);

                return $notification->title === 'New complaint submitted'
                    && $notification->appealId !== null
                    && $payload['url'] === route('admin.reports', [], false);
            }
        );

        Notification::fake();
        $this->actingAs($admin);

        Livewire::test(AdminReportsComplaints::class)
            ->call('showTab', 'appeals')
            ->set("adminNotes.{$appeal->id}", 'Your proof was accepted.')
            ->call('approveAppeal', $appeal->id)
            ->assertSee('Appeal approved and item restored.');

        $this->assertFalse($item->fresh()->trashed());
        $this->assertSame(ItemAppeal::STATUS_APPROVED, $appeal->fresh()->status);
        Notification::assertSentTo(
            $owner,
            AppealDecisionNotification::class,
            function (AppealDecisionNotification $notification) use ($item, $owner): bool {
                $payload = $notification->toArray($owner);

                return $notification->status === ItemAppeal::STATUS_APPROVED
                    && $notification->itemName === $item->name
                    && $notification->adminMessage === 'Your proof was accepted.'
                    && $payload['url'] === route('my-listings');
            }
        );
    }

    public function test_admin_cannot_create_rental_request(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $item = $this->createItemFor($owner);

        $this->actingAs($admin);

        Livewire::test(ViewItem::class, ['id' => $item->id])
            ->set('startDate', now()->addDay()->toDateString())
            ->set('endDate', now()->addDays(2)->toDateString())
            ->call('requestRental')
            ->assertSee('Admin accounts cannot create rental requests.');

        $this->assertDatabaseCount('rentals', 0);
    }

    private function createItemFor(User $owner): Item
    {
        $category = Category::query()->create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'icon' => 'chip',
            'is_active' => true,
        ]);

        return Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Portable Speaker',
            'description' => 'Compact classroom speaker',
            'condition' => 'Good',
            'price' => 75,
            'status' => 'available',
            'category_id' => $category->id,
        ]);
    }
}
