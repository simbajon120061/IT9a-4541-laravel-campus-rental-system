<div class="bg-gradient-to-b from-gray-50 to-white py-8 md:py-12 dark:from-slate-950 dark:to-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2 dark:text-slate-100">My Listings</h1>
                <p class="text-gray-600 dark:text-slate-400">Manage the items you're sharing</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('lister.inventory') }}" class="inline-flex items-center justify-center px-6 py-3 bg-violet-600 hover:bg-violet-700 text-white font-semibold rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h6v6m-9 4h12a2 2 0 002-2V7a2 2 0 00-2-2h-2l-2-2H10L8 5H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Inventory
                </a>
                <a href="{{ route('add-item') }}" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Item
                </a>
            </div>
        </div>

        <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="w-full md:max-w-md">
                    <label for="listing-search" class="sr-only">Search listings</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.4a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input
                            id="listing-search"
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search by item name or description..."
                            class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                        >
                    </div>
                </div>

                <div class="flex w-full flex-col gap-3 sm:flex-row md:w-auto">
                    <div class="w-full sm:w-44">
                        <label for="availability-filter" class="sr-only">Filter by availability</label>
                        <select
                            id="availability-filter"
                            wire:model.live="availability"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                        >
                            <option value="">All Availability</option>
                            <option value="available">Available</option>
                            <option value="rented">Rented</option>
                        </select>
                    </div>

                    <div class="w-full sm:w-52">
                        <label for="category-filter" class="sr-only">Filter by category</label>
                        <select
                            id="category-filter"
                            wire:model.live="categoryId"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                        >
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-44">
                        <label for="sort-filter" class="sr-only">Sort listings</label>
                        <select
                            id="sort-filter"
                            wire:model.live="sortBy"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                        >
                            <option value="newest">Sort: Newest</option>
                            <option value="oldest">Sort: Oldest</option>
                            <option value="name_asc">Name: A to Z</option>
                            <option value="name_desc">Name: Z to A</option>
                            <option value="price_low_high">Price: Low to High</option>
                            <option value="price_high_low">Price: High to Low</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if (session()->has('message'))
            <div class="mb-6 p-4 md:p-5 bg-green-50 border border-green-200 text-green-800 rounded-xl shadow-sm dark:bg-green-900/20 dark:border-green-700 dark:text-green-300">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    {{ session('message') }}
                </div>
            </div>
        @endif

        @if($items->isEmpty())
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden p-12 dark:bg-slate-900 dark:shadow-slate-900/40">
                <div class="text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="mt-4 text-xl font-semibold text-gray-900 dark:text-slate-100">No items listed yet</h3>
                    <p class="mt-2 text-gray-600 mb-8 dark:text-slate-400">Start sharing resources by creating your first item.</p>
                    <a href="{{ route('add-item') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add First Item
                    </a>
                </div>
            </div>
        @else
            <!-- Items Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($items as $item)
                    <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow overflow-hidden dark:bg-slate-900 dark:shadow-slate-900/40">
                        <!-- Item Image -->
                        <div class="relative h-48 bg-gray-200 overflow-hidden dark:bg-slate-800">
                            @if($item->imageUrl())
                                <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="h-16 w-16 text-gray-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif

                            <!-- Status Badge -->
                            <div class="absolute top-3 right-3">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full
                                    @if($item->trashed() && $item->admin_removed_at)
                                        bg-rose-100 text-rose-800
                                    @elseif($item->status === 'available')
                                        bg-green-100 text-green-800
                                    @elseif($item->status === 'rented')
                                        bg-orange-100 text-orange-800
                                    @else
                                        bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $item->trashed() && $item->admin_removed_at ? 'Removed' : ucfirst($item->status) }}
                                </span>
                            </div>

                            <!-- Rental Count Badge -->
                            <div class="absolute top-3 left-3">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-white text-gray-700 shadow-sm dark:bg-slate-800 dark:text-slate-300">
                                    {{ $item->rentals_count ?? 0 }} rentals
                                </span>
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="p-5">
                            <!-- Item Name -->
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-1 dark:text-slate-100">{{ $item->name }}</h3>

                            <!-- Description -->
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2 dark:text-slate-400">{{ $item->description }}</p>

                            @if ($item->trashed() && $item->admin_removed_at)
                                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-900">
                                    <p class="font-semibold">Removed by admin</p>
                                    <p class="mt-1 text-xs">{{ $item->admin_removal_reason }}</p>
                                    @if ($item->latestAppeal)
                                        <p class="mt-2 text-xs font-semibold">Appeal: {{ ucfirst($item->latestAppeal->status) }}</p>
                                    @endif
                                </div>
                            @endif

                            <!-- Price -->
                            <div class="bg-blue-50 rounded-lg p-3 mb-4 dark:bg-slate-800">
                                <p class="text-xs font-medium text-gray-600 mb-1 dark:text-slate-400">Rental Price</p>
                                <p class="text-2xl font-bold text-blue-600">₱{{ number_format($item->price, 2) }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-500">per day</p>
                            </div>

                            <!-- Actions -->
                            @if ($item->trashed() && $item->admin_removed_at)
                                <button
                                    wire:click="openAppeal({{ $item->id }})"
                                    @if ($item->latestAppeal?->status === \App\Models\ItemAppeal::STATUS_PENDING) disabled @endif
                                    class="w-full rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:bg-slate-300 disabled:text-slate-600"
                                >
                                    {{ $item->latestAppeal?->status === \App\Models\ItemAppeal::STATUS_PENDING ? 'Appeal Pending' : 'Appeal Takedown' }}
                                </button>
                            @else
                            <div class="flex gap-2">
                                @if ($item->latestRental)
                                    <a
                                        href="{{ route('rental-requests.item', $item) }}"
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View Requests
                                    </a>
                                @else
                                    <span class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-slate-300 text-slate-600 text-sm font-semibold rounded-lg cursor-not-allowed">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View Requests
                                    </span>
                                @endif
                                <a
                                    href="{{ route('edit-item', $item->id) }}"
                                    class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit
                                </a>
                                <button
                                    wire:click="confirmDeleteItem({{ $item->id }})"
                                    class="inline-flex items-center justify-center px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($items->hasPages())
                <div class="mt-8">
                    {{ $items->links() }}
                </div>
            @endif
        @endif
    </div>

    @if($showDeleteModal)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="cancelDeleteItem"></div>

            <div class="relative w-full max-w-xl rounded-2xl border border-slate-700 bg-gradient-to-br from-slate-900 to-slate-800 p-6 shadow-2xl">
                <h2 class="text-2xl font-bold text-white">Confirm Delete</h2>
                <p class="mt-4 text-lg leading-relaxed text-slate-100">
                    Are you sure you want to delete
                    <span class="font-semibold text-white">"{{ $pendingDeleteItemName }}"</span>?
                    This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        wire:click="cancelDeleteItem"
                        class="rounded-xl border border-slate-500 px-5 py-2.5 text-sm font-semibold text-slate-100 transition hover:bg-slate-700"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="deleteItem"
                        class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700"
                    >
                        Delete Item
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($pendingAppealItemId)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="cancelAppeal"></div>

            <div class="relative w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Appeal Takedown</h2>
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">Ask admins to review "{{ $pendingAppealItemName }}" again.</p>

                <div class="mt-5 space-y-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-400">Reason</label>
                        <input wire:model="appealReason" type="text" class="w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        @error('appealReason') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-400">Details</label>
                        <textarea wire:model="appealDetails" rows="4" class="w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"></textarea>
                        @error('appealDetails') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="cancelAppeal" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">Cancel</button>
                    <button type="button" wire:click="submitAppeal" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Submit Appeal</button>
                </div>
            </div>
        </div>
    @endif
</div>
