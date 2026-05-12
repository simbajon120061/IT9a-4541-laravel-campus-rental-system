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

        <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-3xl font-extrabold text-slate-950 dark:text-white">{{ $totalListings }}</p>
                <p class="mt-2 text-base text-slate-600 dark:text-slate-300">Total Listings</p>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-3xl font-extrabold text-slate-950 dark:text-white">{{ $availableListings }}</p>
                <p class="mt-2 text-base text-slate-600 dark:text-slate-300">Available Listings</p>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-3xl font-extrabold text-slate-950 dark:text-white">{{ $pendingRequests }}</p>
                <p class="mt-2 text-base text-slate-600 dark:text-slate-300">Pending Requests</p>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-3xl font-extrabold text-slate-950 dark:text-white">&#8369;{{ number_format($totalEarnings, 0) }}</p>
                <p class="mt-2 text-base text-slate-600 dark:text-slate-300">Total Earnings</p>
            </div>
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
