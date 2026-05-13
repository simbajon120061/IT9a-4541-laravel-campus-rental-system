<div class="bg-slate-50 py-8 text-slate-950 dark:bg-slate-950 dark:text-slate-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-bold uppercase tracking-wider text-violet-600 dark:text-violet-300">Lister Portal</p>
                <h1 class="mt-2 text-4xl font-extrabold tracking-normal text-slate-950 dark:text-white">Payments</h1>
                <p class="mt-2 text-lg text-slate-600 dark:text-slate-300">Track and record renter payments across your listings.</p>
            </div>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-900 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                <span class="text-sm font-semibold">Collected: &#8369;{{ number_format($totalPaid, 2) }}</span>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-900/20 dark:text-emerald-200">
                {{ session('message') }}
            </div>
        @endif

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <button wire:click="setFilter('outstanding')" class="rounded-lg border p-5 text-left transition {{ $filterStatus === 'outstanding' ? 'border-amber-300 bg-amber-50 text-amber-900 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-200' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <span class="block text-2xl font-extrabold">{{ $outstandingCount }}</span>
                <span class="mt-1 block text-sm font-semibold">Outstanding</span>
            </button>
            <button wire:click="setFilter('partial')" class="rounded-lg border p-5 text-left transition {{ $filterStatus === 'partial' ? 'border-blue-300 bg-blue-50 text-blue-900 dark:border-blue-700 dark:bg-blue-950/40 dark:text-blue-200' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <span class="block text-2xl font-extrabold">{{ $partialCount }}</span>
                <span class="mt-1 block text-sm font-semibold">Partial</span>
            </button>
            <button wire:click="setFilter('fully_paid')" class="rounded-lg border p-5 text-left transition {{ $filterStatus === 'fully_paid' ? 'border-emerald-300 bg-emerald-50 text-emerald-900 dark:border-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <span class="block text-2xl font-extrabold">{{ $paidCount }}</span>
                <span class="mt-1 block text-sm font-semibold">Fully Paid</span>
            </button>
        </div>

        <div class="mt-6 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:flex-row">
            <input type="text" wire:model.live.debounce.300ms="search" class="min-w-0 flex-1 rounded-lg border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-950 focus:border-violet-500 focus:ring-violet-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Search by item or renter name">
            <button wire:click="setFilter('all')" class="rounded-lg border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Show All</button>
        </div>

        <div class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @if ($payments->isEmpty())
                <div class="p-10 text-center text-slate-600 dark:text-slate-300">No payment records found.</div>
            @else
                <div class="overflow-hidden">
                    <table class="block w-full md:table">
                        <thead class="hidden bg-slate-100 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-300 md:table-header-group">
                            <tr>
                                <th class="px-5 py-4 text-left font-semibold">Item</th>
                                <th class="px-5 py-4 text-left font-semibold">Renter</th>
                                <th class="px-5 py-4 text-right font-semibold">Total</th>
                                <th class="px-5 py-4 text-right font-semibold">Paid</th>
                                <th class="px-5 py-4 text-center font-semibold">Payment Status</th>
                                <th class="px-5 py-4 text-right font-semibold">Record Payment</th>
                            </tr>
                        </thead>
                        <tbody class="block divide-y divide-slate-200 dark:divide-slate-800 md:table-row-group">
                            @foreach ($payments as $payment)
                                <tr class="block p-4 text-sm text-slate-700 dark:text-slate-300 md:table-row md:p-0">
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
                                    <td class="block pt-3 md:table-cell md:px-5 md:py-4">
                                        @if ($payment->payment_status !== 'fully_paid')
                                            <div class="flex flex-col gap-2 sm:flex-row md:justify-end">
                                                <input type="number" step="0.01" min="0.01" wire:model.defer="paymentAmounts.{{ $payment->id }}" class="w-full rounded-md border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white sm:w-28" placeholder="Amount">
                                                <button wire:click="recordPayment({{ $payment->id }})" class="rounded-md bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">Save</button>
                                            </div>
                                            @error('paymentAmounts.' . $payment->id)
                                                <p class="mt-1 text-xs text-rose-600 dark:text-rose-400 md:text-right">{{ $message }}</p>
                                            @enderror
                                        @else
                                            <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 md:text-right">Complete</p>
                                        @endif
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
</div>
