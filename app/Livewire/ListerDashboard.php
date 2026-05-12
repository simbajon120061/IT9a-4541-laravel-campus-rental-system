<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Rental;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ListerDashboard extends Component
{
    public function render(): View
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::user()?->isAdministrator(), 403);

        $userId = Auth::id();
        $now = now();

        $itemsQuery = Item::query()->where('user_id', $userId);
        $ownedRentalsQuery = Rental::query()
            ->whereHas('item', fn ($query) => $query->where('user_id', $userId));

        $totalListings = (clone $itemsQuery)->count();
        $availableListings = (clone $itemsQuery)->where('status', 'available')->count();
        $rentedListings = (clone $itemsQuery)->where('status', 'rented')->count();
        $pendingRequests = (clone $ownedRentalsQuery)->where('status', Rental::STATUS_PENDING)->count();
        $dueSoonRentals = (clone $ownedRentalsQuery)
            ->where('status', Rental::STATUS_ACTIVE)
            ->where('start_date', '<=', $now)
            ->whereBetween('end_date', [$now, $now->copy()->addDays(7)])
            ->count();

        $totalEarnings = (float) (clone $ownedRentalsQuery)
            ->where('status', '!=', Rental::STATUS_CANCELLED)
            ->sum('paid_amount');

        $recentRequests = (clone $ownedRentalsQuery)
            ->with(['item:id,name,user_id', 'renter:id,name'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('livewire.lister-dashboard', [
            'totalListings' => $totalListings,
            'availableListings' => $availableListings,
            'rentedListings' => $rentedListings,
            'pendingRequests' => $pendingRequests,
            'dueSoonRentals' => $dueSoonRentals,
            'totalEarnings' => $totalEarnings,
            'recentRequests' => $recentRequests,
        ]);
    }
}
