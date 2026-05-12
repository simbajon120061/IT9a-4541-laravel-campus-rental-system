<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationLinkIconsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_navigation_links_include_icons(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('Browse Categories')
            ->assertDontSee('Search Items')
            ->assertSee('M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', false)
            ->assertSee('M8 7V3m8 4V3m-9 8h10', false)
            ->assertDontSee('My Listings');
    }

    public function test_homepage_shows_profile_dropdown_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Manage Account')
            ->assertSee('group inline-flex items-center rounded-full px-1 py-1 ring-1 ring-transparent transition hover:ring-slate-200 dark:hover:ring-slate-700', false)
            ->assertSee('M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938', false);
    }

    public function test_lister_navigation_menu_links_include_icons(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('my-listings'))
            ->assertOk()
            ->assertSee('M8 7V3m8 4V3m-9 8h10', false)
            ->assertSee('M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2', false);
    }

    public function test_lister_navigation_has_top_bar_and_sidebar_toggle(): void
    {
        $user = User::factory()->create([
            'name' => 'Juan Dela Cruz',
        ]);

        $this->actingAs($user)
            ->get(route('lister.dashboard'))
            ->assertOk()
            ->assertSee('sidebarCollapsed', false)
            ->assertSee('Collapse sidebar')
            ->assertSee('Expand sidebar')
            ->assertSee('Toggle dark mode')
            ->assertSee('Profile Settings')
            ->assertSee('Juan Dela Cruz');
    }

    public function test_lister_profile_link_preserves_lister_navigation_context(): void
    {
        $user = User::factory()->create([
            'name' => 'Juan Dela Cruz',
        ]);

        $this->actingAs($user)
            ->get(route('lister.dashboard'))
            ->assertOk()
            ->assertSee(route('profile.show', ['portal' => 'lister']), false);

        $this->actingAs($user)
            ->get(route('profile.show', ['portal' => 'lister']))
            ->assertOk()
            ->assertSee('Lister Dashboard')
            ->assertSee('My Listings')
            ->assertDontSee('Renter Dashboard');
    }

    public function test_item_page_highlights_marketplace_instead_of_my_listings(): void
    {
        $owner = User::factory()->create();
        $renter = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Clothing',
            'slug' => 'clothing-navigation-test',
            'icon' => 'shirt',
            'is_active' => true,
        ]);

        $item = Item::query()->create([
            'user_id' => $owner->id,
            'name' => 'Light Gray Suit',
            'description' => 'Formal suit',
            'price' => 300,
            'condition' => 'Good',
            'status' => 'available',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($renter)
            ->get(route('item.view', $item->id))
            ->assertOk();

        $content = $response->getContent();

        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('renter.marketplace'), '/').'" class="[^"]*bg-gradient-to-r[^"]*">[\s\S]*?Marketplace/',
            $content
        );

        $this->assertStringNotContainsString('My Listings', $content);
    }
}
