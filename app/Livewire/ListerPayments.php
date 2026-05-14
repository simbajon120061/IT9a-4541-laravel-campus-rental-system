<?php

namespace App\Livewire;

use App\Models\Rental;
use App\Notifications\RentalPaymentStatusNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ListerPayments extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = 'all';

    public string $dueFilter = 'all';

    public string $dueDate = '';

    /** @var array<int, mixed> */
    public array $paymentAmounts = [];

    public ?int $selectedPaymentId = null;

    public string $paymentAmount = '';

    public ?string $paymentMode = null;

    public function mount(): void
    {
        $filter = request('filter');

        if (in_array($filter, [
            Rental::PAYMENT_STATUS_OUTSTANDING,
            Rental::PAYMENT_STATUS_PARTIAL,
            Rental::PAYMENT_STATUS_FULLY_PAID,
            'due_soon',
            'due_today',
            'overdue',
        ], true)) {
            if (in_array($filter, ['due_soon', 'due_today', 'overdue'], true)) {
                $this->dueFilter = $filter;
            } else {
                $this->filterStatus = $filter;
            }
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDueDate(): void
    {
        $this->resetPage();
    }

    public function setFilter(string $status): void
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function setDueFilter(string $status): void
    {
        $this->dueFilter = $status;
        $this->resetPage();
    }

    public function showAll(): void
    {
        $this->filterStatus = 'all';
        $this->dueFilter = 'all';
        $this->dueDate = '';
        $this->search = '';
        $this->resetPage();
    }

    public function clearDueFilters(): void
    {
        $this->dueFilter = 'all';
        $this->dueDate = '';
        $this->resetPage();
    }

    public function openAddPaymentModal(int $rentalId): void
    {
        abort_unless(Auth::check(), 403);

        $this->paymentRentalQuery()->findOrFail($rentalId);

        $this->selectedPaymentId = $rentalId;
        $this->paymentMode = 'add';
        $this->paymentAmount = '';
        $this->resetValidation('paymentAmount');
    }

    public function openFullPaymentModal(int $rentalId): void
    {
        abort_unless(Auth::check(), 403);

        $rental = $this->paymentRentalQuery()->findOrFail($rentalId);

        $this->selectedPaymentId = $rentalId;
        $this->paymentMode = 'full';
        $this->paymentAmount = number_format($this->remainingBalance($rental), 2, '.', '');
        $this->resetValidation('paymentAmount');
    }

    public function closePaymentModal(): void
    {
        $this->selectedPaymentId = null;
        $this->paymentMode = null;
        $this->paymentAmount = '';
        $this->resetValidation('paymentAmount');
    }

    public function savePayment(): void
    {
        abort_unless(Auth::check(), 403);
        abort_if($this->selectedPaymentId === null, 404);

        $rental = $this->paymentRentalQuery()->findOrFail($this->selectedPaymentId);
        $this->recordPaymentAmount($rental, $this->paymentAmount, 'paymentAmount');
        $this->closePaymentModal();

        session()->flash('message', 'Payment recorded successfully.');
    }

    public function recordPayment(int $rentalId): void
    {
        abort_unless(Auth::check(), 403);

        $rental = $this->paymentRentalQuery()->findOrFail($rentalId);
        $this->recordPaymentAmount($rental, $this->paymentAmounts[$rentalId] ?? null, "paymentAmounts.$rentalId");

        $this->paymentAmounts[$rentalId] = '';
        session()->flash('message', 'Payment recorded successfully.');
    }

    public function sendUpcomingDueReminder(int $rentalId): void
    {
        abort_unless(Auth::check(), 403);

        $rental = $this->paymentRentalQuery()
            ->with(['item', 'renter'])
            ->findOrFail($rentalId);

        abort_unless($this->remainingBalance($rental) > 0 && $this->isUpcomingDue($rental), 422);

        $this->notifyRenter(
            rental: $rental,
            type: 'upcoming_due',
            message: "Reminder: your payment for {$rental->item->name} is due on {$rental->end_date->format('M d, Y')}."
        );

        session()->flash('message', 'Upcoming due reminder sent.');
    }

    public function sendOverduePaymentReminder(int $rentalId): void
    {
        abort_unless(Auth::check(), 403);

        $rental = $this->paymentRentalQuery()
            ->with(['item', 'renter'])
            ->findOrFail($rentalId);

        abort_unless($this->remainingBalance($rental) > 0 && $this->isOverdue($rental), 422);

        $this->notifyRenter(
            rental: $rental,
            type: 'overdue_payment',
            message: "Reminder: your payment for {$rental->item->name} was due on {$rental->end_date->format('M d, Y')}."
        );

        session()->flash('message', 'Overdue payment reminder sent.');
    }

    public function render(): View
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::user()?->isAdministrator(), 403);

        $paymentsQuery = $this->paymentRentalQuery()
            ->with(['item.categoryRecord', 'renter'])
            ->latest('updated_at');

        if ($this->search !== '') {
            $search = '%'.trim($this->search).'%';
            $paymentsQuery->where(function ($query) use ($search): void {
                $query->whereHas('item', fn ($itemQuery) => $itemQuery->where('name', 'like', $search))
                    ->orWhereHas('renter', fn ($renterQuery) => $renterQuery->where('name', 'like', $search));
            });
        }

        if (in_array($this->filterStatus, [
            Rental::PAYMENT_STATUS_OUTSTANDING,
            Rental::PAYMENT_STATUS_PARTIAL,
            Rental::PAYMENT_STATUS_FULLY_PAID,
        ], true)) {
            $paymentsQuery->where('payment_status', $this->filterStatus);
        }

        if ($this->dueDate !== '') {
            $paymentsQuery->whereDate('end_date', $this->dueDate);
        }

        $this->applyDueFilter($paymentsQuery, $this->dueFilter);

        $summaryQuery = $this->paymentRentalQuery();

        $selectedPayment = $this->selectedPaymentId
            ? $this->paymentRentalQuery()
                ->with(['item.categoryRecord', 'renter'])
                ->find($this->selectedPaymentId)
            : null;

        return view('livewire.lister-payments', [
            'payments' => $paymentsQuery->paginate(10),
            'selectedPayment' => $selectedPayment,
            'outstandingCount' => (clone $summaryQuery)->where('payment_status', Rental::PAYMENT_STATUS_OUTSTANDING)->count(),
            'partialCount' => (clone $summaryQuery)->where('payment_status', Rental::PAYMENT_STATUS_PARTIAL)->count(),
            'paidCount' => (clone $summaryQuery)->where('payment_status', Rental::PAYMENT_STATUS_FULLY_PAID)->count(),
            'totalPaid' => (float) (clone $summaryQuery)->sum('paid_amount'),
            'totalAmount' => (float) (clone $summaryQuery)->sum('total_price'),
            'totalTransactions' => (clone $summaryQuery)->count(),
            'overduePaymentsCount' => $this->applyDueFilter(
                (clone $summaryQuery)->where('payment_status', '!=', Rental::PAYMENT_STATUS_FULLY_PAID),
                'overdue'
            )->count(),
        ]);
    }

    private function paymentRentalQuery(): Builder
    {
        return Rental::query()
            ->whereHas('item', fn ($query) => $query->where('user_id', Auth::id()))
            ->where('status', '!=', Rental::STATUS_CANCELLED);
    }

    private function applyDueFilter(Builder $query, string $dueFilter): Builder
    {
        if ($dueFilter === 'due_soon') {
            return $query
                ->where('payment_status', '!=', Rental::PAYMENT_STATUS_FULLY_PAID)
                ->whereDate('end_date', '>', now()->toDateString())
                ->whereDate('end_date', '<=', now()->addDays(7)->toDateString());
        }

        if ($dueFilter === 'due_today') {
            return $query
                ->where('payment_status', '!=', Rental::PAYMENT_STATUS_FULLY_PAID)
                ->whereDate('end_date', now()->toDateString());
        }

        if ($dueFilter === 'overdue') {
            return $query
                ->where('payment_status', '!=', Rental::PAYMENT_STATUS_FULLY_PAID)
                ->whereDate('end_date', '<', now()->toDateString());
        }

        return $query;
    }

    private function recordPaymentAmount(Rental $rental, mixed $amount, string $errorKey): void
    {
        $remainingBalance = $this->remainingBalance($rental);

        $this->validate([
            $errorKey => ['required', 'numeric', 'gt:0', 'max:'.$remainingBalance],
        ], [
            $errorKey.'.max' => 'The payment must not exceed the remaining balance.',
        ]);

        $rental->applyPayment((float) $amount);
        $rental->refresh()->loadMissing(['item', 'renter']);

        $this->notifyRenter(
            rental: $rental,
            type: 'payment_confirmed',
            message: "Your payment for {$rental->item->name} has been confirmed."
        );
    }

    private function remainingBalance(Rental $rental): float
    {
        return max(0, round((float) $rental->total_price - (float) ($rental->paid_amount ?? 0), 2));
    }

    private function isUpcomingDue(Rental $rental): bool
    {
        return $rental->end_date->isToday()
            || ($rental->end_date->isFuture() && $rental->end_date->lte(now()->addDays(7)));
    }

    private function isOverdue(Rental $rental): bool
    {
        return $rental->end_date->isBefore(now()->startOfDay());
    }

    private function notifyRenter(Rental $rental, string $type, string $message): void
    {
        $rental->renter->notify(new RentalPaymentStatusNotification(
            rentalId: $rental->id,
            itemId: $rental->item->id,
            itemName: $rental->item->name,
            type: $type,
            message: $message,
            remainingBalance: $this->remainingBalance($rental),
            dueDate: $rental->end_date->toDateString(),
        ));
    }
}
