<?php

namespace App\Livewire;

use App\Models\ItemAppeal;
use App\Models\Report;
use App\Notifications\AccountRestrictedNotification;
use App\Notifications\AppealDecisionNotification;
use App\Notifications\ReportActionTakenNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AdminReportsComplaints extends Component
{
    use WithPagination;

    /**
     * @var array<int, string>
     */
    public array $adminNotes = [];

    public string $tab = 'reports';

    public string $reportStatus = Report::STATUS_PENDING;

    public string $appealStatus = ItemAppeal::STATUS_PENDING;

    public function showTab(string $tab): void
    {
        abort_unless(in_array($tab, ['reports', 'appeals'], true), 404);

        $this->tab = $tab;
        $this->resetPage();
    }

    public function dismissReport(int $reportId): void
    {
        $this->completeReport($this->pendingReport($reportId), Report::ACTION_DISMISSED);
        session()->flash('message', 'Report dismissed after verification.');
    }

    public function issueWarning(int $reportId): void
    {
        $report = $this->pendingReport($reportId);
        $targetUser = $report->targetUser();

        if (! $targetUser) {
            session()->flash('message', 'No user is available for this warning.');

            return;
        }

        DB::transaction(function () use ($report, $targetUser): void {
            $targetUser->increment('warning_count');
            $this->completeReport($report, Report::ACTION_WARNING);
        });

        session()->flash('message', 'Warning issued and report marked reviewed.');
    }

    public function removeItemFromReport(int $reportId): void
    {
        $report = $this->pendingReport($reportId);
        $item = $report->reportedItem;

        if (! $item) {
            session()->flash('message', 'No item is available for removal.');

            return;
        }

        DB::transaction(function () use ($report, $item): void {
            $item->forceFill([
                'admin_removed_by' => Auth::id(),
                'admin_removal_reason' => $report->reason,
                'admin_removal_details' => $report->details,
                'admin_removed_at' => now(),
            ])->save();

            $item->delete();
            $this->completeReport($report, Report::ACTION_ITEM_REMOVED);
        });

        session()->flash('message', 'Item removed and report marked reviewed.');
    }

    public function removeAccount(int $reportId): void
    {
        $report = $this->pendingReport($reportId);
        $targetUser = $report->targetUser();

        if (! $targetUser) {
            session()->flash('message', 'No account is available for removal.');

            return;
        }

        if ($targetUser->isAdministrator() || (int) $targetUser->id === (int) Auth::id()) {
            session()->flash('message', 'Admin accounts cannot be removed from reports.');

            return;
        }

        DB::transaction(function () use ($report, $targetUser): void {
            $targetUser->forceFill([
                'restricted_at' => now(),
                'restricted_by' => Auth::id(),
            ])->save();

            $targetUser->notify(new AccountRestrictedNotification(
                reportId: $report->id,
                reason: $report->reason,
                adminMessage: trim($this->adminNotes[$report->id] ?? '') ?: null,
            ));

            $this->completeReport($report, Report::ACTION_ACCOUNT_REMOVED);
        });

        session()->flash('message', 'Account restricted and report marked reviewed.');
    }

    public function approveAppeal(int $appealId): void
    {
        $appeal = $this->pendingAppeal($appealId);
        $item = $appeal->item;

        DB::transaction(function () use ($appeal, $item): void {
            $item->restore();
            $item->forceFill([
                'admin_removed_by' => null,
                'admin_removal_reason' => null,
                'admin_removal_details' => null,
                'admin_removed_at' => null,
            ])->save();

            $this->completeAppeal($appeal, ItemAppeal::STATUS_APPROVED);
        });

        session()->flash('message', 'Appeal approved and item restored.');
    }

    public function rejectAppeal(int $appealId): void
    {
        $this->completeAppeal($this->pendingAppeal($appealId), ItemAppeal::STATUS_REJECTED);
        session()->flash('message', 'Appeal rejected.');
    }

    public function render(): View
    {
        $reports = Report::query()
            ->with(['reporter', 'reportedUser', 'reportedItem.user', 'reportedMessage.sender'])
            ->where('status', $this->reportStatus)
            ->latest()
            ->paginate(10, pageName: 'reportsPage');

        $appeals = ItemAppeal::query()
            ->with(['item.user', 'user'])
            ->where('status', $this->appealStatus)
            ->latest()
            ->paginate(10, pageName: 'appealsPage');

        return view('livewire.admin-reports-complaints', [
            'reports' => $reports,
            'appeals' => $appeals,
        ]);
    }

    private function pendingReport(int $reportId): Report
    {
        return Report::query()
            ->whereKey($reportId)
            ->where('status', Report::STATUS_PENDING)
            ->with(['reporter', 'reportedUser', 'reportedItem.user', 'reportedMessage.sender'])
            ->firstOrFail();
    }

    private function pendingAppeal(int $appealId): ItemAppeal
    {
        return ItemAppeal::query()
            ->whereKey($appealId)
            ->where('status', ItemAppeal::STATUS_PENDING)
            ->with(['item.user', 'user'])
            ->firstOrFail();
    }

    private function completeReport(Report $report, string $action): void
    {
        $adminMessage = trim($this->adminNotes[$report->id] ?? '') ?: null;

        $report->update([
            'status' => Report::STATUS_REVIEWED,
            'reviewed_by' => Auth::id(),
            'admin_action' => $action,
            'admin_notes' => $adminMessage,
            'reviewed_at' => now(),
        ]);

        $report->loadMissing(['reporter', 'reportedItem.user', 'reportedMessage.sender', 'reportedUser']);
        $itemName = $report->reportedItem?->name;

        $report->reporter?->notify(new ReportActionTakenNotification(
            reportId: $report->id,
            audience: 'reporter',
            action: $action,
            reason: $report->reason,
            adminMessage: $adminMessage,
            itemName: $itemName,
        ));

        $targetUser = $report->targetUser();

        if ($targetUser && (int) $targetUser->id !== (int) $report->reporter_id) {
            $targetUser->notify(new ReportActionTakenNotification(
                reportId: $report->id,
                audience: 'target',
                action: $action,
                reason: $report->reason,
                adminMessage: $adminMessage,
                itemName: $itemName,
            ));
        }

        unset($this->adminNotes[$report->id]);
    }

    private function completeAppeal(ItemAppeal $appeal, string $status): void
    {
        $adminMessage = trim($this->adminNotes[$appeal->id] ?? '') ?: null;

        $appeal->update([
            'status' => $status,
            'reviewed_by' => Auth::id(),
            'admin_notes' => $adminMessage,
            'reviewed_at' => now(),
        ]);

        $appeal->loadMissing(['item', 'user']);
        $appeal->user?->notify(new AppealDecisionNotification(
            appealId: $appeal->id,
            itemName: $appeal->item?->name ?? 'your listing',
            status: $status,
            adminMessage: $adminMessage,
        ));

        unset($this->adminNotes[$appeal->id]);
    }
}
