<div class="bg-gradient-to-b from-gray-50 to-white py-8 md:py-12 dark:from-slate-950 dark:to-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2 dark:text-slate-100">My Rentals</h1>
            <p class="text-gray-600 dark:text-slate-400">Track the items you've rented</p>
        </div>

        @php
            $dueSoonCount = $rentals->filter(function ($r): bool {
                $secondsLeft = now()->diffInSeconds($r->end_date, false);
                $daysLeft = $secondsLeft >= 0
                    ? (int) ceil($secondsLeft / 86400)
                    : (int) floor($secondsLeft / 86400);
                $isOnProcess = $r->status === 'approved' || ($r->status === 'active' && $r->start_date->isFuture());

                return $r->status === 'active' && ! $isOnProcess && $daysLeft >= 0 && $daysLeft <= 7;
            })->count();
            $overdueCount = $rentals->filter(function ($r): bool {
                $secondsLeft = now()->diffInSeconds($r->end_date, false);
                $daysLeft = $secondsLeft >= 0
                    ? (int) ceil($secondsLeft / 86400)
                    : (int) floor($secondsLeft / 86400);
                $isOnProcess = $r->status === 'approved' || ($r->status === 'active' && $r->start_date->isFuture());

                return $r->status === 'active' && ! $isOnProcess && $daysLeft < 0;
            })->count();
        @endphp

        @if($dueSoonCount > 0)
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg dark:bg-red-900/20 dark:border-red-700">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h4 class="font-semibold text-red-800">{{ $dueSoonCount }} rental(s) due soon!</h4>
                        <p class="text-sm text-red-700 mt-1">You have rental(s) that will be due within the next 7 days. Please plan for their return.</p>
                    </div>
                </div>
            </div>
        @endif

        @if($overdueCount > 0)
            <div class="mb-6 p-4 bg-orange-50 border-l-4 border-orange-500 rounded-lg dark:bg-orange-900/20 dark:border-orange-700">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-orange-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h4 class="font-semibold text-orange-800">{{ $overdueCount }} rental(s) are overdue!</h4>
                        <p class="text-sm text-orange-700 mt-1">Please return these items as soon as possible.</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
            <p class="mb-3 text-lg font-bold text-slate-900 dark:text-slate-100">Search Item or Owner</p>

            <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
                <div class="relative w-full xl:w-72 xl:shrink-0">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.3-4.3m1.8-5.2a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by item name or owner name..."
                        class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-500 dark:focus:ring-blue-500/30"
                    >
                </div>

                <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:flex xl:min-w-0 xl:flex-1 xl:flex-nowrap">
                    <button
                        wire:click="setFilter('all')"
                        class="{{ $filterStatus === 'all' ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'border border-slate-300 bg-white text-slate-700 hover:border-blue-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500' }} inline-flex min-w-0 items-center justify-between rounded-xl px-3 py-2.5 text-xs font-semibold transition xl:flex-1"
                    >
                        <span class="truncate">All Rentals</span>
                        <span class="{{ $filterStatus === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }} ms-2 inline-flex min-w-6 shrink-0 items-center justify-center rounded-full px-1.5 py-0.5 text-xs font-bold">{{ $allCount }}</span>
                    </button>

                    <button
                        wire:click="setFilter('due_soon')"
                        class="{{ $filterStatus === 'due_soon' ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'border border-slate-300 bg-white text-slate-700 hover:border-blue-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500' }} inline-flex min-w-0 items-center justify-between rounded-xl px-3 py-2.5 text-xs font-semibold transition xl:flex-1"
                    >
                        <span class="truncate">Due Soon</span>
                        <span class="{{ $filterStatus === 'due_soon' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }} ms-2 inline-flex min-w-6 shrink-0 items-center justify-center rounded-full px-1.5 py-0.5 text-xs font-bold">{{ $dueSoonCount }}</span>
                    </button>

                    <button
                        wire:click="setFilter('active')"
                        class="{{ $filterStatus === 'active' ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'border border-slate-300 bg-white text-slate-700 hover:border-blue-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500' }} inline-flex min-w-0 items-center justify-between rounded-xl px-3 py-2.5 text-xs font-semibold transition xl:flex-1"
                    >
                        <span class="truncate">Active Loan</span>
                        <span class="{{ $filterStatus === 'active' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }} ms-2 inline-flex min-w-6 shrink-0 items-center justify-center rounded-full px-1.5 py-0.5 text-xs font-bold">{{ $activeCount }}</span>
                    </button>

                    <button
                        wire:click="setFilter('pending')"
                        class="{{ $filterStatus === 'pending' ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'border border-slate-300 bg-white text-slate-700 hover:border-blue-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500' }} inline-flex min-w-0 items-center justify-between rounded-xl px-3 py-2.5 text-xs font-semibold transition xl:flex-1"
                    >
                        <span class="truncate">Pending Request</span>
                        <span class="{{ $filterStatus === 'pending' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }} ms-2 inline-flex min-w-6 shrink-0 items-center justify-center rounded-full px-1.5 py-0.5 text-xs font-bold">{{ $pendingCount }}</span>
                    </button>

                    <button
                        wire:click="setFilter('approved')"
                        class="{{ $filterStatus === 'approved' ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'border border-slate-300 bg-white text-slate-700 hover:border-blue-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500' }} inline-flex min-w-0 items-center justify-between rounded-xl px-3 py-2.5 text-xs font-semibold transition xl:flex-1"
                    >
                        <span class="truncate">Approved Request</span>
                        <span class="{{ $filterStatus === 'approved' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }} ms-2 inline-flex min-w-6 shrink-0 items-center justify-center rounded-full px-1.5 py-0.5 text-xs font-bold">{{ $approvedCount }}</span>
                    </button>
                </div>
            </div>
        </div>

        @if($rentals->isEmpty())
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden p-12 dark:bg-slate-900 dark:shadow-slate-900/40">
                <div class="text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    @if($filterStatus === 'all')
                        <h3 class="mt-4 text-xl font-semibold text-gray-900">No rentals yet</h3>
                        <p class="mt-2 text-gray-600 mb-8">Start browsing items available for rent.</p>
                        <a href="{{ route('renter.marketplace') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Browse Items
                        </a>
                    @else
                        <h3 class="mt-4 text-xl font-semibold text-gray-900 dark:text-slate-100">No rentals for this filter</h3>
                        <p class="mt-2 text-gray-600 mb-8">Try another filter or go back to all rentals.</p>
                        <button wire:click="setFilter('all')" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back to All Rentals
                        </button>
                    @endif
                </div>
            </div>
        @else
            <!-- Table Container -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 dark:bg-slate-900 dark:border-slate-700 dark:shadow-slate-900/40">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-100 border-b border-gray-200 dark:bg-slate-800 dark:border-slate-700">
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-slate-300">Item</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-slate-300">Owner</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-slate-300">Rental Period</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700 dark:text-slate-300">Days Left</th>
                                <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700 dark:text-slate-300">Total Price</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700 dark:text-slate-300">Payment Status</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700 dark:text-slate-300">Status</th>
                                <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700 dark:text-slate-300">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rentals as $rental)
                                @php
                                    $secondsLeft = now()->diffInSeconds($rental->end_date, false);
                                    $daysLeft = $secondsLeft >= 0
                                        ? (int) ceil($secondsLeft / 86400)
                                        : (int) floor($secondsLeft / 86400);
                                    $isOnProcess = $rental->status === 'approved' || ($rental->status === 'active' && $rental->start_date->isFuture());
                                    $isDueSoon = $rental->status === 'active' && ! $isOnProcess && $daysLeft >= 0 && $daysLeft <= 7;
                                    $isOverdue = $daysLeft < 0;
                                    $rowClass = $isDueSoon ? 'bg-red-50 hover:bg-red-100 dark:bg-red-950/20 dark:hover:bg-red-950/40' : ($isOverdue ? 'bg-orange-50 hover:bg-orange-100 dark:bg-orange-950/20 dark:hover:bg-orange-950/40' : 'hover:bg-gray-50 dark:hover:bg-slate-800/60');
                                    $paidAmount = (float) ($rental->paid_amount ?? 0);
                                    $totalPrice = (float) $rental->total_price;
                                    $balanceAmount = max(0, $totalPrice - $paidAmount);
                                    $paymentStatusLabel = match ($rental->payment_status) {
                                        'fully_paid' => 'Fully Paid',
                                        'partial' => 'Partial',
                                        default => 'Unpaid',
                                    };
                                    $paymentStatusClass = match ($rental->payment_status) {
                                        'fully_paid' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                                        'partial' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                                        default => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                                    };
                                @endphp
                                <tr class="{{ $rowClass }} border-b border-gray-200 transition-colors duration-200 dark:border-slate-700">
                                    <!-- Item Name -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if($rental->item->imageUrl())
                                                <img class="w-12 h-12 rounded-lg object-cover" src="{{ $rental->item->imageUrl() }}" alt="{{ $rental->item->name }}">
                                            @else
                                                <div class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $rental->item->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $rental->item->categoryRecord?->name ?? 'No category' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Owner -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                <span class="text-xs font-semibold text-blue-600">
                                                    @php
                                                        $initials = collect(explode(' ', trim($rental->item->user->name)))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
                                                    @endphp
                                                    {{ $initials }}
                                                </span>
                                            </div>
                                            <p class="text-sm font-medium text-gray-900">{{ $rental->item->user->name }}</p>
                                        </div>
                                    </td>

                                    <!-- Rental Period -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <p class="text-gray-900">{{ $rental->start_date->format('M d') }} → {{ $rental->end_date->format('M d, Y') }}</p>
                                            <p class="text-xs text-gray-500">{{ $rental->start_date->format('Y') }}</p>
                                        </div>
                                    </td>

                                    <!-- Days Left -->
                                    <td class="px-6 py-4">
                                        <div class="text-center">
                                            @if($rental->status === 'completed')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Completed
                                                </span>
                                            @elseif($rental->status === 'pending')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Pending
                                                </span>
                                            @elseif($isOnProcess)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                                    On Process
                                                </span>
                                            @else
                                                @if($isOverdue)
                                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"></path>
                                                        </svg>
                                                        Overdue
                                                    </div>
                                                @elseif($isDueSoon)
                                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold animate-pulse bg-red-100 text-red-800">
                                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"></path>
                                                        </svg>
                                                        {{ $daysLeft }} days
                                                    </div>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                                        {{ $daysLeft }} days
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Total Price -->
                                    <td class="px-6 py-4 text-right">
                                        <p class="text-sm font-bold text-gray-900 dark:text-slate-100">₱{{ number_format($rental->total_price, 2) }}</p>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="{{ $paymentStatusClass }} inline-flex rounded-full px-3 py-1 text-xs font-semibold">
                                                {{ $paymentStatusLabel }}
                                            </span>
                                            <p class="whitespace-nowrap text-xs font-medium text-slate-500 dark:text-slate-400">
                                                Balance: &#8369;{{ number_format($balanceAmount, 2) }}
                                            </p>
                                            <p class="whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">
                                                Paid &#8369;{{ number_format($paidAmount, 2) }} / &#8369;{{ number_format($totalPrice, 2) }}
                                            </p>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                                            @if($isOnProcess)
                                                bg-blue-100 text-blue-800
                                            @elseif($rental->status === 'active')
                                                bg-green-100 text-green-800
                                            @elseif($rental->status === 'completed')
                                                bg-blue-100 text-blue-800
                                            @elseif($rental->status === 'approved')
                                                bg-blue-100 text-blue-800
                                            @elseif($rental->status === 'pending')
                                                bg-yellow-100 text-yellow-800
                                            @else
                                                bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $isOnProcess ? 'On Process' : ($rental->status === 'approved' ? 'Approved Request' : ucfirst($rental->status)) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('rental-requests.show', $rental) }}" class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($rentals->hasPages())
                <div class="mt-8">
                    {{ $rentals->links() }}
                </div>
            @endif

        @endif
    </div>
</div>
