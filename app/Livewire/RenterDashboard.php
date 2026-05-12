<?php

namespace App\Livewire;

use App\Models\Rental;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class RenterDashboard extends Component
{
    public function render(): View
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::user()?->isAdministrator(), 403);

        $rentalsQuery = Rental::query()
            ->where('renter_id', Auth::id())
            ->with(['item:id,name,user_id', 'item.user:id,name']);

        $now = now();

        $activeRentals = (clone $rentalsQuery)
            ->where('status', Rental::STATUS_ACTIVE)
            ->where('start_date', '<=', $now)
            ->count();

        $pendingRequests = (clone $rentalsQuery)
            ->where('status', Rental::STATUS_PENDING)
            ->count();

        $dueSoonRentals = (clone $rentalsQuery)
            ->where('status', Rental::STATUS_ACTIVE)
            ->where('start_date', '<=', $now)
            ->whereBetween('end_date', [$now, $now->copy()->addDays(7)])
            ->count();

        $approvedRentals = (clone $rentalsQuery)
            ->where(function ($query): void {
                $query->where('status', Rental::STATUS_APPROVED)
                    ->orWhere(function ($futureActiveQuery): void {
                        $futureActiveQuery->where('status', Rental::STATUS_ACTIVE)
                            ->where('start_date', '>', now());
                    });
            })
            ->count();

        $recentRentals = (clone $rentalsQuery)
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('livewire.renter-dashboard', [
            'activeRentals' => $activeRentals,
            'pendingRequests' => $pendingRequests,
            'dueSoonRentals' => $dueSoonRentals,
            'approvedRentals' => $approvedRentals,
            'recentRentals' => $recentRentals,
        ]);
    }
}
