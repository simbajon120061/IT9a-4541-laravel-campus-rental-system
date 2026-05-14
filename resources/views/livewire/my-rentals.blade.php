<div class="bg-gradient-to-b from-gray-50 to-white py-6 sm:py-8 md:py-12 dark:from-slate-950 dark:to-slate-900">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <h1 class="mb-2 text-3xl font-bold text-gray-900 dark:text-slate-100 sm:text-4xl">My Rentals</h1>
            <p class="text-sm text-gray-600 dark:text-slate-400 sm:text-base">Track the items you've rented</p>
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

        @if (session()->has('message'))
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-900/20 dark:text-emerald-200">
                {{ session('message') }}
            </div>
        @endif

        @if ($receiptRental)
            @php
                $receiptPaidAmount = (float) ($receiptRental->paid_amount ?? 0);
                $receiptTotalPrice = (float) $receiptRental->total_price;
                $receiptBalance = max(0, $receiptTotalPrice - $receiptPaidAmount);
                $receiptStatusLabel = $receiptRental->payment_status === \App\Models\Rental::PAYMENT_STATUS_FULLY_PAID ? 'Fully Paid' : 'Partial';
            @endphp

            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="receipt-modal-title">
                <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 dark:border-slate-800 dark:bg-slate-950">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-300">Payment Confirmation</p>
                                <h2 id="receipt-modal-title" class="mt-1 text-2xl font-extrabold text-slate-950 dark:text-white">Digital Receipt</h2>
                            </div>
                            <button type="button" wire:click="closeReceiptModal" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-500 transition hover:bg-white hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Close receipt">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="px-5 py-5">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-900/20 dark:text-emerald-200">
                            <p class="text-sm font-bold">{{ $receiptStatusLabel }}</p>
                            <p class="mt-1 text-xs">Receipt #CR-{{ $receiptRental->id }}</p>
                        </div>

                        <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
                            <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950">
                                <dt class="text-xs font-bold uppercase text-slate-400">Item</dt>
                                <dd class="mt-1 font-semibold text-slate-950 dark:text-white">{{ $receiptRental->item->name }}</dd>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950">
                                <dt class="text-xs font-bold uppercase text-slate-400">Lister</dt>
                                <dd class="mt-1 font-semibold text-slate-950 dark:text-white">{{ $receiptRental->item->user->name }}</dd>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950">
                                <dt class="text-xs font-bold uppercase text-slate-400">Rental Period</dt>
                                <dd class="mt-1 font-semibold text-slate-950 dark:text-white">{{ $receiptRental->start_date->format('M d, Y') }} - {{ $receiptRental->end_date->format('M d, Y') }}</dd>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950">
                                <dt class="text-xs font-bold uppercase text-slate-400">Confirmed</dt>
                                <dd class="mt-1 font-semibold text-slate-950 dark:text-white">{{ $receiptRental->updated_at->format('M d, Y g:i A') }}</dd>
                            </div>
                        </dl>

                        <div class="mt-5 rounded-xl border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 text-sm dark:border-slate-800">
                                <span class="font-semibold text-slate-600 dark:text-slate-300">Total</span>
                                <span class="font-bold text-slate-950 dark:text-white">&#8369;{{ number_format($receiptTotalPrice, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 text-sm dark:border-slate-800">
                                <span class="font-semibold text-slate-600 dark:text-slate-300">Paid</span>
                                <span class="font-bold text-emerald-700 dark:text-emerald-300">&#8369;{{ number_format($receiptPaidAmount, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between px-4 py-3 text-sm">
                                <span class="font-semibold text-slate-600 dark:text-slate-300">Remaining Balance</span>
                                <span class="font-bold text-slate-950 dark:text-white">&#8369;{{ number_format($receiptBalance, 2) }}</span>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <button type="button" wire:click="downloadReceipt" class="inline-flex h-11 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">
                                Save/Download
                            </button>
                            <button type="button" wire:click="closeReceiptModal" class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-300 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                Exit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/70 sm:rounded-2xl sm:p-5">
            <p class="mb-3 text-base font-bold text-slate-900 dark:text-slate-100 sm:text-lg">Search Item or Owner</p>

            <div class="grid gap-3 lg:grid-cols-[minmax(16rem,24rem)_minmax(0,1fr)] lg:items-center">
                <div class="relative w-full">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.3-4.3m1.8-5.2a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by item name or owner name..."
                        class="h-12 w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-500 dark:focus:ring-blue-500/30"
                    >
                </div>

                <div class="grid w-full grid-cols-1 gap-2 min-[420px]:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
                    <button
                        wire:click="setFilter('all')"
                        class="{{ $filterStatus === 'all' ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'border border-slate-300 bg-white text-slate-700 hover:border-blue-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500' }} inline-flex h-12 min-w-0 items-center justify-between rounded-xl px-3 text-xs font-semibold transition sm:text-sm"
                    >
                        <span class="truncate">All Rentals</span>
                        <span class="{{ $filterStatus === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }} ms-2 inline-flex min-w-6 shrink-0 items-center justify-center rounded-full px-1.5 py-0.5 text-xs font-bold">{{ $allCount }}</span>
                    </button>

                    <button
                        wire:click="setFilter('due_soon')"
                        class="{{ $filterStatus === 'due_soon' ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'border border-slate-300 bg-white text-slate-700 hover:border-blue-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500' }} inline-flex h-12 min-w-0 items-center justify-between rounded-xl px-3 text-xs font-semibold transition sm:text-sm"
                    >
                        <span class="truncate">Due Soon</span>
                        <span class="{{ $filterStatus === 'due_soon' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }} ms-2 inline-flex min-w-6 shrink-0 items-center justify-center rounded-full px-1.5 py-0.5 text-xs font-bold">{{ $dueSoonCount }}</span>
                    </button>

                    <button
                        wire:click="setFilter('active')"
                        class="{{ $filterStatus === 'active' ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'border border-slate-300 bg-white text-slate-700 hover:border-blue-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500' }} inline-flex h-12 min-w-0 items-center justify-between rounded-xl px-3 text-xs font-semibold transition sm:text-sm"
                    >
                        <span class="truncate">Active Loan</span>
                        <span class="{{ $filterStatus === 'active' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }} ms-2 inline-flex min-w-6 shrink-0 items-center justify-center rounded-full px-1.5 py-0.5 text-xs font-bold">{{ $activeCount }}</span>
                    </button>

                    <button
                        wire:click="setFilter('pending')"
                        class="{{ $filterStatus === 'pending' ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'border border-slate-300 bg-white text-slate-700 hover:border-blue-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500' }} inline-flex h-12 min-w-0 items-center justify-between rounded-xl px-3 text-xs font-semibold transition sm:text-sm"
                    >
                        <span class="truncate">Pending Request</span>
                        <span class="{{ $filterStatus === 'pending' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }} ms-2 inline-flex min-w-6 shrink-0 items-center justify-center rounded-full px-1.5 py-0.5 text-xs font-bold">{{ $pendingCount }}</span>
                    </button>

                    <button
                        wire:click="setFilter('approved')"
                        class="{{ $filterStatus === 'approved' ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'border border-slate-300 bg-white text-slate-700 hover:border-blue-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-500' }} inline-flex h-12 min-w-0 items-center justify-between rounded-xl px-3 text-xs font-semibold transition sm:text-sm"
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
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900 dark:shadow-slate-900/40">
                <div>
                    <table class="w-full xl:table">
                        <thead class="hidden xl:table-header-group">
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
                        <tbody class="block divide-y divide-gray-200 dark:divide-slate-700 xl:table-row-group">
                            @foreach($rentals as $rental)
                                @php
                                    $secondsLeft = now()->diffInSeconds($rental->end_date, false);
                                    $daysLeft = $secondsLeft >= 0
                                        ? (int) ceil($secondsLeft / 86400)
                                        : (int) floor($secondsLeft / 86400);
                                    $isOnProcess = $rental->status === 'approved' || ($rental->status === 'active' && $rental->start_date->isFuture());
                                    $isDueToday = $rental->status === 'active' && ! $isOnProcess && $rental->end_date->isToday();
                                    $isDueSoon = $rental->status === 'active' && ! $isOnProcess && ! $isDueToday && $daysLeft >= 0 && $daysLeft <= 7;
                                    $isOverdue = $rental->status === 'active' && ! $isOnProcess && ! $isDueToday && $daysLeft < 0;
                                    $rowClass = ($isDueToday || $isDueSoon) ? 'bg-red-50 hover:bg-red-100 dark:bg-red-950/20 dark:hover:bg-red-950/40' : ($isOverdue ? 'bg-orange-50 hover:bg-orange-100 dark:bg-orange-950/20 dark:hover:bg-orange-950/40' : 'hover:bg-gray-50 dark:hover:bg-slate-800/60');
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
                                <tr id="rental-{{ $rental->id }}" class="{{ $rowClass }} block scroll-mt-28 p-4 transition-colors duration-200 sm:p-5 md:grid md:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)] md:gap-x-6 md:gap-y-3 xl:table-row xl:p-0">
                                    <!-- Item Name -->
                                    <td class="block py-2 md:col-span-2 xl:table-cell xl:px-6 xl:py-4">
                                        <p class="mb-2 text-[11px] font-semibold uppercase text-slate-400 xl:hidden">Item</p>
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
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-slate-100">{{ $rental->item->name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-slate-400">{{ $rental->item->categoryRecord?->name ?? 'No category' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Owner -->
                                    <td class="block py-2 xl:table-cell xl:px-6 xl:py-4">
                                        <p class="mb-2 text-[11px] font-semibold uppercase text-slate-400 xl:hidden">Owner</p>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                <span class="text-xs font-semibold text-blue-600">
                                                    @php
                                                        $initials = collect(explode(' ', trim($rental->item->user->name)))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
                                                    @endphp
                                                    {{ $initials }}
                                                </span>
                                            </div>
                                            <p class="min-w-0 text-sm font-medium text-gray-900 dark:text-slate-100">{{ $rental->item->user->name }}</p>
                                        </div>
                                    </td>

                                    <!-- Rental Period -->
                                    <td class="block py-2 xl:table-cell xl:px-6 xl:py-4">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 xl:hidden">Rental Period</p>
                                        <div class="text-sm">
                                            <p class="text-gray-900 dark:text-slate-100">{{ $rental->start_date->format('M d') }} &rarr; {{ $rental->end_date->format('M d, Y') }}</p>
                                            <p class="text-xs text-gray-500 dark:text-slate-400">{{ $rental->start_date->format('Y') }}</p>
                                        </div>
                                    </td>

                                    <!-- Days Left -->
                                    <td class="block py-2 xl:table-cell xl:px-6 xl:py-4">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 xl:hidden">Days Left</p>
                                        <div class="xl:text-center">
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
                                            @elseif($rental->status === 'cancelled')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                                                    Cancelled
                                                </span>
                                            @elseif($isOnProcess)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                                    On Process
                                                </span>
                                            @else
                                                @if($isDueToday)
                                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"></path>
                                                        </svg>
                                                        Due Today
                                                    </div>
                                                @elseif($isOverdue)
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
                                    <td class="block py-2 xl:table-cell xl:px-6 xl:py-4 xl:text-right">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 xl:hidden">Total Price</p>
                                        <p class="text-sm font-bold text-gray-900 dark:text-slate-100">&#8369;{{ number_format($rental->total_price, 2) }}</p>
                                    </td>

                                    <td class="block py-2 xl:table-cell xl:px-6 xl:py-4 xl:text-center">
                                        <p class="mb-2 text-[11px] font-semibold uppercase text-slate-400 xl:hidden">Payment Status</p>
                                        <div class="flex flex-col items-start gap-1 xl:items-center">
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
                                    <td class="block py-2 xl:table-cell xl:px-6 xl:py-4 xl:text-center">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 xl:hidden">Status</p>
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full
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
                                            {{ $isOnProcess ? 'On Process' : ($rental->status === 'approved' ? 'Approved Request' : ($rental->status === 'completed' ? 'Returned' : ucfirst($rental->status))) }}
                                        </span>
                                    </td>

                                    <td class="block pt-3 md:col-span-2 xl:table-cell xl:px-6 xl:py-4 xl:text-center">
                                        <div class="grid gap-2 sm:inline-grid sm:grid-cols-2 xl:grid-cols-1">
                                            <a href="{{ route('rental-requests.show', $rental) }}" class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-3 py-2.5 text-xs font-semibold text-white transition hover:bg-blue-700 sm:w-auto sm:px-4 xl:w-full xl:py-2">
                                                View Details
                                            </a>
                                            @if ($rental->status === 'completed')
                                                <button type="button" wire:click="deleteReturnedRental({{ $rental->id }})" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-lg border border-rose-200 px-3 py-2.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-rose-900/60 dark:text-rose-200 dark:hover:bg-rose-900/30 sm:w-auto sm:px-4 xl:w-full xl:py-2">
                                                    Delete
                                                </button>
                                            @else
                                                <a href="{{ route('renter.messages', ['rental' => $rental->id]) }}" class="inline-flex w-full items-center justify-center rounded-lg border border-blue-200 px-3 py-2.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-50 dark:border-blue-900/60 dark:text-blue-200 dark:hover:bg-blue-900/30 sm:w-auto sm:px-4 xl:w-full xl:py-2">
                                                    Send a message
                                                </a>
                                            @endif
                                        </div>
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
