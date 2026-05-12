<div class="bg-gradient-to-b from-slate-50 via-blue-50/40 to-violet-50/30 py-8 md:py-12 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <a href="{{ route('my-listings') }}" class="inline-flex items-center mb-8 text-blue-600 hover:text-blue-700 font-medium transition-colors dark:text-blue-400 dark:hover:text-blue-300">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to My Listings
        </a>

        <div class="relative mb-8 overflow-hidden rounded-2xl border border-white/50 bg-gradient-to-br from-blue-600 via-indigo-600 to-violet-600 p-6 text-white shadow-xl shadow-indigo-900/20 md:p-8">
            <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-12 -left-12 h-40 w-40 rounded-full bg-cyan-300/20 blur-2xl"></div>

            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="mb-2 text-3xl font-black tracking-tight md:text-4xl">Rent Inventory Management</h1>
                    <p class="text-blue-100">Monitor and manage all rental transactions tied to your listed items.</p>
                </div>
                @if ($approvedCount > 0)
                    <div class="rounded-xl border border-blue-200/30 bg-white/15 p-4 text-blue-50 shadow-sm backdrop-blur-sm">
                        <p class="text-sm font-semibold">On Process</p>
                        <p class="mt-1 text-sm"><span class="font-bold">{{ $approvedCount }}</span> approved request(s) are waiting for payment confirmation.</p>
                    </div>
                @endif
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mb-6 rounded-xl border border-emerald-200/80 bg-emerald-50/90 p-4 text-emerald-900 shadow-sm backdrop-blur-sm dark:border-emerald-900/60 dark:bg-emerald-900/20 dark:text-emerald-200">
                <div class="flex items-start gap-2">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            </div>
        @endif

        @if ($pendingCount > 0 || $dueSoonCount > 0)
            <div class="mb-6 grid gap-3 sm:grid-cols-2">
                @if ($pendingCount > 0)
                    <div class="rounded-xl border border-amber-200/80 bg-amber-50/90 p-4 text-amber-900 shadow-sm backdrop-blur-sm dark:border-amber-900/60 dark:bg-amber-900/20 dark:text-amber-200">
                        <p class="text-sm font-semibold">Pending Requests</p>
                        <p class="mt-1 text-sm">You have <span class="font-bold">{{ $pendingCount }}</span> request(s) waiting for approval.</p>
                    </div>
                @endif
                @if ($dueSoonCount > 0)
                    <div class="rounded-xl border border-rose-200/80 bg-rose-50/90 p-4 text-rose-900 shadow-sm backdrop-blur-sm dark:border-rose-900/60 dark:bg-rose-900/20 dark:text-rose-200">
                        <p class="text-sm font-semibold">Due Soon Alert</p>
                        <p class="mt-1 text-sm"><span class="font-bold">{{ $dueSoonCount }}</span> active loan(s) are nearing return date.</p>
                    </div>
                @endif
            </div>
        @endif

        <div class="mb-6 rounded-2xl border border-slate-200/70 bg-white/75 p-4 shadow-lg shadow-slate-900/5 backdrop-blur-xl dark:border-slate-700/70 dark:bg-slate-900/70 md:p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="w-full lg:max-w-sm">
                    <label for="search" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Search Item or Borrower</label>
                    <div class="relative rounded-lg border border-slate-300 bg-white/80 dark:border-slate-700 dark:bg-slate-900">
                        <svg class="pointer-events-none absolute left-3 top-3.5 h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input id="search" type="text" wire:model.live.debounce.300ms="search" placeholder="Search by item name or borrower name..." class="w-full rounded-lg border-0 bg-transparent py-3 pl-10 pr-4 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:text-slate-100">
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 lg:justify-end">
                    <button wire:click="setFilter('all')" class="inline-flex h-12 items-center rounded-lg px-3 text-sm font-semibold transition {{ $filterStatus === 'all' ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-md' : 'border border-slate-300 bg-white text-slate-700 hover:border-blue-400 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
                        All Listings <span class="ml-2 rounded-full bg-black/10 px-2 py-0.5 text-xs">{{ $allCount }}</span>
                    </button>
                    <button wire:click="setFilter('due_soon')" class="inline-flex h-12 items-center rounded-lg px-3 text-sm font-semibold transition {{ $filterStatus === 'due_soon' ? 'bg-gradient-to-r from-rose-600 to-red-600 text-white shadow-md' : 'border border-slate-300 bg-white text-slate-700 hover:border-rose-400 hover:text-rose-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
                        Due Soon <span class="ml-2 rounded-full bg-black/10 px-2 py-0.5 text-xs">{{ $dueSoonCount }}</span>
                    </button>
                    <button wire:click="setFilter('active')" class="inline-flex h-12 items-center rounded-lg px-3 text-sm font-semibold transition {{ $filterStatus === 'active' ? 'bg-gradient-to-r from-emerald-600 to-green-600 text-white shadow-md' : 'border border-slate-300 bg-white text-slate-700 hover:border-emerald-400 hover:text-emerald-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
                        Active Loan <span class="ml-2 rounded-full bg-black/10 px-2 py-0.5 text-xs">{{ $activeCount }}</span>
                    </button>
                    <button wire:click="setFilter('pending')" class="inline-flex h-12 items-center rounded-lg px-3 text-sm font-semibold transition {{ $filterStatus === 'pending' ? 'bg-gradient-to-r from-amber-600 to-orange-600 text-white shadow-md' : 'border border-slate-300 bg-white text-slate-700 hover:border-amber-400 hover:text-amber-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
                        Pending Request <span class="ml-2 rounded-full bg-black/10 px-2 py-0.5 text-xs">{{ $pendingCount }}</span>
                    </button>
                    <button wire:click="setFilter('approved')" class="inline-flex h-12 items-center rounded-lg px-3 text-sm font-semibold transition {{ $filterStatus === 'approved' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md' : 'border border-slate-300 bg-white text-slate-700 hover:border-blue-400 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
                        Approved Request <span class="ml-2 rounded-full bg-black/10 px-2 py-0.5 text-xs">{{ $approvedCount }}</span>
                    </button>
                </div>
            </div>
        </div>

        @if ($rentals->isEmpty())
            <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-10 text-center shadow-sm backdrop-blur-xl dark:border-slate-700/70 dark:bg-slate-900/70 md:p-14">
                <svg class="mx-auto mb-4 h-14 w-14 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mb-2 text-xl font-semibold text-slate-900 dark:text-slate-100">No records found</h3>
                <p class="text-slate-600 dark:text-slate-400">Try another filter or search keyword to find a rental transaction.</p>
            </div>
        @else
            <div class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white/80 shadow-lg shadow-slate-900/5 backdrop-blur-xl dark:border-slate-700/70 dark:bg-slate-900/70">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1080px]">
                        <thead>
                            <tr class="bg-slate-100/90 text-sm text-slate-700 dark:bg-slate-800/90 dark:text-slate-300">
                                <th class="px-5 py-4 text-left font-semibold">Item</th>
                                <th class="px-5 py-4 text-left font-semibold">Borrower</th>
                                <th class="px-5 py-4 text-left font-semibold">Price/Day</th>
                                <th class="px-5 py-4 text-left font-semibold">Start Date</th>
                                <th class="px-5 py-4 text-left font-semibold">End Date</th>
                                <th class="px-5 py-4 text-center font-semibold">Days</th>
                                <th class="px-5 py-4 text-right font-semibold">Total Cost</th>
                                <th class="px-5 py-4 text-center font-semibold">Payment Status</th>
                                <th class="px-5 py-4 text-center font-semibold">Rental Status</th>
                                <th class="px-5 py-4 text-center font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rentals as $rental)
                                @php
                                    $seconds = $rental->start_date->diffInSeconds($rental->end_date, false);
                                    $days = max(1, (int) ceil($seconds / 86400));
                                    $remainingBalance = max(0, round((float) $rental->total_price - (float) ($rental->paid_amount ?? 0), 2));
                                    $secondsLeft = now()->diffInSeconds($rental->end_date, false);
                                    $daysLeft = $secondsLeft >= 0
                                        ? (int) ceil($secondsLeft / 86400)
                                        : (int) floor($secondsLeft / 86400);
                                    $isOnProcess = $rental->status === 'approved' || ($rental->status === 'active' && $rental->start_date->isFuture());
                                    $dueSoon = $rental->status === 'active' && now()->between($rental->start_date, $rental->end_date) && $daysLeft <= 7;
                                @endphp
                                <tr class="border-t border-slate-200 text-sm text-slate-700 dark:border-slate-700 dark:text-slate-300">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100 ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                                                @if ($rental->item->imageUrl())
                                                    <img src="{{ $rental->item->imageUrl() }}" alt="{{ $rental->item->name }}" class="h-full w-full object-cover">
                                                @else
                                                    <svg class="h-6 w-6 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $rental->item->name }}</div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $rental->item->categoryRecord?->name ?? 'No category' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">{{ $rental->renter->name }}</td>
                                    <td class="px-5 py-4">&#8369;{{ number_format($rental->item->price, 2) }}</td>
                                    <td class="px-5 py-4">{{ $rental->start_date->format('M d, Y') }}</td>
                                    <td class="px-5 py-4">{{ $rental->end_date->format('M d, Y') }}</td>
                                    <td class="px-5 py-4 text-center">{{ $days }}</td>
                                    <td class="px-5 py-4 text-right font-semibold text-slate-900 dark:text-slate-100">&#8369;{{ number_format($rental->total_price, 2) }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="space-y-2">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                                                @if ($rental->payment_status === 'fully_paid') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200
                                                @elseif ($rental->payment_status === 'partial') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200
                                                @else bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200
                                                @endif">
                                                {{ $rental->payment_status === 'fully_paid' ? 'Paid' : ucfirst($rental->payment_status) }}
                                            </span>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                                &#8369;{{ number_format((float) $rental->paid_amount, 2) }} / &#8369;{{ number_format($rental->total_price, 2) }}
                                            </div>
                                            @if (in_array($rental->status, ['approved', 'active'], true) && $rental->payment_status !== 'fully_paid')
                                                <div class="flex items-center justify-center gap-1">
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0.01"
                                                        max="{{ $remainingBalance }}"
                                                        wire:model.defer="paymentAmounts.{{ $rental->id }}"
                                                        class="w-24 rounded-md border border-slate-300 bg-white px-2 py-1 text-xs text-slate-900 focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                                        placeholder="Amount"
                                                    >
                                                    <button wire:click="fillFullPaymentAmount({{ $rental->id }})" type="button" class="rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100 dark:border-blue-900/60 dark:bg-blue-900/30 dark:text-blue-200">
                                                        Pay in Full
                                                    </button>
                                                    <button wire:click="recordPayment({{ $rental->id }})" class="rounded-md bg-blue-600 px-2.5 py-1 text-xs font-semibold text-white transition hover:bg-blue-700">
                                                        Save
                                                    </button>
                                                </div>
                                                @error('paymentAmounts.' . $rental->id)
                                                    <p class="text-[11px] text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                                @enderror
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if ($rental->status === 'pending')
                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Pending Request</span>
                                        @elseif ($isOnProcess)
                                            <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">On Process</span>
                                        @elseif ($dueSoon)
                                            <span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800 dark:bg-rose-900/40 dark:text-rose-200">Due Soon</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Active Loan</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if ($rental->status === 'pending')
                                            <a href="{{ route('rental-requests.show', $rental) }}" class="inline-flex items-center rounded-md bg-gradient-to-r from-amber-600 to-orange-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:shadow-md">
                                                View Request
                                            </a>
                                        @elseif ($isOnProcess)
                                            @if ($rental->start_date->isFuture())
                                                <span class="text-xs font-semibold text-blue-600 dark:text-blue-300">Starts {{ $rental->start_date->format('M d, Y') }}</span>
                                            @else
                                                <button wire:click="markAsRented({{ $rental->id }})" class="inline-flex items-center rounded-md bg-gradient-to-r from-emerald-600 to-green-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:shadow-md">
                                                    Mark as Rented
                                                </button>
                                            @endif
                                        @elseif ($rental->status === 'active')
                                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                                                {{ max(0, $daysLeft) }} day(s) left
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-500 dark:text-slate-400">No action</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($rentals->hasPages())
                <div class="mt-6">
                    {{ $rentals->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
