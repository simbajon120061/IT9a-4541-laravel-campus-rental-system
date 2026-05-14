<div class="bg-slate-50 py-8 text-slate-950 dark:bg-slate-950 dark:text-slate-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-bold uppercase tracking-wider text-violet-600 dark:text-violet-300">Lister Portal</p>
                <h1 class="mt-2 text-4xl font-extrabold tracking-normal text-slate-950 dark:text-white">Payments</h1>
                <p class="mt-2 text-lg text-slate-600 dark:text-slate-300">Track and record renter payments across your listings.</p>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-900/20 dark:text-emerald-200">
                {{ session('message') }}
            </div>
        @endif

        <div class="mt-6 grid gap-2 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
            <div class="rounded-lg border border-slate-200 bg-white p-3 text-slate-700 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                <span class="block text-lg font-extrabold text-slate-950 dark:text-white">&#8369;{{ number_format($totalAmount, 2) }}</span>
                <span class="mt-1 block text-[11px] font-semibold">Total</span>
            </div>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-emerald-900 shadow-sm dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                <span class="block text-lg font-extrabold">&#8369;{{ number_format($totalPaid, 2) }}</span>
                <span class="mt-1 block text-[11px] font-semibold">Collected</span>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-3 text-slate-700 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                <span class="block text-lg font-extrabold text-slate-950 dark:text-white">{{ $totalTransactions }}</span>
                <span class="mt-1 block text-[11px] font-semibold">Total Transactions</span>
            </div>
            <button wire:click="setFilter('outstanding')" class="rounded-lg border p-3 text-left transition {{ $filterStatus === 'outstanding' ? 'border-amber-300 bg-amber-50 text-amber-900 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-200' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <span class="block text-lg font-extrabold">{{ $outstandingCount }}</span>
                <span class="mt-1 block text-[11px] font-semibold">Outstanding</span>
            </button>
            <button wire:click="setFilter('partial')" class="rounded-lg border p-3 text-left transition {{ $filterStatus === 'partial' ? 'border-blue-300 bg-blue-50 text-blue-900 dark:border-blue-700 dark:bg-blue-950/40 dark:text-blue-200' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <span class="block text-lg font-extrabold">{{ $partialCount }}</span>
                <span class="mt-1 block text-[11px] font-semibold">Partial</span>
            </button>
            <button wire:click="setDueFilter('overdue')" class="rounded-lg border p-3 text-left shadow-sm transition {{ $dueFilter === 'overdue' ? 'border-rose-300 bg-rose-50 text-rose-900 dark:border-rose-700 dark:bg-rose-950/40 dark:text-rose-200' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <span class="block text-lg font-extrabold">{{ $overduePaymentsCount }}</span>
                <span class="mt-1 block text-[11px] font-semibold">Overdue Payments</span>
            </button>
            <button wire:click="setFilter('fully_paid')" class="rounded-lg border p-3 text-left transition {{ $filterStatus === 'fully_paid' ? 'border-emerald-300 bg-emerald-50 text-emerald-900 dark:border-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <span class="block text-lg font-extrabold">{{ $paidCount }}</span>
                <span class="mt-1 block text-[11px] font-semibold">Fully Paid</span>
            </button>
        </div>

        <div class="mt-4 grid gap-2 rounded-lg border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:grid-cols-[minmax(0,1fr)_11rem_11rem_11rem]">
            <input type="text" wire:model.live.debounce.300ms="search" class="min-w-0 rounded-lg border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-950 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Search by item or renter name">
            <select wire:change="setFilter($event.target.value)" class="rounded-lg border-slate-300 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                <option value="all">All payment status</option>
                <option value="outstanding" @selected($filterStatus === 'outstanding')>Outstanding</option>
                <option value="partial" @selected($filterStatus === 'partial')>Partial</option>
                <option value="fully_paid" @selected($filterStatus === 'fully_paid')>Fully paid</option>
            </select>
            <select wire:change="setDueFilter($event.target.value)" class="rounded-lg border-slate-300 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                <option value="all">All due dates</option>
                <option value="due_soon" @selected($dueFilter === 'due_soon')>Due soon</option>
                <option value="due_today" @selected($dueFilter === 'due_today')>Due today</option>
                <option value="overdue" @selected($dueFilter === 'overdue')>Overdue</option>
            </select>
            <input type="date" wire:model.live="dueDate" class="rounded-lg border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-950 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
        </div>

        <div class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @if ($payments->isEmpty())
                <div class="p-10 text-center text-slate-600 dark:text-slate-300">No payment records found.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="block w-full md:table md:min-w-[78rem]">
                        <thead class="hidden bg-slate-100 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-300 md:table-header-group">
                            <tr>
                                <th class="px-5 py-4 text-left font-semibold">Item</th>
                                <th class="px-5 py-4 text-left font-semibold">Renter</th>
                                <th class="px-5 py-4 text-right font-semibold">Total</th>
                                <th class="px-5 py-4 text-right font-semibold">Paid</th>
                                <th class="px-5 py-4 text-center font-semibold">Payment Status</th>
                                <th class="px-5 py-4 text-right font-semibold">Remaining Balance</th>
                                <th class="px-5 py-4 text-center font-semibold">Due Date</th>
                                <th class="px-5 py-4 text-right font-semibold">Record Payment</th>
                                <th class="px-5 py-4 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="block divide-y divide-slate-200 dark:divide-slate-800 md:table-row-group">
                            @foreach ($payments as $payment)
                                @php
                                    $remainingBalance = max(0, round((float) $payment->total_price - (float) ($payment->paid_amount ?? 0), 2));
                                    $isUnpaid = $payment->payment_status !== 'fully_paid';
                                    $isOverdue = $isUnpaid && $payment->end_date->isBefore(now()->startOfDay());
                                    $isDueToday = $isUnpaid && $payment->end_date->isToday();
                                    $isDueSoon = $isUnpaid && $payment->end_date->isFuture() && $payment->end_date->lte(now()->addDays(7));
                                @endphp
                                <tr id="payment-{{ $payment->id }}" class="block scroll-mt-28 p-4 text-sm text-slate-700 dark:text-slate-300 md:table-row md:p-0">
                                    <td class="block py-2 md:table-cell md:px-5 md:py-4">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Item</p>
                                        <p class="font-bold text-slate-950 dark:text-white">{{ $payment->item->name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ ucfirst($payment->status) }}</p>
                                    </td>
                                    <td class="block py-2 md:table-cell md:px-5 md:py-4">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Renter</p>
                                        {{ $payment->renter->name }}
                                    </td>
                                    <td class="block py-2 font-bold text-slate-950 dark:text-white md:table-cell md:px-5 md:py-4 md:text-right">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Total</p>
                                        &#8369;{{ number_format($payment->total_price, 2) }}
                                    </td>
                                    <td class="block py-2 md:table-cell md:px-5 md:py-4 md:text-right">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Paid</p>
                                        &#8369;{{ number_format((float) $payment->paid_amount, 2) }}
                                    </td>
                                    <td class="block py-2 md:table-cell md:px-5 md:py-4 md:text-center">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Payment Status</p>
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                            @if ($payment->payment_status === 'fully_paid') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200
                                            @elseif ($payment->payment_status === 'partial') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200
                                            @else bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200
                                            @endif">
                                            {{ str_replace('_', ' ', ucfirst($payment->payment_status)) }}
                                        </span>
                                    </td>
                                    <td class="block py-2 font-bold text-slate-950 dark:text-white md:table-cell md:px-5 md:py-4 md:text-right">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Remaining Balance</p>
                                        &#8369;{{ number_format($remainingBalance, 2) }}
                                    </td>
                                    <td class="block py-2 md:table-cell md:px-5 md:py-4 md:text-center">
                                        <p class="mb-1 text-[11px] font-semibold uppercase text-slate-400 md:hidden">Due Date</p>
                                        <div class="space-y-1">
                                            <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $payment->end_date->format('M d, Y') }}</p>
                                            @if ($isOverdue)
                                                <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800 dark:bg-rose-900/40 dark:text-rose-200">Overdue</span>
                                            @elseif ($isDueToday)
                                                <span class="inline-flex rounded-full bg-orange-100 px-2.5 py-1 text-xs font-semibold text-orange-800 dark:bg-orange-900/40 dark:text-orange-200">Due Today</span>
                                            @elseif ($isDueSoon)
                                                <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Due Soon</span>
                                            @else
                                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">Scheduled</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="block pt-3 md:table-cell md:px-5 md:py-4">
                                        @if ($payment->payment_status !== 'fully_paid')
                                            <div class="grid grid-cols-2 gap-2 md:flex md:justify-end">
                                                <button type="button" wire:click="openAddPaymentModal({{ $payment->id }})" class="inline-flex items-center justify-center rounded-md border border-blue-200 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-50 dark:border-blue-900/60 dark:text-blue-200 dark:hover:bg-blue-900/30">
                                                    Add Payment
                                                </button>
                                                <button type="button" wire:click="openFullPaymentModal({{ $payment->id }})" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">
                                                    Pay in Full
                                                </button>
                                            </div>
                                        @else
                                            <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 md:text-right">Complete</p>
                                        @endif
                                    </td>
                                    <td class="block pt-3 md:table-cell md:px-5 md:py-4">
                                        <div class="grid gap-2 md:justify-end">
                                            @if ($isOverdue)
                                                <button type="button" wire:click="sendOverduePaymentReminder({{ $payment->id }})" class="inline-flex items-center justify-center rounded-md border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50 dark:border-rose-900/60 dark:text-rose-200 dark:hover:bg-rose-900/30">
                                                    Send Overdue
                                                </button>
                                            @elseif ($isDueSoon || $isDueToday)
                                                <button type="button" wire:click="sendUpcomingDueReminder({{ $payment->id }})" class="inline-flex items-center justify-center rounded-md border border-amber-200 px-3 py-2 text-xs font-semibold text-amber-700 transition hover:bg-amber-50 dark:border-amber-900/60 dark:text-amber-200 dark:hover:bg-amber-900/30">
                                                    Send Reminder
                                                </button>
                                            @endif
                                            <a href="{{ route('rental-requests.show', $payment) }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                                View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($payments->hasPages())
            <div class="mt-6">{{ $payments->links() }}</div>
        @endif
    </div>

    @if ($selectedPayment)
        @php
            $remainingBalance = max(0, round((float) $selectedPayment->total_price - (float) ($selectedPayment->paid_amount ?? 0), 2));
        @endphp

        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="payment-modal-title">
            <div class="w-full max-w-md rounded-xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wider text-violet-600 dark:text-violet-300">Record Payment</p>
                        <h2 id="payment-modal-title" class="mt-1 text-xl font-extrabold text-slate-950 dark:text-white">
                            {{ $paymentMode === 'full' ? 'Pay in Full' : 'Add Payment' }}
                        </h2>
                    </div>
                    <button type="button" wire:click="closePaymentModal" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Cancel payment entry">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-700 dark:bg-slate-950 dark:text-slate-300">
                    <p class="font-bold text-slate-950 dark:text-white">{{ $selectedPayment->item->name }}</p>
                    <p class="mt-1">{{ $selectedPayment->renter->name }}</p>
                    <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="block font-semibold uppercase text-slate-400">Paid</span>
                            <span class="mt-1 block font-bold text-slate-900 dark:text-slate-100">&#8369;{{ number_format((float) $selectedPayment->paid_amount, 2) }}</span>
                        </div>
                        <div>
                            <span class="block font-semibold uppercase text-slate-400">Remaining</span>
                            <span class="mt-1 block font-bold text-slate-900 dark:text-slate-100">&#8369;{{ number_format($remainingBalance, 2) }}</span>
                        </div>
                    </div>
                </div>

                <form wire:submit.prevent="savePayment" class="mt-5 space-y-4">
                    <div>
                        <label for="paymentAmount" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Payment amount</label>
                        <input
                            id="paymentAmount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            max="{{ $remainingBalance }}"
                            wire:model.defer="paymentAmount"
                            @if ($paymentMode === 'full') readonly @endif
                            class="h-12 w-full rounded-lg border-slate-300 bg-white px-4 text-sm text-slate-950 focus:border-violet-500 focus:ring-violet-500 read-only:bg-slate-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:read-only:bg-slate-800"
                            placeholder="Enter amount"
                        >
                        @error('paymentAmount')
                            <p class="mt-2 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" wire:click="closePaymentModal" class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-300 px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                            Cancel
                        </button>
                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
