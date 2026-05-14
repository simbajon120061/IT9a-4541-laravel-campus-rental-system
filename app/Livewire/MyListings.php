<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemAppeal;
use App\Models\User;
use App\Notifications\AdminReviewQueueNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class MyListings extends Component
{
    use WithPagination;

    public string $search = '';

    public string $availability = '';

    public string $categoryId = '';

    public string $sortBy = 'newest';

    public bool $showDeleteModal = false;

    public ?int $pendingDeleteItemId = null;

    public string $pendingDeleteItemName = '';

    public ?int $pendingAppealItemId = null;

    public string $pendingAppealItemName = '';

    public string $appealReason = '';

    public string $appealDetails = '';

    public function confirmDeleteItem(int $itemId): void
    {
        abort_unless(Auth::check(), 403);

        $item = Item::where('user_id', Auth::id())->findOrFail($itemId);

        $this->pendingDeleteItemId = $item->id;
        $this->pendingDeleteItemName = $item->name;
        $this->showDeleteModal = true;
    }

    public function cancelDeleteItem(): void
    {
        $this->resetDeleteModalState();
    }

    public function deleteItem(?int $itemId = null): void
    {
        abort_unless(Auth::check(), 403);

        $targetItemId = $itemId ?? $this->pendingDeleteItemId;

        if (! $targetItemId) {
            return;
        }

        $item = Item::where('user_id', Auth::id())->findOrFail($targetItemId);

        $item->delete();

        $this->resetPage();
        $this->resetDeleteModalState();

        session()->flash('message', 'Item deleted successfully.');
    }

    private function resetDeleteModalState(): void
    {
        $this->showDeleteModal = false;
        $this->pendingDeleteItemId = null;
        $this->pendingDeleteItemName = '';
    }

    public function openAppeal(int $itemId): void
    {
        abort_unless(Auth::check(), 403);

        $item = Item::withTrashed()
            ->where('user_id', Auth::id())
            ->whereNotNull('admin_removed_at')
            ->findOrFail($itemId);

        if ($item->latestAppeal?->status === ItemAppeal::STATUS_PENDING) {
            session()->flash('message', 'This item already has a pending appeal.');

            return;
        }

        $this->pendingAppealItemId = $item->id;
        $this->pendingAppealItemName = $item->name;
        $this->appealReason = '';
        $this->appealDetails = '';
        $this->resetValidation();
    }

    public function cancelAppeal(): void
    {
        $this->reset(['pendingAppealItemId', 'pendingAppealItemName', 'appealReason', 'appealDetails']);
        $this->resetValidation();
    }

    public function submitAppeal(): void
    {
        abort_unless(Auth::check(), 403);

        $validated = $this->validate([
            'pendingAppealItemId' => ['required', 'integer'],
            'appealReason' => ['required', 'string', 'max:150'],
            'appealDetails' => ['nullable', 'string', 'max:1000'],
        ]);

        $item = Item::withTrashed()
            ->where('user_id', Auth::id())
            ->whereNotNull('admin_removed_at')
            ->findOrFail((int) $validated['pendingAppealItemId']);

        $hasPendingAppeal = $item->appeals()
            ->where('status', ItemAppeal::STATUS_PENDING)
            ->exists();

        if ($hasPendingAppeal) {
            session()->flash('message', 'This item already has a pending appeal.');
            $this->cancelAppeal();

            return;
        }

        $appeal = $item->appeals()->create([
            'user_id' => Auth::id(),
            'reason' => trim($validated['appealReason']),
            'details' => trim((string) $validated['appealDetails']) ?: null,
        ]);

        User::query()
            ->where('is_admin', true)
            ->get()
            ->each
            ->notify(new AdminReviewQueueNotification(
                title: 'New complaint submitted',
                message: "{$appeal->user?->name} submitted an appeal for {$item->name}.",
                appealId: $appeal->id,
            ));

        $this->cancelAppeal();
        session()->flash('message', 'Appeal submitted for admin review.');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingAvailability(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatingSortBy(): void
    {
        $this->resetPage();
    }

    private function applySorting(Builder $itemsQuery): void
    {
        if ($this->sortBy === 'oldest') {
            $itemsQuery->orderBy('created_at');

            return;
        }

        if ($this->sortBy === 'name_asc') {
            $itemsQuery->orderBy('name');

            return;
        }

        if ($this->sortBy === 'name_desc') {
            $itemsQuery->orderByDesc('name');

            return;
        }

        if ($this->sortBy === 'price_low_high') {
            $itemsQuery->orderBy('price')->orderByDesc('created_at');

            return;
        }

        if ($this->sortBy === 'price_high_low') {
            $itemsQuery->orderByDesc('price')->orderByDesc('created_at');

            return;
        }

        $itemsQuery->orderByDesc('created_at');
    }

    public function render(): View
    {
        abort_unless(Auth::check(), 403);

        $itemsQuery = Item::withTrashed()
            ->where('user_id', Auth::id())
            ->where(function (Builder $query): void {
                $query->whereNull('deleted_at')
                    ->orWhereNotNull('admin_removed_at');
            })
            ->with(['latestRental', 'latestAppeal'])
            ->withCount('rentals');

        if ($this->search !== '') {
            $searchTerm = '%'.trim($this->search).'%';
            $itemsQuery->where(function ($query) use ($searchTerm): void {
                $query->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        if (in_array($this->availability, ['available', 'rented'], true)) {
            $itemsQuery->where('status', $this->availability);
        }

        if ($this->categoryId !== '') {
            $itemsQuery->where('category_id', (int) $this->categoryId);
        }

        $this->applySorting($itemsQuery);

        $items = $itemsQuery->paginate(10);

        $categories = Category::query()
            ->where('is_active', true)
            ->whereHas('items', fn ($query) => $query->withTrashed()->where('user_id', Auth::id()))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.my-listings', [
            'items' => $items,
            'categories' => $categories,
        ]);
    }
}
