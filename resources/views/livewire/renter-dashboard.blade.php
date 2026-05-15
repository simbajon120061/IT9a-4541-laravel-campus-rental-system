<div class="bg-slate-50 py-8 text-slate-950 dark:bg-slate-950 dark:text-slate-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div class="space-y-2">
                <p class="text-sm font-bold uppercase tracking-wider text-blue-500 dark:text-blue-300">Renter Portal</p>
                <h1 class="text-4xl font-extrabold tracking-normal text-blue-700 dark:text-white">Renter Dashboard</h1>
                <p class="text-lg text-slate-600 dark:text-slate-300">Browse items, track requests, and manage everything you are renting.</p>
            </div>

            <a href="{{ route('renter.marketplace') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Browse Marketplace
            </a>
        </div>

        <a href="{{ route('renter.my-rentals') }}" class="mt-8 flex items-center justify-between gap-4 rounded-lg border border-amber-200 bg-amber-50 p-5 text-amber-950 shadow-sm transition hover:border-amber-300 hover:bg-amber-100 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-100 dark:hover:bg-amber-950/70">
            <span>
                <span class="block text-sm font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">Due Soon Alert</span>
                <span class="mt-1 block text-base font-semibold">{{ $dueSoonRentals }} {{ \Illuminate\Support\Str::plural('rental', $dueSoonRentals) }} due within 7 days.</span>
                @if (! is_null($daysUntilNextDue))
                    <span class="mt-2 block text-sm font-bold text-amber-800 dark:text-amber-200">Nearest return: {{ $daysUntilNextDue }} {{ \Illuminate\Support\Str::plural('day', $daysUntilNextDue) }} left.</span>
                @else
                    <span class="mt-2 block text-sm font-bold text-amber-800 dark:text-amber-200">No rentals due in the next 7 days.</span>
                @endif
            </span>
            <span class="flex shrink-0 flex-col items-center justify-center rounded-lg bg-amber-100 px-4 py-3 text-amber-900 dark:bg-amber-900/70 dark:text-amber-100">
                <span class="text-3xl font-extrabold leading-none">{{ $daysUntilNextDue ?? 0 }}</span>
                <span class="mt-1 text-xs font-bold uppercase tracking-wider">{{ \Illuminate\Support\Str::plural('day', $daysUntilNextDue ?? 0) }} left</span>
            </span>
        </a>

        <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('renter.my-rentals') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-800/60 dark:hover:bg-blue-950/30">
                <p class="text-3xl font-extrabold text-slate-950 dark:text-white">{{ $activeRentals }}</p>
                <p class="mt-2 text-base text-slate-600 dark:text-slate-300">Active Rentals</p>
            </a>

            <a href="{{ route('renter.my-rentals') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-800/60 dark:hover:bg-blue-950/30">
                <p class="text-3xl font-extrabold text-slate-950 dark:text-white">{{ $pendingRequests }}</p>
                <p class="mt-2 text-base text-slate-600 dark:text-slate-300">Pending Requests</p>
            </a>

            <a href="{{ route('renter.my-rentals') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-800/60 dark:hover:bg-blue-950/30">
                <p class="text-3xl font-extrabold text-slate-950 dark:text-white">{{ $approvedRentals }}</p>
                <p class="mt-2 text-base text-slate-600 dark:text-slate-300">Approved Rentals</p>
            </a>

            <a href="{{ route('renter.my-rentals') }}" class="rounded-lg border border-amber-200 bg-white p-6 shadow-sm transition hover:border-amber-300 hover:bg-amber-50 dark:border-amber-800/60 dark:bg-slate-900 dark:hover:bg-amber-950/30">
                <p class="text-3xl font-extrabold text-slate-950 dark:text-white">{{ $dueSoonRentals }}</p>
                <p class="mt-2 text-base text-slate-600 dark:text-slate-300">Due Soon</p>
            </a>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-2">
            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-2xl font-extrabold text-slate-950 dark:text-white">Renter Actions</h2>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <a href="{{ route('renter.marketplace') }}" class="rounded-lg border border-blue-200 bg-blue-50 px-5 py-5 transition hover:border-blue-300 hover:bg-blue-100 dark:border-blue-800/60 dark:bg-blue-950/40 dark:hover:bg-blue-950/70">
                        <span class="block text-lg font-bold text-blue-800 dark:text-blue-300">Marketplace</span>
                        <span class="mt-2 block text-sm text-blue-700 dark:text-blue-200">Find items available from other students.</span>
                    </a>

                    <a href="{{ route('renter.my-rentals') }}" class="rounded-lg border border-emerald-200 bg-emerald-50 px-5 py-5 transition hover:border-emerald-300 hover:bg-emerald-100 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:hover:bg-emerald-950/70">
                        <span class="block text-lg font-bold text-emerald-950 dark:text-emerald-200">My Rentals</span>
                        <span class="mt-2 block text-sm text-emerald-700 dark:text-emerald-300">Review pending, approved, and active rentals.</span>
                    </a>
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-2xl font-extrabold text-slate-950 dark:text-white">Recent Rentals</h2>

                <div class="mt-6 space-y-4">
                    @forelse ($recentRentals as $rental)
                        <a href="{{ route('rental-requests.show', $rental) }}" class="block rounded-lg bg-slate-50 p-4 transition hover:bg-slate-100 dark:bg-slate-800/70 dark:hover:bg-slate-800">
                            <span class="block font-bold text-slate-950 dark:text-white">{{ $rental->item?->name ?? 'Unknown item' }}</span>
                            <span class="mt-1 block text-sm text-slate-600 dark:text-slate-300">{{ ucfirst($rental->status) }} with {{ $rental->item?->user?->name ?? 'Unknown lister' }}</span>
                        </a>
                    @empty
                        <div class="rounded-lg bg-slate-50 p-5 text-base text-slate-600 dark:bg-slate-800/70 dark:text-slate-300">
                            No rentals yet.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
