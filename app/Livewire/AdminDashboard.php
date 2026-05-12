<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Rental;
use App\Models\Report;
use App\Models\User;
use App\Notifications\AccountRestrictedNotification;
use App\Notifications\ReportActionTakenNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AdminDashboard extends Component
{
    /**
     * @var array<int, string>
     */
    public array $adminNotes = [];

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

    public function removeItem(int $reportId): void
    {
        $report = $this->pendingReport($reportId);

        if (! $report->reportedItem) {
            session()->flash('message', 'No item is available for removal.');

            return;
        }

        DB::transaction(function () use ($report): void {
            $report->reportedItem->forceFill([
                'admin_removed_by' => Auth::id(),
                'admin_removal_reason' => $report->reason,
                'admin_removal_details' => $report->details,
                'admin_removed_at' => now(),
            ])->save();

            $report->reportedItem->delete();
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

    /**
     * @return array<string, mixed>
     */
    public function render()
    {
        $totalUsers = User::query()->count();
        $verifiedStudents = User::query()->where('is_verified_student', true)->count();
        $totalItems = Item::query()->count();
        $availableItems = Item::query()->available()->count();
        $totalRentals = Rental::query()->count();
        $activeRentals = Rental::query()->where('status', Rental::STATUS_ACTIVE)->count();
        $pendingRentals = Rental::query()->where('status', Rental::STATUS_PENDING)->count();
        $pendingReports = Report::query()
            ->where('status', Report::STATUS_PENDING)
            ->count();

        return view('livewire.admin-dashboard', [
            'totalUsers' => $totalUsers,
            'verifiedStudents' => $verifiedStudents,
            'totalItems' => $totalItems,
            'availableItems' => $availableItems,
            'totalRentals' => $totalRentals,
            'activeRentals' => $activeRentals,
            'pendingRentals' => $pendingRentals,
            'pendingReports' => $pendingReports,
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
}
