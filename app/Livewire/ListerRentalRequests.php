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
class ListerRentalRequests extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function approveRequest(int $rentalId): void
    {
        abort_unless(Auth::check(), 403);

        $rental = Rental::query()
            ->whereHas('item', fn ($query) => $query->where('user_id', Auth::id()))
            ->where('status', Rental::STATUS_PENDING)
            ->with(['item', 'renter'])
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
            ->with(['item', 'renter'])
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

    public function render(): View
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::user()?->isAdministrator(), 403);

        $baseQuery = Rental::query()
            ->whereHas('item', fn ($query) => $query->where('user_id', Auth::id()))
            ->with(['item.categoryRecord', 'renter']);

        if ($this->search !== '') {
            $search = '%'.trim($this->search).'%';
            $baseQuery->where(function ($query) use ($search): void {
                $query->whereHas('item', fn ($itemQuery) => $itemQuery->where('name', 'like', $search))
                    ->orWhereHas('renter', fn ($renterQuery) => $renterQuery->where('name', 'like', $search));
            });
        }

        $requestsQuery = (clone $baseQuery)
            ->where('status', Rental::STATUS_PENDING)
            ->latest('created_at');

        return view('livewire.lister-rental-requests', [
            'requests' => $requestsQuery->paginate(10),
            'pendingCount' => (clone $requestsQuery)->count(),
            'managedCount' => (clone $baseQuery)
                ->whereIn('status', [Rental::STATUS_APPROVED, Rental::STATUS_ACTIVE])
                ->count(),
        ]);
    }
}
