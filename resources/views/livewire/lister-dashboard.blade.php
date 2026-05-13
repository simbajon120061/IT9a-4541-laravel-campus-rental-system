<div class="bg-slate-50 py-8 text-slate-950 dark:bg-slate-950 dark:text-slate-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div class="space-y-2">
                <p class="text-sm font-bold uppercase tracking-wider text-violet-600 dark:text-violet-300">Lister Portal</p>
                <h1 class="text-4xl font-extrabold tracking-normal text-slate-950 dark:text-white">Lister Dashboard</h1>
                <p class="text-lg text-slate-600 dark:text-slate-300">Manage listed items, rental requests, payments, and inventory activity.</p>
            </div>

            <a href="{{ route('add-item') }}" class="inline-flex items-center justify-center rounded-lg bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:bg-violet-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Listing
            </a>
        </div>

        <a href="{{ route('lister.inventory') }}" class="mt-8 flex items-center justify-between gap-4 rounded-lg border border-amber-200 bg-amber-50 p-5 text-amber-950 shadow-sm transition hover:border-amber-300 hover:bg-amber-100 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-100 dark:hover:bg-amber-950/70">
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

        <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-6">
            <a href="{{ route('lister.my-listings') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm transition hover:border-violet-200 hover:bg-violet-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-violet-800/60 dark:hover:bg-violet-950/30">
                <p class="text-3xl font-extrabold text-slate-950 dark:text-white">{{ $totalListings }}</p>
                <p class="mt-2 text-base text-slate-600 dark:text-slate-300">Total Listings</p>
            </a>

            <a href="{{ route('lister.my-listings') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm transition hover:border-violet-200 hover:bg-violet-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-violet-800/60 dark:hover:bg-violet-950/30">
                <p class="text-3xl font-extrabold text-slate-950 dark:text-white">{{ $availableListings }}</p>
                <p class="mt-2 text-base text-slate-600 dark:text-slate-300">Available Listings</p>
            </a>

            <a href="{{ route('lister.rental-requests') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm transition hover:border-violet-200 hover:bg-violet-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-violet-800/60 dark:hover:bg-violet-950/30">
                <p class="text-3xl font-extrabold text-slate-950 dark:text-white">{{ $pendingRequests }}</p>
                <p class="mt-2 text-base text-slate-600 dark:text-slate-300">Pending Requests</p>
            </a>

            <a href="{{ route('lister.inventory', ['filter' => 'approved']) }}{{ $nextOnProcessRental ? '#rental-'.$nextOnProcessRental->id : '' }}" class="rounded-lg border border-blue-200 bg-white p-6 shadow-sm transition hover:border-blue-300 hover:bg-blue-50 dark:border-blue-800/60 dark:bg-slate-900 dark:hover:bg-blue-950/30">
                <p class="text-3xl font-extrabold text-slate-950 dark:text-white">{{ $onProcessRentals }}</p>
                <p class="mt-2 text-base text-slate-600 dark:text-slate-300">On Process</p>
                <p class="mt-2 text-xs font-semibold text-blue-700 dark:text-blue-300">Message renters and monitor handoff.</p>
            </a>

            <a href="{{ route('lister.inventory') }}" class="rounded-lg border border-amber-200 bg-white p-6 shadow-sm transition hover:border-amber-300 hover:bg-amber-50 dark:border-amber-800/60 dark:bg-slate-900 dark:hover:bg-amber-950/30">
                <p class="text-3xl font-extrabold text-slate-950 dark:text-white">{{ $dueSoonRentals }}</p>
                <p class="mt-2 text-base text-slate-600 dark:text-slate-300">Due Soon</p>
            </a>

            <a href="{{ route('lister.payments') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm transition hover:border-violet-200 hover:bg-violet-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-violet-800/60 dark:hover:bg-violet-950/30">
                <p class="text-3xl font-extrabold text-slate-950 dark:text-white">&#8369;{{ number_format($totalEarnings, 0) }}</p>
                <p class="mt-2 text-base text-slate-600 dark:text-slate-300">Total Earnings</p>
            </a>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-2">
            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-2xl font-extrabold text-slate-950 dark:text-white">Lister Actions</h2>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <a href="{{ route('lister.my-listings') }}" class="rounded-lg border border-violet-200 bg-violet-50 px-5 py-5 transition hover:border-violet-300 hover:bg-violet-100 dark:border-violet-800/60 dark:bg-violet-950/40 dark:hover:bg-violet-950/70">
                        <span class="block text-lg font-bold text-violet-900 dark:text-violet-200">My Listings</span>
                        <span class="mt-2 block text-sm text-violet-700 dark:text-violet-300">Create, edit, and review listed items.</span>
                    </a>

                    <a href="{{ route('lister.inventory') }}" class="rounded-lg border border-emerald-200 bg-emerald-50 px-5 py-5 transition hover:border-emerald-300 hover:bg-emerald-100 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:hover:bg-emerald-950/70">
                        <span class="block text-lg font-bold text-emerald-950 dark:text-emerald-200">Inventory</span>
                        <span class="mt-2 block text-sm text-emerald-700 dark:text-emerald-300">Approve requests and record payments.</span>
                    </a>
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-2xl font-extrabold text-slate-950 dark:text-white">Recent Requests</h2>

                <div class="mt-6 space-y-4">
                    @forelse ($recentRequests as $rental)
                        <a href="{{ route('rental-requests.show', $rental) }}" class="block rounded-lg bg-slate-50 p-4 transition hover:bg-slate-100 dark:bg-slate-800/70 dark:hover:bg-slate-800">
                            <span class="block font-bold text-slate-950 dark:text-white">{{ $rental->item?->name ?? 'Unknown item' }}</span>
                            <span class="mt-1 block text-sm text-slate-600 dark:text-slate-300">{{ ucfirst($rental->status) }} request from {{ $rental->renter?->name ?? 'Unknown renter' }}</span>
                        </a>
                    @empty
                        <div class="rounded-lg bg-slate-50 p-5 text-base text-slate-600 dark:bg-slate-800/70 dark:text-slate-300">
                            No lister activity yet.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
