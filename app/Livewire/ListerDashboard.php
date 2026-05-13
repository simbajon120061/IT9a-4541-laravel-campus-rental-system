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
        $onProcessRentalsQuery = (clone $ownedRentalsQuery)
            ->where(function ($query) use ($now): void {
                $query->where('status', Rental::STATUS_APPROVED)
                    ->orWhere(function ($subQuery) use ($now): void {
                        $subQuery->where('status', Rental::STATUS_ACTIVE)
                            ->where('start_date', '>', $now);
                    });
            });
        $onProcessRentals = (clone $onProcessRentalsQuery)->count();
        $nextOnProcessRental = (clone $onProcessRentalsQuery)
            ->orderBy('start_date')
            ->first(['id', 'start_date']);
        $dueSoonRentalsQuery = (clone $ownedRentalsQuery)
            ->where('status', Rental::STATUS_ACTIVE)
            ->where('start_date', '<=', $now)
            ->whereBetween('end_date', [$now, $now->copy()->addDays(7)]);

        $dueSoonRentals = (clone $dueSoonRentalsQuery)->count();

        $nextDueRental = (clone $dueSoonRentalsQuery)
            ->orderBy('end_date')
            ->first(['id', 'end_date']);

        $daysUntilNextDue = $nextDueRental?->end_date
            ? max(0, (int) $now->copy()->startOfDay()->diffInDays($nextDueRental->end_date->copy()->startOfDay(), false))
            : null;

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
            'onProcessRentals' => $onProcessRentals,
            'nextOnProcessRental' => $nextOnProcessRental,
            'dueSoonRentals' => $dueSoonRentals,
            'daysUntilNextDue' => $daysUntilNextDue,
            'totalEarnings' => $totalEarnings,
            'recentRequests' => $recentRequests,
        ]);
    }
}
