<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;

class ItemFilter extends Component
{
    public const ITEMS_PER_BATCH = 12;

    public string $search = '';

    public string $category = '';

    public string $maxPrice = '';

    public string $sortBy = 'latest';

    public int $itemsToShow = self::ITEMS_PER_BATCH;

    public function mount(?int $selectedCategoryId = null): void
    {
        if ($selectedCategoryId !== null) {
            $this->category = (string) $selectedCategoryId;
        }
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetVisibleItems();
    }

    #[On('category-selected')]
    public function applyCategoryFilter(?int $categoryId = null): void
    {
        $this->category = $categoryId !== null ? (string) $categoryId : '';
        $this->resetVisibleItems();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->category = '';
        $this->maxPrice = '';
        $this->sortBy = 'latest';
        $this->resetVisibleItems();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'category', 'maxPrice', 'sortBy'], true)) {
            $this->resetVisibleItems();
        }
    }

    public function loadMore(): void
    {
        $this->itemsToShow += self::ITEMS_PER_BATCH;
    }

    private function resetVisibleItems(): void
    {
        $this->itemsToShow = self::ITEMS_PER_BATCH;
    }

    public function render()
    {
        $itemsQuery = Item::query()
            ->select(['id', 'user_id', 'name', 'description', 'price', 'status', 'category_id', 'image_path', 'created_at'])
            ->with(['categoryRecord', 'user'])
            ->visibleToPublic()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->category, fn ($q) => $q->where('category_id', $this->category))
            ->when($this->maxPrice, function (Builder $q): Builder {
                return match ($this->maxPrice) {
                    'under_50' => $q->where('price', '<', 50),
                    'under_100' => $q->where('price', '<', 100),
                    'under_200' => $q->where('price', '<', 200),
                    'under_500' => $q->where('price', '<', 500),
                    default => $q
                };
            })
            ->when($this->sortBy, function (Builder $q): Builder {
                return match ($this->sortBy) {
                    'latest' => $q->orderByDesc('created_at')->orderByDesc('id'),
                    'oldest' => $q->orderBy('created_at')->orderBy('id'),
                    'price_asc' => $q->orderBy('price', 'asc')->orderByDesc('id'),
                    'price_desc' => $q->orderBy('price', 'desc')->orderByDesc('id'),
                    default => $q->orderByDesc('created_at')->orderByDesc('id')
                };
            })
            ->available();

        $totalItems = (clone $itemsQuery)->count();

        $items = $itemsQuery
            ->take($this->itemsToShow)
            ->get();

        return view('livewire.item-filter', [
            'items' => $items,
            'totalItems' => $totalItems,
            'canLoadMore' => $totalItems > $items->count(),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
