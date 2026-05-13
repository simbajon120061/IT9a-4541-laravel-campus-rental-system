<?php

namespace App\Livewire;

use App\Models\Rental;
use App\Models\User;
use App\Notifications\RentalMessageSentNotification;
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

    public string $portalContext = 'all';

    public string $messageText = '';

    public function mount(): void
    {
        $this->portalContext = match (true) {
            request()->routeIs('renter.messages') => 'renter',
            request()->routeIs('lister.messages') => 'lister',
            session('active_portal') === 'renter' => 'renter',
            session('active_portal') === 'lister' => 'lister',
            default => 'all',
        };

        $rentalId = request('rental');

        if (is_numeric($rentalId) && $this->accessibleRentalsQuery()->whereKey((int) $rentalId)->exists()) {
            $this->selectedRentalId = (int) $rentalId;
            $this->markConversationAsRead((int) $rentalId);
        }
    }

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
        $this->markConversationAsRead($rentalId);
    }

    public function closeConversation(): void
    {
        $this->selectedRentalId = null;
        $this->reset('messageText');
    }

    public function sendMessage(): void
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::user()?->isAdministrator(), 403);
        abort_unless($this->selectedRentalId !== null, 404);

        $rental = $this->accessibleRentalsQuery()
            ->whereKey($this->selectedRentalId)
            ->with(['item.user', 'renter'])
            ->firstOrFail();

        $this->messageText = trim($this->messageText);

        $validated = $this->validate([
            'messageText' => ['required', 'string', 'max:40'],
        ]);

        $message = $rental->messages()->create([
            'sender_id' => Auth::id(),
            'body' => trim($validated['messageText']),
        ]);

        $recipient = $this->messageRecipient($rental);
        $recipient?->notify(new RentalMessageSentNotification(
            rentalId: $rental->id,
            itemId: $rental->item_id,
            itemName: $rental->item?->name ?? 'Rental item',
            senderName: Auth::user()->name,
            messageBody: $message->body,
        ));

        $this->reset('messageText');
    }

    public function render(): View
    {
        abort_unless(Auth::check(), 403);
        abort_if(Auth::user()?->isAdministrator(), 403);

        $conversationsQuery = $this->accessibleRentalsQuery()
            ->whereHas('messages')
            ->with(['item.user', 'renter', 'messages.sender'])
            ->withMax('messages', 'created_at');

        if ($this->selectedRentalId !== null) {
            $conversationsQuery->orWhere(function (Builder $query): void {
                $query->whereKey($this->selectedRentalId)
                    ->whereIn('id', $this->accessibleRentalsQuery()->select('id'));
            });
        }

        $conversations = $conversationsQuery->get()
            ->map(function (Rental $rental): Rental {
                $rental->setRelation('messages', $rental->messages->sortBy('created_at')->values());

                return $rental;
            });

        if ($this->unreadOnly) {
            $conversations = $conversations->filter(function (Rental $rental): bool {
                return $rental->messages->contains(function ($message): bool {
                    return (int) $message->sender_id !== (int) Auth::id()
                        && $message->read_at === null;
                });
            });
        }

        $conversations = (match ($this->sortBy) {
            'name' => $conversations->sortBy(fn (Rental $rental): string => $this->otherUserName($rental)),
            'item' => $conversations->sortBy(fn (Rental $rental): string => (string) $rental->item?->name),
            default => $conversations->sortByDesc('messages_max_created_at'),
        })->values();

        $selectedConversation = $conversations->firstWhere('id', $this->selectedRentalId);
        $this->selectedRentalId = $selectedConversation?->id;

        if ($selectedConversation) {
            $selectedConversation->load(['item.user', 'renter', 'messages.sender']);
            $selectedConversation->setRelation('messages', $selectedConversation->messages->sortBy('created_at')->values());
        }

        return view('livewire.messages-index', [
            'conversations' => $conversations,
            'selectedConversation' => $selectedConversation,
        ]);
    }

    private function accessibleRentalsQuery(): Builder
    {
        return $this->portalRentalsQuery()
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

    private function portalRentalsQuery(): Builder
    {
        return Rental::query()
            ->when(
                $this->portalContext === 'renter',
                fn (Builder $query) => $query->where('renter_id', Auth::id()),
                fn (Builder $query) => $query->when(
                    $this->portalContext === 'lister',
                    fn (Builder $query) => $query->whereHas('item', fn (Builder $itemQuery) => $itemQuery->where('user_id', Auth::id())),
                    fn (Builder $query) => $query->where(function (Builder $query): void {
                        $query->where('renter_id', Auth::id())
                            ->orWhereHas('item', fn (Builder $itemQuery) => $itemQuery->where('user_id', Auth::id()));
                    })
                )
            );
    }

    private function otherUserName(Rental $rental): string
    {
        $isOwner = (int) $rental->item?->user_id === (int) Auth::id();

        return (string) ($isOwner ? $rental->renter?->name : $rental->item?->user?->name);
    }

    private function messageRecipient(Rental $rental): ?User
    {
        $isOwner = (int) $rental->item?->user_id === (int) Auth::id();

        return $isOwner ? $rental->renter : $rental->item?->user;
    }

    private function markConversationAsRead(int $rentalId): void
    {
        $this->accessibleRentalsQuery()
            ->whereKey($rentalId)
            ->firstOrFail()
            ->messages()
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
