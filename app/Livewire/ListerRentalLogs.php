<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Rental;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ListerRentalLogs extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::user()?->isAdministrator(), 403);

        $items = Item::query()
            ->where('user_id', Auth::id())
            ->whereHas('rentals')
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $search = '%'.trim($this->search).'%';

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', $search)
                        ->orWhereHas('categoryRecord', fn (Builder $categoryQuery) => $categoryQuery->where('name', 'like', $search));
                });
            })
            ->with('categoryRecord')
            ->withCount([
                'rentals as rental_requests_count',
                'rentals as pending_requests_count' => fn (Builder $query) => $query->where('status', Rental::STATUS_PENDING),
                'rentals as managed_requests_count' => fn (Builder $query) => $query->whereIn('status', [Rental::STATUS_APPROVED, Rental::STATUS_ACTIVE]),
                'rentals as closed_requests_count' => fn (Builder $query) => $query->whereIn('status', [Rental::STATUS_COMPLETED, Rental::STATUS_CANCELLED]),
            ])
            ->withMax('rentals', 'created_at')
            ->orderBy('name')
            ->orderByDesc('rentals_max_created_at')
            ->paginate(10);

        return view('livewire.lister-rental-logs', [
            'items' => $items,
        ]);
    }
}
