<?php

namespace Tests\Feature;

use App\Livewire\ItemFilter;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ItemFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_clear_search_resets_search_value(): void
    {
        Livewire::test(ItemFilter::class)
            ->set('search', 'calculator')
            ->call('clearSearch')
            ->assertSet('search', '');
    }

    public function test_selected_category_id_applies_initial_item_filter(): void
    {
        $user = User::factory()->create();

        $booksCategory = Category::query()->create([
            'name' => 'Books',
            'slug' => 'books-test-selected-category',
            'icon' => 'book',
            'is_active' => true,
        ]);

        $toolsCategory = Category::query()->create([
            'name' => 'Tools',
            'slug' => 'tools-test-selected-category',
            'icon' => 'tool',
            'is_active' => true,
        ]);

        Item::query()->create([
            'user_id' => $user->id,
            'name' => 'Engineering Calculator',
            'description' => 'Scientific calculator',
            'price' => 40,
            'condition' => 'Good',
            'status' => 'available',
            'category' => $booksCategory->slug,
            'category_id' => $booksCategory->id,
        ]);

        Item::query()->create([
            'user_id' => $user->id,
            'name' => 'Hammer Set',
            'description' => 'Basic hammer set',
            'price' => 75,
            'condition' => 'Good',
            'status' => 'available',
            'category' => $toolsCategory->slug,
            'category_id' => $toolsCategory->id,
        ]);

        Livewire::test(ItemFilter::class, ['selectedCategoryId' => $booksCategory->id])
            ->assertSet('category', (string) $booksCategory->id)
            ->assertSee('Engineering Calculator')
            ->assertDontSee('Hammer Set');
    }

    public function test_apply_category_filter_updates_category_state(): void
    {
        Livewire::test(ItemFilter::class)
            ->call('applyCategoryFilter', 5)
            ->assertSet('category', '5')
            ->call('applyCategoryFilter', null)
            ->assertSet('category', '');
    }

    public function test_show_more_loads_more_than_initial_twelve_items(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Electronics',
            'slug' => 'electronics-show-more-test',
            'icon' => 'device',
            'is_active' => true,
        ]);

        for ($index = 1; $index <= 13; $index++) {
            Item::query()->create([
                'user_id' => $user->id,
                'name' => sprintf('Item %02d', $index),
                'description' => 'Demo item description',
                'price' => $index,
                'condition' => 'Good',
                'status' => 'available',
                'category_id' => $category->id,
            ]);
        }

        Livewire::test(ItemFilter::class)
            ->assertSee('Item 13')
            ->assertDontSee('Item 01')
            ->call('loadMore')
            ->assertSee('Item 01')
            ->assertSet('itemsToShow', 24);
    }

    public function test_restricted_user_listings_are_hidden_from_marketplace_filters(): void
    {
        $activeUser = User::factory()->create();
        $restrictedUser = User::factory()->create([
            'restricted_at' => now(),
        ]);
        $category = Category::query()->create([
            'name' => 'Electronics',
            'slug' => 'electronics-restricted-user-test',
            'icon' => 'device',
            'is_active' => true,
        ]);

        Item::query()->create([
            'user_id' => $activeUser->id,
            'name' => 'Visible Calculator',
            'description' => 'Available item',
            'price' => 40,
            'condition' => 'Good',
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        Item::query()->create([
            'user_id' => $restrictedUser->id,
            'name' => 'Hidden Calculator',
            'description' => 'Restricted owner item',
            'price' => 50,
            'condition' => 'Good',
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        Livewire::test(ItemFilter::class)
            ->assertSee('Visible Calculator')
            ->assertDontSee('Hidden Calculator');
    }
}
