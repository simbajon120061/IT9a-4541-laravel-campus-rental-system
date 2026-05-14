<div class="bg-slate-50 py-8 text-slate-950 dark:bg-slate-950 dark:text-slate-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto_auto_auto] lg:items-end">
            <div class="min-w-0">
                <p class="text-sm font-bold uppercase tracking-wider text-violet-600 dark:text-violet-300">Lister Portal</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-normal text-slate-950 dark:text-white sm:text-4xl">Pending Rental Requests</h1>
                <p class="mt-2 text-lg text-slate-600 dark:text-slate-300">Approve or reject new rental requests for your listed items.</p>
            </div>
            
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-5 py-3 text-amber-900 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-200">
                <span class="text-sm font-semibold">{{ $pendingCount }} pending request(s)</span>
            </div>

            <a href="{{ route('lister.rental-logs') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-blue-200 px-5 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-50 dark:border-blue-900/60 dark:text-blue-200 dark:hover:bg-blue-900/30 sm:w-auto">
                View Rental Logs
            </a>

            <a href="{{ route('lister.inventory', ['filter' => 'approved']) }}" class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 sm:w-auto">
                Manage Approved Rentals ({{ $managedCount }})
            </a> 
            
        </div>

        @if (session()->has('message'))
            <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-900/20 dark:text-emerald-200">
                {{ session('message') }}
            </div>
        @endif

        <div class="mt-8 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(18rem,1fr)_minmax(11rem,14rem)_minmax(11rem,14rem)_minmax(10rem,13rem)]">
                <div class="min-w-0 sm:col-span-2 xl:col-span-1">
                    <label for="request-search" class="sr-only">Search rental requests</label>
                    <input id="request-search" type="text" wire:model.live.debounce.300ms="search" class="h-12 w-full rounded-lg border-slate-300 bg-slate-50 px-4 text-sm text-slate-950 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Search by item or renter name">
                </div>

                <div>
                    <label for="request-category" class="sr-only">Filter by item category</label>
                    <select id="request-category" wire:model.live="categoryFilter" class="h-12 w-full rounded-lg border-slate-300 bg-slate-50 px-4 text-sm font-semibold text-slate-700 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        <option value="all">All categories</option>
                        @foreach ($categoryOptions as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="request-grouping" class="sr-only">Group by item</label>
                    <select id="request-grouping" wire:model.live="itemGrouping" class="h-12 w-full rounded-lg border-slate-300 bg-slate-50 px-4 text-sm font-semibold text-slate-700 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        <option value="table">Standard table</option>
                        <option value="grouped">Grouped by item</option>
                    </select>
                </div>

                <div>
                    <label for="request-date-sort" class="sr-only">Sort by date</label>
                    <select id="request-date-sort" wire:model.live="dateSort" class="h-12 w-full rounded-lg border-slate-300 bg-slate-50 px-4 text-sm font-semibold text-slate-700 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        <option value="newest">Newest first</option>
                        <option value="oldest">Oldest first</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @if ($requests->isEmpty())
                <div class="p-10 text-center text-slate-600 dark:text-slate-300">
                    <p>No pending rental requests found.</p>
                    <a href="{{ route('lister.inventory', ['filter' => 'approved']) }}" class="mt-3 inline-flex items-center justify-center rounded-lg border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-50 dark:border-blue-900/60 dark:text-blue-200 dark:hover:bg-blue-900/30">
                        Check approved and active rentals
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="block w-full md:table md:min-w-[58rem]">
                        <thead class="hidden bg-slate-100 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-300 md:table-header-group">
                            <tr>
                                <th class="px-5 py-4 text-left font-semibold">Item</th>
                                <th class="px-5 py-4 text-left font-semibold">Requester</th>
                                <th class="px-5 py-4 text-left font-semibold">Rental Dates</th>
                                <th class="px-5 py-4 text-right font-semibold">Total</th>
                                <th class="px-5 py-4 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="block divide-y divide-slate-200 dark:divide-slate-800 md:table-row-group">
                            @php
                                $currentGroupedItemId = null;
                            @endphp

                            @foreach ($requests as $request)
                                @if ($itemGrouping === 'grouped' && $currentGroupedItemId !== $request->item_id)
                                    @php
                                        $currentGroupedItemId = $request->item_id;
                                    @endphp

                                    <tr class="block bg-violet-50/70 text-sm text-violet-900 dark:bg-violet-950/30 dark:text-violet-100 md:table-row">
                                        <td colspan="5" class="block px-4 py-3 font-bold md:table-cell md:px-5">
                                            {{ $request->item->name }}
                                            <span class="ml-2 text-xs font-semibold text-violet-700/70 dark:text-violet-200/80">{{ $request->item->categoryRecord?->name ?? 'No category' }}</span>
                                        </td>
                                    </tr>
                                @endif

                                <tr class="block p-4 text-sm text-slate-700 dark:text-slate-300 md:table-row md:p-0">
                                    <td class="block py-2 md:table-cell md:px-5 md:py-4">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Item</p>
                                        <p class="font-bold text-slate-950 dark:text-white">{{ $request->item->name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $request->item->categoryRecord?->name ?? 'No category' }}</p>
                                    </td>
                                    <td class="block py-2 md:table-cell md:px-5 md:py-4">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Requester</p>
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $request->renter->name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $request->renter->email }}</p>
                                    </td>
                                    <td class="block py-2 md:table-cell md:px-5 md:py-4">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Rental Dates</p>
                                        {{ $request->start_date->format('M d, Y') }} - {{ $request->end_date->format('M d, Y') }}
                                    </td>
                                    <td class="block py-2 font-bold text-slate-950 dark:text-white md:table-cell md:px-5 md:py-4 md:text-right">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Total</p>
                                        &#8369;{{ number_format($request->total_price, 2) }}
                                    </td>
                                    <td class="block pt-3 md:table-cell md:px-5 md:py-4">
                                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 md:flex md:justify-end">
                                            <a href="{{ route('rental-requests.show', $request) }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">View</a>
                                            <a href="{{ route('rental-requests.item', $request->item) }}" class="inline-flex items-center justify-center rounded-md border border-violet-200 px-3 py-2 text-xs font-semibold text-violet-700 transition hover:bg-violet-50 dark:border-violet-900/60 dark:text-violet-200 dark:hover:bg-violet-900/30">History</a>
                                            <button wire:click="approveRequest({{ $request->id }})" class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">Approve</button>
                                            <button wire:click="rejectRequest({{ $request->id }})" class="inline-flex items-center justify-center rounded-md bg-rose-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-rose-700">Reject</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($requests->hasPages())
            <div class="mt-6">{{ $requests->links() }}</div>
        @endif
    </div>
</div>
