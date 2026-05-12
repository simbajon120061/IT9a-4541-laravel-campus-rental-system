<?php

namespace App\Livewire;

use App\Models\Rental;
use App\Notifications\RentalRequestDecisionNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class RentInventoryManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = 'all';

    /** @var array<int, mixed> */
    public array $paymentAmounts = [];

    public function applySearch(): void
    {
        $this->search = trim($this->search);
        $this->resetPage();
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

    public function approveRequest(int $rentalId): void
    {
        abort_unless(Auth::check(), 403);

        $rental = Rental::query()
            ->whereHas('item', fn ($query) => $query->where('user_id', Auth::id()))
            ->where('status', Rental::STATUS_PENDING)
            ->findOrFail($rentalId);

        $rental->update([
            'status' => Rental::STATUS_APPROVED,
            'approved_at' => now(),
            'cancelled_at' => null,
        ]);
        $rental->renter->notify(new RentalRequestDecisionNotification(
            rentalId: $rental->id,
            itemId: $rental->item->id,
            itemName: $rental->item->name,
            decision: 'approved',
        ));

        session()->flash('message', 'Rental request approved.');
    }

    public function rejectRequest(int $rentalId): void
    {
        abort_unless(Auth::check(), 403);

        $rental = Rental::query()
            ->whereHas('item', fn ($query) => $query->where('user_id', Auth::id()))
            ->where('status', Rental::STATUS_PENDING)
            ->findOrFail($rentalId);

        $rental->update([
            'status' => Rental::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
        $rental->renter->notify(new RentalRequestDecisionNotification(
            rentalId: $rental->id,
            itemId: $rental->item->id,
            itemName: $rental->item->name,
            decision: 'rejected',
        ));

        session()->flash('message', 'Rental request rejected.');
    }

    public function recordPayment(int $rentalId): void
    {
        abort_unless(Auth::check(), 403);

        $rental = Rental::query()
            ->whereHas('item', fn ($query) => $query->where('user_id', Auth::id()))
            ->whereIn('status', [Rental::STATUS_APPROVED, Rental::STATUS_ACTIVE])
            ->findOrFail($rentalId);

        $remainingBalance = $this->remainingBalance($rental);

        $this->validate([
            "paymentAmounts.$rentalId" => ['required', 'numeric', 'gt:0', 'max:'.$remainingBalance],
        ], [
            "paymentAmounts.$rentalId.max" => 'The payment must not exceed the remaining balance.',
        ]);

        $paymentAmount = (float) ($this->paymentAmounts[$rentalId] ?? 0);

        $rental->applyPayment($paymentAmount);

        $this->paymentAmounts[$rentalId] = '';
        session()->flash('message', 'Payment recorded successfully.');
    }

    public function fillFullPaymentAmount(int $rentalId): void
    {
        abort_unless(Auth::check(), 403);

        $rental = Rental::query()
            ->whereHas('item', fn ($query) => $query->where('user_id', Auth::id()))
            ->whereIn('status', [Rental::STATUS_APPROVED, Rental::STATUS_ACTIVE])
            ->findOrFail($rentalId);

        $this->paymentAmounts[$rentalId] = number_format($this->remainingBalance($rental), 2, '.', '');
    }

    public function markAsRented(int $rentalId): void
    {
        abort_unless(Auth::check(), 403);

        $rental = Rental::query()
            ->whereHas('item', fn ($query) => $query->where('user_id', Auth::id()))
            ->whereIn('status', [Rental::STATUS_APPROVED, Rental::STATUS_ACTIVE])
            ->findOrFail($rentalId);

        if ($rental->start_date->isFuture()) {
            session()->flash('message', 'Rental cannot be marked as Rented before the start date.');

            return;
        }

        $rental->update([
            'status' => Rental::STATUS_ACTIVE,
            'active_at' => now(),
            'cancelled_at' => null,
        ]);
        $rental->item->update(['status' => 'rented']);

        session()->flash('message', 'Rental status updated to Rented.');
    }

    public function render(): View
    {
        abort_unless(Auth::check(), 403);

        $baseQuery = Rental::query()
            ->whereHas('item', fn ($query) => $query->where('user_id', Auth::id()))
            ->whereIn('status', [Rental::STATUS_PENDING, Rental::STATUS_APPROVED, Rental::STATUS_ACTIVE])
            ->with(['item.categoryRecord', 'renter']);

        if ($this->search !== '') {
            $search = '%'.trim($this->search).'%';
            $baseQuery->where(function ($query) use ($search): void {
                $query->whereHas('item', fn ($itemQuery) => $itemQuery->where('name', 'like', $search))
                    ->orWhereHas('renter', fn ($renterQuery) => $renterQuery->where('name', 'like', $search));
            });
        }

        $allCount = (clone $baseQuery)->count();
        $dueSoonCount = (clone $baseQuery)
            ->where('status', Rental::STATUS_ACTIVE)
            ->where('start_date', '<=', now())
            ->whereBetween('end_date', [now(), now()->addDays(7)])
            ->count();
        $activeCount = (clone $baseQuery)
            ->where('status', Rental::STATUS_ACTIVE)
            ->where('start_date', '<=', now())
            ->count();
        $pendingCount = (clone $baseQuery)->where('status', Rental::STATUS_PENDING)->count();
        $approvedCount = (clone $baseQuery)
            ->where(function ($query): void {
                $query->where('status', Rental::STATUS_APPROVED)
                    ->orWhere(function ($subQuery): void {
                        $subQuery->where('status', Rental::STATUS_ACTIVE)
                            ->where('start_date', '>', now());
                    });
            })
            ->count();

        $rentalsQuery = clone $baseQuery;

        if ($this->filterStatus === 'due_soon') {
            $rentalsQuery->where('status', Rental::STATUS_ACTIVE)
                ->where('start_date', '<=', now())
                ->whereBetween('end_date', [now(), now()->addDays(7)]);
        } elseif ($this->filterStatus === 'active') {
            $rentalsQuery->where('status', Rental::STATUS_ACTIVE)
                ->where('start_date', '<=', now());
        } elseif ($this->filterStatus === 'pending') {
            $rentalsQuery->where('status', Rental::STATUS_PENDING);
        } elseif ($this->filterStatus === 'approved') {
            $rentalsQuery->where(function ($query): void {
                $query->where('status', Rental::STATUS_APPROVED)
                    ->orWhere(function ($subQuery): void {
                        $subQuery->where('status', Rental::STATUS_ACTIVE)
                            ->where('start_date', '>', now());
                    });
            });
        }

        $rentals = $rentalsQuery
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('end_date')
            ->paginate(10);

        return view('livewire.rent-inventory-management', [
            'rentals' => $rentals,
            'allCount' => $allCount,
            'dueSoonCount' => $dueSoonCount,
            'activeCount' => $activeCount,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
        ]);
    }

    private function remainingBalance(Rental $rental): float
    {
        return max(0, round((float) $rental->total_price - (float) ($rental->paid_amount ?? 0), 2));
    }
}
