<?php

namespace Tests\Feature;

use App\Livewire\AdminDashboard;
use App\Livewire\OwnerRentalRequestView;
use App\Livewire\ViewItem;
use App\Models\Category;
use App\Models\Item;
use App\Models\Rental;
use App\Models\RentalMessage;
use App\Models\Report;
use App\Models\User;
use App\Notifications\AccountRestrictedNotification;
use App\Notifications\ReportActionTakenNotification;
use App\Notifications\ReportSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ReportModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_report_an_item_and_the_item_owner(): void
    {
        [, $renter, $item] = $this->createRentalScenario();

        Notification::fake();
        $this->actingAs($renter);

        Livewire::test(ViewItem::class, ['id' => $item->id])
            ->call('openReportForm', Report::TYPE_ITEM)
            ->set('reportReason', 'Misleading item details')
            ->set('reportDetails', 'The photo does not match the item.')
            ->call('submitReport')
            ->assertSee('Report submitted. An admin will verify it.');

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $renter->id,
            'reported_user_id' => $item->user_id,
            'reported_item_id' => $item->id,
            'type' => Report::TYPE_ITEM,
            'status' => Report::STATUS_PENDING,
        ]);

        Notification::assertSentTo(
            $item->user,
            ReportSubmittedNotification::class,
            function (ReportSubmittedNotification $notification) use ($item, $renter): bool {
                $payload = $notification->toArray($item->user);

                return $notification->reportType === Report::TYPE_ITEM
                    && $notification->reason === 'Misleading item details'
                    && $notification->itemName === $item->name
                    && $payload['title'] === 'Report received'
                    && str_contains($payload['message'], 'A user submitted a report')
                    && ! str_contains($payload['message'], $renter->name)
                    && $payload['url'] === route('my-listings');
            }
        );

        Livewire::test(ViewItem::class, ['id' => $item->id])
            ->call('openReportForm', Report::TYPE_USER)
            ->set('reportReason', 'Unsafe meetup behavior')
            ->call('submitReport');

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $renter->id,
            'reported_user_id' => $item->user_id,
            'reported_item_id' => null,
            'type' => Report::TYPE_USER,
        ]);
    }

    public function test_user_can_report_a_message(): void
    {
        [$owner, $renter, , $rental] = $this->createRentalScenario();
        $message = RentalMessage::query()->create([
            'rental_id' => $rental->id,
            'sender_id' => $owner->id,
            'body' => 'Pay now',
        ]);

        Notification::fake();
        $this->actingAs($renter);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental])
            ->call('openMessageReportForm', $message->id)
            ->set('reportReason', 'Threatening message')
            ->call('submitReport')
            ->assertSee('Report submitted. An admin will verify it.');

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $renter->id,
            'reported_user_id' => $owner->id,
            'reported_message_id' => $message->id,
            'type' => Report::TYPE_MESSAGE,
        ]);

        Notification::assertSentTo(
            $owner,
            ReportSubmittedNotification::class,
            function (ReportSubmittedNotification $notification) use ($owner, $renter): bool {
                $payload = $notification->toArray($owner);

                return $notification->reportType === Report::TYPE_MESSAGE
                    && $notification->reason === 'Threatening message'
                    && $payload['title'] === 'Report received'
                    && str_contains($payload['message'], 'A user submitted a report')
                    && ! str_contains($payload['message'], $renter->name);
            }
        );
    }

    public function test_user_report_form_opens_from_rental_request_owner_card(): void
    {
        [$owner, $renter, , $rental] = $this->createRentalScenario();

        Notification::fake();
        $this->actingAs($renter);

        Livewire::test(OwnerRentalRequestView::class, ['rental' => $rental])
            ->call('openUserReportForm', $owner->id)
            ->assertSet('showReportForm', true)
            ->assertSet('reportType', Report::TYPE_USER)
            ->assertSet('reportUserId', $owner->id)
            ->assertSee('Report User')
            ->assertSee('Share what admins should verify.')
            ->set('reportReason', 'Unsafe meetup behavior')
            ->call('submitReport')
            ->assertSet('showReportForm', false)
            ->assertSee('Report submitted. An admin will verify it.');

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $renter->id,
            'reported_user_id' => $owner->id,
            'reported_message_id' => null,
            'type' => Report::TYPE_USER,
        ]);
    }

    public function test_admin_can_issue_warning_remove_item_and_remove_account_after_verification(): void
    {
        [$owner, $renter, $item] = $this->createRentalScenario();
        $accountTarget = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $warningReport = Report::query()->create([
            'reporter_id' => $renter->id,
            'reported_user_id' => $owner->id,
            'type' => Report::TYPE_USER,
            'reason' => 'Late handover',
        ]);
        $itemReport = Report::query()->create([
            'reporter_id' => $renter->id,
            'reported_user_id' => $owner->id,
            'reported_item_id' => $item->id,
            'type' => Report::TYPE_ITEM,
            'reason' => 'Broken item',
        ]);
        $accountReport = Report::query()->create([
            'reporter_id' => $renter->id,
            'reported_user_id' => $accountTarget->id,
            'type' => Report::TYPE_USER,
            'reason' => 'Repeated unsafe behavior',
        ]);

        Notification::fake();
        $this->actingAs($admin);

        Livewire::test(AdminDashboard::class)
            ->set("adminNotes.{$warningReport->id}", 'Please keep exchanges respectful.')
            ->call('issueWarning', $warningReport->id)
            ->call('removeItem', $itemReport->id)
            ->call('removeAccount', $accountReport->id);

        $this->assertSame(1, $owner->fresh()->warning_count);
        $this->assertSoftDeleted($item);
        $accountTarget->refresh();
        $this->assertFalse($accountTarget->trashed());
        $this->assertNotNull($accountTarget->restricted_at);
        $this->assertSame($admin->id, $accountTarget->restricted_by);

        Notification::assertSentTo(
            $accountTarget,
            AccountRestrictedNotification::class,
            function (AccountRestrictedNotification $notification) use ($accountReport, $accountTarget): bool {
                $payload = $notification->toArray($accountTarget);

                return $notification->reportId === $accountReport->id
                    && $notification->reason === 'Repeated unsafe behavior'
                    && $payload['title'] === 'Account restricted';
            }
        );

        $this->assertDatabaseHas('reports', [
            'id' => $warningReport->id,
            'status' => Report::STATUS_REVIEWED,
            'admin_action' => Report::ACTION_WARNING,
            'reviewed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('reports', [
            'id' => $itemReport->id,
            'status' => Report::STATUS_REVIEWED,
            'admin_action' => Report::ACTION_ITEM_REMOVED,
            'reviewed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('reports', [
            'id' => $accountReport->id,
            'status' => Report::STATUS_REVIEWED,
            'admin_action' => Report::ACTION_ACCOUNT_REMOVED,
            'reviewed_by' => $admin->id,
        ]);

        Notification::assertSentTo(
            $renter,
            ReportActionTakenNotification::class,
            function (ReportActionTakenNotification $notification) use ($renter): bool {
                $payload = $notification->toArray($renter);

                return $notification->audience === 'reporter'
                    && $notification->action === Report::ACTION_WARNING
                    && $payload['title'] === 'Report action update';
            }
        );

        Notification::assertSentTo(
            $owner,
            ReportActionTakenNotification::class,
            function (ReportActionTakenNotification $notification) use ($owner): bool {
                $payload = $notification->toArray($owner);

                return $notification->audience === 'target'
                    && $notification->action === Report::ACTION_WARNING
                    && $notification->adminMessage === 'Please keep exchanges respectful.'
                    && str_contains($payload['message'], 'Admin message: Please keep exchanges respectful.');
            }
        );
    }

    /**
     * @return array{0: User, 1: User, 2: Item, 3: Rental}
     */
    private function createRentalScenario(): array
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
            'category_id' => $category->id,
        ]);

        $rental = Rental::query()->create([
            'item_id' => $item->id,
            'renter_id' => $renter->id,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'total_price' => 200,
            'paid_amount' => 0,
            'payment_status' => Rental::PAYMENT_STATUS_OUTSTANDING,
            'status' => Rental::STATUS_PENDING,
        ]);

        return [$owner, $renter, $item, $rental];
    }
}
