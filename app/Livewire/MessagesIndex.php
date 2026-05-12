<?php

namespace App\Livewire;

use App\Models\Rental;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MessagesIndex extends Component
{
    public string $search = '';

    public bool $unreadOnly = false;

    public string $sortBy = 'date';

    public ?int $selectedRentalId = null;

    public function updatedSearch(): void
    {
        $this->selectedRentalId = null;
    }

    public function updatedUnreadOnly(): void
    {
        $this->selectedRentalId = null;
    }

    public function updatedSortBy(): void
    {
        if (! in_array($this->sortBy, ['date', 'name', 'item'], true)) {
            $this->sortBy = 'date';
        }
    }

    public function selectConversation(int $rentalId): void
    {
        abort_unless($this->accessibleRentalsQuery()->whereKey($rentalId)->exists(), 403);

        $this->selectedRentalId = $rentalId;
    }

    public function render(): View
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::user()?->isAdministrator(), 403);

        $conversations = $this->accessibleRentalsQuery()
            ->whereHas('messages')
            ->with(['item.user', 'renter', 'messages.sender'])
            ->withMax('messages', 'created_at')
            ->get()
            ->map(function (Rental $rental): Rental {
                $rental->setRelation('messages', $rental->messages->sortBy('created_at')->values());

                return $rental;
            });

        if ($this->unreadOnly) {
            $conversations = $conversations->filter(function (Rental $rental): bool {
                return (int) $rental->messages->last()?->sender_id !== (int) Auth::id();
            });
        }

        $conversations = (match ($this->sortBy) {
            'name' => $conversations->sortBy(fn (Rental $rental): string => $this->otherUserName($rental)),
            'item' => $conversations->sortBy(fn (Rental $rental): string => (string) $rental->item?->name),
            default => $conversations->sortByDesc('messages_max_created_at'),
        })->values();

        $selectedConversation = $conversations->firstWhere('id', $this->selectedRentalId) ?? $conversations->first();
        $this->selectedRentalId = $selectedConversation?->id;

        return view('livewire.messages-index', [
            'conversations' => $conversations,
            'selectedConversation' => $selectedConversation,
        ]);
    }

    private function accessibleRentalsQuery(): Builder
    {
        return Rental::query()
            ->where(function (Builder $query): void {
                $query->where('renter_id', Auth::id())
                    ->orWhereHas('item', fn (Builder $itemQuery) => $itemQuery->where('user_id', Auth::id()));
            })
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $search = '%'.trim($this->search).'%';

                $query->where(function (Builder $query) use ($search): void {
                    $query->whereHas('messages', function (Builder $messageQuery) use ($search): void {
                        $messageQuery->where('body', 'like', $search)
                            ->orWhereHas('sender', fn (Builder $senderQuery) => $senderQuery->where('name', 'like', $search));
                    })
                        ->orWhereHas('item', fn (Builder $itemQuery) => $itemQuery->where('name', 'like', $search))
                        ->orWhereHas('renter', fn (Builder $renterQuery) => $renterQuery->where('name', 'like', $search))
                        ->orWhereHas('item.user', fn (Builder $ownerQuery) => $ownerQuery->where('name', 'like', $search));
                });
            });
    }

    private function otherUserName(Rental $rental): string
    {
        $isOwner = (int) $rental->item?->user_id === (int) Auth::id();

        return (string) ($isOwner ? $rental->renter?->name : $rental->item?->user?->name);
    }
}
