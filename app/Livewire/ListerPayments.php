<?php

namespace App\Livewire;

use App\Models\Rental;
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
        ], true)) {
            $this->filterStatus = $filter;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setFilter(string $status): void
    {
        $this->filterStatus = $status;
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
        ]);
    }

    private function paymentRentalQuery(): Builder
    {
        return Rental::query()
            ->whereHas('item', fn ($query) => $query->where('user_id', Auth::id()))
            ->where('status', '!=', Rental::STATUS_CANCELLED);
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
    }

    private function remainingBalance(Rental $rental): float
    {
        return max(0, round((float) $rental->total_price - (float) ($rental->paid_amount ?? 0), 2));
    }
}
