<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class HomePage extends Component
{
    public ?Category $selectedCategory = null;

    public function mount(?Category $category = null): void
    {
        if (! $category) {
            $this->selectedCategory = null;

            return;
        }

        $this->selectedCategory = Category::query()
            ->where('is_active', true)
            ->find($category->id);

        $this->dispatch('category-selected', categoryId: $this->selectedCategory?->id);
    }

    public function selectCategory(int $categoryId): void
    {
        if ($this->selectedCategory?->id === $categoryId) {
            $this->clearCategorySelection();

            return;
        }

        $selectedCategory = Category::query()
            ->where('is_active', true)
            ->find($categoryId);

        $this->selectedCategory = $selectedCategory;
        $this->dispatch('category-selected', categoryId: $this->selectedCategory?->id);
    }

    public function clearCategorySelection(): void
    {
        $this->selectedCategory = null;
        $this->dispatch('category-selected', categoryId: null);
    }

    public function render(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount([
                'items as available_items_count' => fn ($query) => $query->available()->visibleToPublic(),
            ])
            ->orderBy('name')
            ->get();

        $featuredItemsQuery = Item::query()
            ->available()
            ->visibleToPublic()
            ->select(['id', 'user_id', 'name', 'description', 'price', 'status', 'category_id', 'image_path', 'created_at'])
            ->with(['user', 'categoryRecord'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($this->selectedCategory) {
            $featuredItemsQuery->where('category_id', $this->selectedCategory->id);
        }

        $featuredItems = $featuredItemsQuery
            ->take(10)
            ->get();

        return view('livewire.home-page', [
            'categories' => $categories,
            'featuredItems' => $featuredItems,
        ]);
    }
}
