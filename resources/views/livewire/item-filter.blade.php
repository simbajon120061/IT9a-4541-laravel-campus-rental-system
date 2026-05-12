<div>
    <div class="grid gap-6 xl:grid-cols-[18rem_minmax(0,1fr)]">
        <aside class="xl:sticky xl:top-24 xl:h-fit">
            <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-5 shadow-lg shadow-slate-900/5 backdrop-blur-xl dark:border-slate-700/70 dark:bg-slate-900/75">
                
                <div class="mb-4">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Search</label>
                    <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-700 dark:bg-slate-800">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0Z"/>
                        </svg>
                        <input
                            wire:model.live.debounce.300ms="search"
                            type="text"
                            placeholder="Search items..."
                            class="w-full border-0 bg-transparent text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0 dark:text-slate-100"
                        >
                        @if ($search !== '')
                            <button
                                type="button"
                                wire:click="clearSearch"
                                class="inline-flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold text-slate-500 transition hover:bg-slate-200 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100"
                                aria-label="Clear search"
                            >
                                &times;
                            </button>
                        @endif
                    </div>
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Category</label>
                    <select
                        wire:model.live="category"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Max Price (PHP)</label>
                    <select
                        wire:model.live="maxPrice"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                        <option value="">Any Price</option>
                        <option value="under_50">Under &#8369;50</option>
                        <option value="under_100">Under &#8369;100</option>
                        <option value="under_200">Under &#8369;200</option>
                        <option value="under_500">Under &#8369;500</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sort By</label>
                    <select
                        wire:model.live="sortBy"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                        <option value="latest">Recently Added</option>
                        <option value="oldest">Oldest First</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                    </select>
                </div>

                <button
                    wire:click="resetFilters"
                    class="w-full rounded-xl bg-gradient-to-r from-blue-600 to-violet-600 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-500/20 transition-all hover:brightness-110 hover:shadow-lg hover:shadow-blue-500/30 active:scale-[0.98]"
                >
                    Reset Filters
                </button>
            </div>
        </aside>

        <div>
            <div class="mb-8 flex items-center justify-between">
                
                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">
                    Showing {{ $items->count() }} of {{ $totalItems }} Items Available
                </span>
            </div>

            @if ($items->isEmpty())
                <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-20 text-center dark:border-slate-600 dark:bg-slate-800">
                    <svg class="mx-auto mb-4 h-16 w-16 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-slate-50">No items available yet</h3>
                    <p class="mt-2 text-slate-500 dark:text-slate-400">Be the first to list something useful for other students.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                    @foreach ($items as $item)
                        <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-slate-700 dark:bg-slate-900 dark:shadow-slate-900/50 dark:hover:shadow-slate-900/70">
                            <div class="relative aspect-[4/3] overflow-hidden bg-slate-100 dark:bg-slate-800">
                                @if ($item->imageUrl())
                                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-110">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700">
                                        <svg class="h-10 w-10 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 transition duration-300 group-hover:opacity-100"></div>
                            </div>

                            <div class="space-y-3 p-4">
                                <div>
                                    <h3 class="line-clamp-1 text-sm font-bold text-slate-900 dark:text-slate-50">{{ $item->name }}</h3>
                                    <p class="mt-1 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">{{ $item->description ?: 'No description provided.' }}</p>
                                </div>

                                <div class="flex items-end justify-between border-t border-slate-200 pt-2 dark:border-slate-700">
                                    <div>
                                        <div class="bg-gradient-to-r from-blue-600 to-violet-600 bg-clip-text text-lg font-bold text-transparent">&#8369;{{ number_format($item->price, 2) }}</div>
                                        <div class="text-[11px] text-slate-400 dark:text-slate-500">per day</div>
                                    </div>
                                    <div class="text-right text-[11px] text-slate-400 dark:text-slate-500">
                                        <div>{{ $item->categoryName() }}</div>
                                        <div class="font-medium">{{ ucfirst($item->status) }}</div>
                                    </div>
                                </div>

                                @auth
                                    <a href="{{ route('item.view', $item->id) }}" class="block rounded-lg bg-gradient-to-r from-blue-600 to-violet-600 px-3 py-2.5 text-center text-xs font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                                        View Details
                                    </a>
                                @else
                                    <a href="{{ route('login.options') }}" class="block rounded-lg bg-slate-900 px-3 py-2.5 text-center text-xs font-semibold text-white transition hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700">
                                        Sign In
                                    </a>
                                @endauth
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($canLoadMore)
                    <div class="mt-8 flex justify-center">
                        <button
                            type="button"
                            wire:click="loadMore"
                            wire:loading.attr="disabled"
                            wire:target="loadMore"
                            class="rounded-xl bg-gradient-to-r from-blue-600 to-violet-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/25 transition hover:-translate-y-0.5 hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            <span wire:loading.remove wire:target="loadMore">Show More</span>
                            <span wire:loading wire:target="loadMore">Loading...</span>
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
