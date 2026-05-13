<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Rental;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class OwnerItemRentalRequests extends Component
{
    public Item $item;

    public function mount(Item $item): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless((int) $item->user_id === (int) Auth::id(), 403);

        $this->item = $item;
    }

    public function render(): View
    {
        $requests = Rental::query()
            ->where('item_id', $this->item->id)
            ->with('renter')
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->get();

        $pendingCount = $requests->where('status', Rental::STATUS_PENDING)->count();

        return view('livewire.owner-item-rental-requests', [
            'requests' => $requests,
            'pendingCount' => $pendingCount,
            'managedCount' => $requests
                ->whereIn('status', [Rental::STATUS_APPROVED, Rental::STATUS_ACTIVE])
                ->count(),
        ]);
    }
}
