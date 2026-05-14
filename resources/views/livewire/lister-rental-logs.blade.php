<div class="bg-slate-50 py-8 text-slate-950 dark:bg-slate-950 dark:text-slate-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
            <div class="min-w-0">
                <p class="text-sm font-bold uppercase tracking-wider text-violet-600 dark:text-violet-300">Lister Portal</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-normal text-slate-950 dark:text-white sm:text-4xl">Rental Logs</h1>
                <p class="mt-2 text-lg text-slate-600 dark:text-slate-300">Review rental request history grouped by listed item.</p>
            </div>

            <a href="{{ route('lister.rental-requests') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-blue-200 px-5 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-50 dark:border-blue-900/60 dark:text-blue-200 dark:hover:bg-blue-900/30 sm:w-auto">
                Back to Pending Requests
            </a>
        </div>

        <div class="mt-8 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <label for="rental-log-search" class="sr-only">Search rental logs</label>
            <input
                id="rental-log-search"
                type="text"
                wire:model.live.debounce.300ms="search"
                class="h-12 w-full rounded-lg border-slate-300 bg-slate-50 px-4 text-sm text-slate-950 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                placeholder="Search by item or category name"
            >
        </div>

        <div class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @if ($items->isEmpty())
                <div class="p-10 text-center text-slate-600 dark:text-slate-300">
                    <p>No rental logs found.</p>
                    <a href="{{ route('lister.rental-requests') }}" class="mt-3 inline-flex items-center justify-center rounded-lg border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-50 dark:border-blue-900/60 dark:text-blue-200 dark:hover:bg-blue-900/30">
                        Check pending requests
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="block w-full md:table md:min-w-[56rem]">
                        <thead class="hidden bg-slate-100 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-300 md:table-header-group">
                            <tr>
                                <th class="px-5 py-4 text-left font-semibold">Item</th>
                                <th class="px-5 py-4 text-center font-semibold">Total Requests</th>
                                <th class="px-5 py-4 text-center font-semibold">Pending</th>
                                <th class="px-5 py-4 text-center font-semibold">Approved / Active</th>
                                <th class="px-5 py-4 text-left font-semibold">Latest Activity</th>
                                <th class="px-5 py-4 text-right font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="block divide-y divide-slate-200 dark:divide-slate-800 md:table-row-group">
                            @foreach ($items as $item)
                                @php
                                    $latestActivity = $item->rentals_max_created_at
                                        ? \Illuminate\Support\Carbon::parse($item->rentals_max_created_at)
                                        : null;
                                @endphp
                                <tr class="block p-4 text-sm text-slate-700 dark:text-slate-300 md:table-row md:p-0">
                                    <td class="block py-2 md:table-cell md:px-5 md:py-4">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Item</p>
                                        <p class="font-bold text-slate-950 dark:text-white">{{ $item->name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item->categoryRecord?->name ?? 'No category' }}</p>
                                    </td>
                                    <td class="block py-2 font-semibold text-slate-950 dark:text-white md:table-cell md:px-5 md:py-4 md:text-center">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Total Requests</p>
                                        {{ $item->rental_requests_count }}
                                    </td>
                                    <td class="block py-2 md:table-cell md:px-5 md:py-4 md:text-center">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Pending</p>
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">
                                            {{ $item->pending_requests_count }}
                                        </span>
                                    </td>
                                    <td class="block py-2 md:table-cell md:px-5 md:py-4 md:text-center">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Approved / Active</p>
                                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                                            {{ $item->managed_requests_count }}
                                        </span>
                                    </td>
                                    <td class="block py-2 md:table-cell md:px-5 md:py-4">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Latest Activity</p>
                                        {{ $latestActivity?->format('M d, Y') ?? 'No activity' }}
                                        @if ($item->closed_requests_count > 0)
                                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item->closed_requests_count }} closed request(s)</p>
                                        @endif
                                    </td>
                                    <td class="block pt-3 md:table-cell md:px-5 md:py-4 md:text-right">
                                        <a href="{{ route('rental-requests.item', $item) }}" class="inline-flex w-full items-center justify-center rounded-md bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700 md:w-auto">
                                            View History
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($items->hasPages())
            <div class="mt-6">{{ $items->links() }}</div>
        @endif
    </div>
</div>
