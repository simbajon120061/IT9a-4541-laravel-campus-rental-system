<?php

namespace App\Livewire;

use App\Models\Rental;
use Illuminate\Contracts\View\View;
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setFilter(string $status): void
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function recordPayment(int $rentalId): void
    {
        abort_unless(Auth::check(), 403);

        $rental = Rental::query()
            ->whereHas('item', fn ($query) => $query->where('user_id', Auth::id()))
            ->whereIn('status', [Rental::STATUS_APPROVED, Rental::STATUS_ACTIVE, Rental::STATUS_COMPLETED])
            ->findOrFail($rentalId);

        $this->validate([
            "paymentAmounts.$rentalId" => ['required', 'numeric', 'gt:0'],
        ]);

        $rental->applyPayment((float) ($this->paymentAmounts[$rentalId] ?? 0));

        $this->paymentAmounts[$rentalId] = '';
        session()->flash('message', 'Payment recorded successfully.');
    }

    public function render(): View
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::user()?->isAdministrator(), 403);

        $paymentsQuery = Rental::query()
            ->whereHas('item', fn ($query) => $query->where('user_id', Auth::id()))
            ->where('status', '!=', Rental::STATUS_CANCELLED)
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

        $summaryQuery = Rental::query()
            ->whereHas('item', fn ($query) => $query->where('user_id', Auth::id()))
            ->where('status', '!=', Rental::STATUS_CANCELLED);

        return view('livewire.lister-payments', [
            'payments' => $paymentsQuery->paginate(10),
            'outstandingCount' => (clone $summaryQuery)->where('payment_status', Rental::PAYMENT_STATUS_OUTSTANDING)->count(),
            'partialCount' => (clone $summaryQuery)->where('payment_status', Rental::PAYMENT_STATUS_PARTIAL)->count(),
            'paidCount' => (clone $summaryQuery)->where('payment_status', Rental::PAYMENT_STATUS_FULLY_PAID)->count(),
            'totalPaid' => (float) (clone $summaryQuery)->sum('paid_amount'),
        ]);
    }
}
