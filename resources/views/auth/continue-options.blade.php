<x-guest-layout>
    <div class="min-h-screen bg-slate-50 px-4 py-12 text-slate-950 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8">
        <div class="mx-auto flex min-h-[calc(100vh-6rem)] max-w-5xl flex-col justify-center">
            <a href="{{ route('landing') }}" class="mb-10 inline-flex items-center gap-3 self-center">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-violet-600 text-white shadow-lg shadow-blue-600/25">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 8-9-5-9 5m18 0-9 5m9-5v8l-9 5m0-8L3 8m9 5v8M3 8v8l9 5" />
                    </svg>
                </span>
                <span class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">Campus<span class="text-violet-600 dark:text-violet-400">Rent</span></span>
            </a>

            <div class="text-center">
                <p class="text-sm font-bold uppercase tracking-wider text-blue-600 dark:text-blue-300">Account ready</p>
                <h1 class="mt-3 text-4xl font-extrabold tracking-normal text-slate-950 dark:text-white">How would you like to continue?</h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600 dark:text-slate-300">Choose the workspace you want to open first. You can switch between renter and lister areas anytime.</p>
            </div>

            <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-2">
                <form method="POST" action="{{ route('portal.continue', ['portal' => 'lister']) }}">
                    @csrf
                    <button type="submit" class="group flex h-full w-full flex-col items-center justify-center rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm transition hover:-translate-y-1 hover:border-violet-300 hover:shadow-xl hover:shadow-violet-600/10 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-violet-700">
                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-xl bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </span>
                        <span class="mt-6 block text-2xl font-extrabold text-slate-950 dark:text-white">Lister Portal</span>
                        <span class="mt-2 block text-base font-semibold text-slate-700 dark:text-slate-200">Manage items you want to rent out.</span>
                        <span class="mt-3 block text-sm leading-6 text-slate-600 dark:text-slate-300">Add listings, track rental requests, manage inventory, and message renters.</span>
                        <span class="mt-6 inline-flex font-bold text-violet-700 transition dark:text-violet-300">Continue as Lister</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('portal.continue', ['portal' => 'renter']) }}">
                    @csrf
                    <button type="submit" class="group flex h-full w-full flex-col items-center justify-center rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm transition hover:-translate-y-1 hover:border-blue-300 hover:shadow-xl hover:shadow-blue-600/10 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-700">
                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </span>
                        <span class="mt-6 block text-2xl font-extrabold text-slate-950 dark:text-white">Renter Portal</span>
                        <span class="mt-2 block text-base font-semibold text-slate-700 dark:text-slate-200">Find and rent campus items.</span>
                        <span class="mt-3 block text-sm leading-6 text-slate-600 dark:text-slate-300">Browse the marketplace, request rentals, check bookings, and message item owners.</span>
                        <span class="mt-6 inline-flex font-bold text-blue-700 transition dark:text-blue-300">Continue as Renter</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
