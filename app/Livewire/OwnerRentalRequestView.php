<?php

namespace App\Livewire;

use App\Models\Rental;
use App\Models\Report;
use App\Models\User;
use App\Notifications\AdminReviewQueueNotification;
use App\Notifications\RentalMessageSentNotification;
use App\Notifications\RentalPaymentStatusNotification;
use App\Notifications\RentalRequestDecisionNotification;
use App\Notifications\ReportSubmittedNotification;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class OwnerRentalRequestView extends Component
{
    public Rental $rental;

    public bool $isOwner = false;

    public string $messageText = '';

    public bool $isEditingSchedule = false;

    public string $editableStartDate = '';

    public string $editableEndDate = '';

    public bool $showReportForm = false;

    public string $reportType = '';

    public ?int $reportMessageId = null;

    public ?int $reportUserId = null;

    public string $reportReason = '';

    public string $reportDetails = '';

    public string $decisionNotice = '';

    public string $messageNotice = '';

    public string $reportNotice = '';

    public int $noticeToken = 0;

    public bool $showPaymentModal = false;

    public string $paymentAmount = '';

    public string $paymentMode = 'add';

    public function mount(Rental $rental): void
    {
        abort_unless(Auth::check(), 403);

        $this->rental = Rental::query()
            ->whereKey($rental->id)
            ->with(['item.user', 'renter'])
            ->firstOrFail();

        $this->isOwner = (int) $this->rental->item->user_id === (int) Auth::id();
        $isRenter = (int) $this->rental->renter_id === (int) Auth::id();

        abort_unless($this->isOwner || $isRenter, 403);

        session()->put('active_portal', match (request('portal')) {
            'renter' => $isRenter ? 'renter' : 'lister',
            'lister' => $this->isOwner ? 'lister' : 'renter',
            default => $isRenter && ! $this->isOwner ? 'renter' : 'lister',
        });

        $this->syncEditableSchedule();
    }

    public function sendMessage(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->isOwner || (int) $this->rental->renter_id === (int) Auth::id(), 403);

        $this->messageText = trim($this->messageText);

        $validated = $this->validate([
            'messageText' => ['required', 'string', 'max:40'],
        ]);

        $this->rental->messages()->create([
            'sender_id' => Auth::id(),
            'body' => trim($validated['messageText']),
        ]);

        $recipient = $this->isOwner
            ? $this->rental->renter
            : $this->rental->item->user;

        $recipient->notify(new RentalMessageSentNotification(
            rentalId: $this->rental->id,
            itemId: $this->rental->item->id,
            itemName: $this->rental->item->name,
            senderName: Auth::user()->name,
            messageBody: $validated['messageText'],
        ));

        $this->reset('messageText');
        session()->flash('message', 'Message sent.');
        $this->showNotice('messageNotice', 'Message sent.');
    }

    public function grantRequest(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->isOwner, 403);

        if ($this->rental->status !== 'pending') {
            return;
        }

        $this->rental->update([
            'status' => Rental::STATUS_APPROVED,
            'approved_at' => now(),
            'cancelled_at' => null,
        ]);
        $this->rental->renter->notify(new RentalRequestDecisionNotification(
            rentalId: $this->rental->id,
            itemId: $this->rental->item->id,
            itemName: $this->rental->item->name,
            decision: 'approved',
        ));

        $this->rental->refresh();
        session()->flash('message', 'Rental request granted.');
        $this->showNotice('decisionNotice', 'Rental request granted.');
    }

    public function rejectRequest(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->isOwner, 403);

        if ($this->rental->status !== 'pending') {
            return;
        }

        $this->rental->update([
            'status' => Rental::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
        $this->rental->renter->notify(new RentalRequestDecisionNotification(
            rentalId: $this->rental->id,
            itemId: $this->rental->item->id,
            itemName: $this->rental->item->name,
            decision: 'rejected',
        ));

        $this->rental->refresh();
        session()->flash('message', 'Rental request rejected.');
        $this->showNotice('decisionNotice', 'Rental request rejected.');
    }

    public function editSchedule(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->isOwner, 403);

        if ($this->rental->status !== Rental::STATUS_PENDING) {
            return;
        }

        $this->syncEditableSchedule();
        $this->isEditingSchedule = true;
        $this->resetValidation();
    }

    public function cancelScheduleEdit(): void
    {
        $this->syncEditableSchedule();
        $this->isEditingSchedule = false;
        $this->resetValidation();
    }

    public function updateSchedule(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->isOwner, 403);

        if ($this->rental->status !== Rental::STATUS_PENDING) {
            return;
        }

        $validated = $this->validate([
            'editableStartDate' => ['required', 'date', 'after_or_equal:today'],
            'editableEndDate' => ['required', 'date', 'after:editableStartDate'],
        ]);

        $startDate = Carbon::parse($validated['editableStartDate'])->startOfDay();
        $endDate = Carbon::parse($validated['editableEndDate'])->startOfDay();
        $seconds = $startDate->diffInSeconds($endDate, false);
        $days = max(1, (int) ceil($seconds / 86400));

        $this->rental->update([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_price' => $days * (float) $this->rental->item->price,
        ]);

        $this->rental->refresh()->load(['item.user', 'renter']);
        $this->syncEditableSchedule();
        $this->isEditingSchedule = false;

        session()->flash('message', 'Rental dates updated.');
    }

    public function openUserReportForm(int $userId): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->isOwner || (int) $this->rental->renter_id === (int) Auth::id(), 403);
        abort_if($userId === Auth::id(), 403, 'You cannot report yourself.');

        $allowedUserIds = [
            (int) $this->rental->item->user_id,
            (int) $this->rental->renter_id,
        ];

        abort_unless(in_array($userId, $allowedUserIds, true), 404);

        $this->startReport(Report::TYPE_USER);
        $this->reportUserId = $userId;
    }

    public function openMessageReportForm(int $messageId): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->isOwner || (int) $this->rental->renter_id === (int) Auth::id(), 403);

        $message = $this->rental->messages()->whereKey($messageId)->firstOrFail();
        abort_if((int) $message->sender_id === (int) Auth::id(), 403, 'You cannot report your own message.');

        $this->startReport(Report::TYPE_MESSAGE);
        $this->reportMessageId = $message->id;
        $this->reportUserId = $message->sender_id;
    }

    public function cancelReport(): void
    {
        $this->showReportForm = false;
        $this->reportType = '';
        $this->reportMessageId = null;
        $this->reportUserId = null;
        $this->reportReason = '';
        $this->reportDetails = '';
        $this->resetValidation();
    }

    public function submitReport(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless(in_array($this->reportType, [Report::TYPE_USER, Report::TYPE_MESSAGE], true), 404);

        $validated = $this->validate([
            'reportReason' => ['required', 'string', 'max:120'],
            'reportDetails' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($this->reportType === Report::TYPE_MESSAGE) {
            $message = $this->rental->messages()->whereKey($this->reportMessageId)->firstOrFail();
            abort_if((int) $message->sender_id === (int) Auth::id(), 403, 'You cannot report your own message.');
            $reportedUserId = $message->sender_id;
            $reportedMessageId = $message->id;
        } else {
            abort_unless(User::query()->whereKey($this->reportUserId)->exists(), 404);
            abort_if((int) $this->reportUserId === (int) Auth::id(), 403, 'You cannot report yourself.');
            $reportedUserId = $this->reportUserId;
            $reportedMessageId = null;
        }

        $report = Report::query()->create([
            'reporter_id' => Auth::id(),
            'reported_user_id' => $reportedUserId,
            'reported_message_id' => $reportedMessageId,
            'type' => $this->reportType,
            'reason' => trim($validated['reportReason']),
            'details' => trim((string) $validated['reportDetails']) ?: null,
        ]);

        User::query()->find($reportedUserId)?->notify(new ReportSubmittedNotification(
            reportId: $report->id,
            reportType: $report->type,
            reason: $report->reason,
        ));

        User::query()
            ->where('is_admin', true)
            ->get()
            ->each
            ->notify(new AdminReviewQueueNotification(
                title: 'New report submitted',
                message: "{$report->reporter?->name} submitted a {$report->type} report for admin review.",
                reportId: $report->id,
            ));

        $this->cancelReport();
        session()->flash('message', 'Report submitted. An admin will verify it.');
        $this->showNotice('reportNotice', 'Report submitted. An admin will verify it.');
    }

    public function openAddPaymentModal(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->isOwner, 403);
        abort_unless($this->canRecordPayment(), 404);

        $this->paymentMode = 'add';
        $this->paymentAmount = '';
        $this->showPaymentModal = true;
        $this->resetValidation('paymentAmount');
    }

    public function fillFullPaymentAmount(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->isOwner, 403);
        abort_unless($this->canRecordPayment(), 404);

        $this->paymentMode = 'full';
        $this->paymentAmount = number_format($this->remainingBalance(), 2, '.', '');
        $this->resetValidation('paymentAmount');
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->paymentMode = 'add';
        $this->paymentAmount = '';
        $this->resetValidation('paymentAmount');
    }

    public function savePayment(): mixed
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->isOwner, 403);
        abort_unless($this->canRecordPayment(), 404);

        $remainingBalance = $this->remainingBalance();

        $this->validate([
            'paymentAmount' => ['required', 'numeric', 'gt:0', 'max:'.$remainingBalance],
        ], [
            'paymentAmount.max' => 'The payment must not exceed the remaining balance.',
        ]);

        $this->rental->applyPayment((float) $this->paymentAmount);
        $this->rental->refresh()->load(['item.user', 'renter']);

        $this->rental->renter->notify(new RentalPaymentStatusNotification(
            rentalId: $this->rental->id,
            itemId: $this->rental->item->id,
            itemName: $this->rental->item->name,
            type: 'payment_confirmed',
            message: "Your payment for {$this->rental->item->name} has been confirmed.",
            remainingBalance: $this->remainingBalance(),
            dueDate: $this->rental->end_date->toDateString(),
        ));

        $this->closePaymentModal();

        return redirect()->to(route('lister.payments', ['filter' => $this->rental->payment_status]).'#payment-'.$this->rental->id);
    }

    private function startReport(string $type): void
    {
        $this->resetValidation();
        $this->reportType = $type;
        $this->reportMessageId = null;
        $this->reportUserId = null;
        $this->reportReason = '';
        $this->reportDetails = '';
        $this->showReportForm = true;
    }

    private function syncEditableSchedule(): void
    {
        $this->editableStartDate = $this->rental->start_date->toDateString();
        $this->editableEndDate = $this->rental->end_date->toDateString();
    }

    private function canRecordPayment(): bool
    {
        return $this->rental->status !== Rental::STATUS_CANCELLED
            && $this->rental->payment_status !== Rental::PAYMENT_STATUS_FULLY_PAID
            && $this->remainingBalance() > 0;
    }

    private function remainingBalance(): float
    {
        return max(0, round((float) $this->rental->total_price - (float) ($this->rental->paid_amount ?? 0), 2));
    }

    private function showNotice(string $property, string $message): void
    {
        $this->{$property} = $message;
        $this->noticeToken++;
    }

    public function render(): View
    {
        $secondsLeft = now()->diffInSeconds($this->rental->end_date, false);
        $daysLeft = $secondsLeft >= 0
            ? (int) ceil($secondsLeft / 86400)
            : (int) floor($secondsLeft / 86400);
        $secondsRequested = $this->rental->start_date->diffInSeconds($this->rental->end_date, false);
        $daysRequested = max(1, (int) ceil($secondsRequested / 86400));
        $isOnProcess = $this->rental->status === Rental::STATUS_APPROVED
            || ($this->rental->status === Rental::STATUS_ACTIVE && $this->rental->start_date->isFuture());
        $dueNow = $this->rental->status === Rental::STATUS_ACTIVE && $daysLeft <= 0;
        $dueSoon = $this->rental->status === Rental::STATUS_ACTIVE
            && now()->between($this->rental->start_date, $this->rental->end_date)
            && $daysLeft <= 7;

        return view('livewire.owner-rental-request-view', [
            'daysRequested' => $daysRequested,
            'daysLeft' => max(0, $daysLeft),
            'dueNow' => $dueNow,
            'dueSoon' => $dueSoon,
            'dueTomorrow' => $this->rental->status === Rental::STATUS_ACTIVE && $daysLeft === 1,
            'isOnProcess' => $isOnProcess,
            'isOwner' => $this->isOwner,
            'messages' => $this->rental->messages()
                ->with('sender')
                ->oldest()
                ->get(),
        ]);
    }
}
