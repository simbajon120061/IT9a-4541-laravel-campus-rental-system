<div class="bg-gradient-to-b from-slate-50 via-blue-50/30 to-white py-8 md:py-12 dark:from-slate-950 dark:to-slate-900">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <a href="{{ route('my-listings') }}" class="mb-6 inline-flex items-center text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to My Listings
        </a>

        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-slate-100">Item Rental History</h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        <span class="font-semibold">{{ $item->name }}</span>
                        @if($pendingCount > 0)
                            &bull; {{ $pendingCount }} pending request(s)
                        @endif
                    </p>
                </div>
                @if($managedCount > 0)
                    <a href="{{ route('lister.inventory', ['filter' => 'approved']) }}" class="inline-flex items-center justify-center rounded-lg border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-50 dark:border-blue-900/60 dark:text-blue-200 dark:hover:bg-blue-900/30">
                        Manage in Inventory
                    </a>
                @endif
            </div>
        </div>

        @if($requests->isEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-slate-600 dark:text-slate-400">No rental requests yet for this item.</p>
            </div>
        @else
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="overflow-hidden">
                    <table class="block w-full md:table">
                        <thead class="hidden bg-slate-100 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-300 md:table-header-group">
                            <tr>
                                <th class="px-5 py-4 text-left font-semibold">Requester</th>
                                <th class="px-5 py-4 text-left font-semibold">Rental Dates</th>
                                <th class="px-5 py-4 text-left font-semibold">Total</th>
                                <th class="px-5 py-4 text-center font-semibold">Status</th>
                                <th class="px-5 py-4 text-right font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="block divide-y divide-slate-200 dark:divide-slate-700 md:table-row-group">
                            @foreach($requests as $request)
                                <tr class="block p-4 text-sm text-slate-700 dark:text-slate-300 md:table-row md:p-0">
                                    <td class="block py-2 md:table-cell md:px-5 md:py-4">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Requester</p>
                                        <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $request->renter->name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $request->renter->email }}</p>
                                    </td>
                                    <td class="block py-2 md:table-cell md:px-5 md:py-4">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Rental Dates</p>
                                        {{ $request->start_date->format('M d, Y') }} - {{ $request->end_date->format('M d, Y') }}
                                    </td>
                                    <td class="block py-2 font-semibold text-slate-900 dark:text-slate-100 md:table-cell md:px-5 md:py-4">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Total</p>
                                        &#8369;{{ number_format($request->total_price, 2) }}
                                    </td>
                                    <td class="block py-2 md:table-cell md:px-5 md:py-4 md:text-center">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Status</p>
                                        @if($request->status === 'pending')
                                            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Pending</span>
                                        @elseif($request->status === 'approved')
                                            <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">Approved</span>
                                        @elseif($request->status === 'active')
                                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Active</span>
                                        @elseif($request->status === 'completed')
                                            <span class="inline-flex rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">Completed</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800 dark:bg-rose-900/40 dark:text-rose-200">Cancelled</span>
                                        @endif
                                    </td>
                                    <td class="block pt-3 md:table-cell md:px-5 md:py-4 md:text-right">
                                        <a href="{{ route('rental-requests.show', $request) }}" class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700 md:w-auto">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
