<x-guest-layout>
    @php
        $portal = $portal ?? null;
    @endphp

    <div class="min-h-screen bg-slate-50 text-slate-950 dark:bg-slate-950 dark:text-slate-100">
        <div class="mx-auto grid min-h-screen max-w-7xl grid-cols-1 lg:grid-cols-2">
            <section class="hidden border-r border-slate-200 bg-white px-10 py-12 dark:border-slate-800 dark:bg-slate-900 lg:flex lg:flex-col lg:justify-between xl:px-14">
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-violet-600 text-white shadow-lg shadow-blue-600/25">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 8-9-5-9 5m18 0-9 5m9-5v8l-9 5m0-8L3 8m9 5v8M3 8v8l9 5" />
                        </svg>
                    </span>
                    <span class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">Campus<span class="text-violet-600 dark:text-violet-400">Rent</span></span>
                </a>

                <div class="max-w-xl">
                    <p class="inline-flex rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 ring-1 ring-blue-100 dark:bg-blue-500/15 dark:text-blue-300 dark:ring-blue-500/20">Campus marketplace</p>
                    <h1 class="mt-6 text-5xl font-extrabold tracking-normal text-slate-950 dark:text-white">Welcome back to your rental hub.</h1>
                    <p class="mt-5 text-xl leading-relaxed text-slate-600 dark:text-slate-300">Manage requests, track borrowed items, and keep your campus listings moving from one clean dashboard.</p>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 dark:border-blue-800/50 dark:bg-blue-950/40">
                        <p class="text-2xl font-extrabold text-blue-700 dark:text-blue-300">Fast</p>
                        <p class="mt-2 text-sm text-blue-700/80 dark:text-blue-200">Request items in minutes.</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 dark:border-emerald-800/50 dark:bg-emerald-950/40">
                        <p class="text-2xl font-extrabold text-emerald-800 dark:text-emerald-300">Local</p>
                        <p class="mt-2 text-sm text-emerald-700 dark:text-emerald-200">Built for students.</p>
                    </div>
                    <div class="rounded-2xl border border-purple-100 bg-purple-50 p-5 dark:border-purple-800/50 dark:bg-purple-950/40">
                        <p class="text-2xl font-extrabold text-purple-800 dark:text-purple-300">Secure</p>
                        <p class="mt-2 text-sm text-purple-700 dark:text-purple-200">Campus accounts only.</p>
                    </div>
                </div>
            </section>

            <main class="flex items-center justify-center px-4 py-10 sm:px-6 lg:px-12 xl:px-16">
                <div class="w-full max-w-lg">
                    <div class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-violet-600 text-white shadow-lg shadow-blue-600/25">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 8-9-5-9 5m18 0-9 5m9-5v8l-9 5m0-8L3 8m9 5v8M3 8v8l9 5" />
                            </svg>
                        </span>
                        <span class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">Campus<span class="text-violet-600 dark:text-violet-400">Rent</span></span>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/40 sm:p-10">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wider text-blue-600 dark:text-blue-300">
                                {{ $portal === 'lister' ? 'Lister Portal' : ($portal === 'renter' ? 'Renter Portal' : 'Campus Portal') }}
                            </p>
                            <h2 class="mt-2 text-3xl font-extrabold tracking-normal text-slate-950 dark:text-white">Sign in</h2>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                {{ $portal === 'lister' ? 'Access your Lister Account.' : ($portal === 'renter' ? 'Access your Renter Account.' : 'Use your University of Mindanao email to continue.') }}
                            </p>
                        </div>

                        <x-validation-errors class="mt-6 text-left" />

                        <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-7">
                            @csrf

                            <div class="space-y-2">
                                <x-label for="email" value="{{ __('Email Address') }}" class="text-sm font-semibold text-slate-700 dark:text-slate-200" />
                                <x-input id="email" class="block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-950 transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white" type="email" name="email" :value="old('email')" required autofocus placeholder="name@umindanao.edu.ph" />
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-4">
                                    <x-label for="password" value="{{ __('Password') }}" class="text-sm font-semibold text-slate-700 dark:text-slate-200" />
                                    @if (Route::has('password.request'))
                                        <a class="text-sm font-semibold text-blue-600 transition hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-200" href="{{ route('password.request') }}">
                                            {{ __('Forgot password?') }}
                                        </a>
                                    @endif
                                </div>
                                <x-input id="password" class="block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-950 transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white" type="password" name="password" required placeholder="********" />
                            </div>

                            <div class="-mt-2">
                                <label for="remember_me" class="inline-flex items-center gap-3">
                                    <x-checkbox id="remember_me" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500/20 dark:border-slate-700" />
                                    <span class="text-sm text-slate-600 dark:text-slate-300">{{ __('Remember me') }}</span>
                                </label>
                            </div>

                            <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-blue-600 to-violet-600 px-5 py-3.5 font-bold text-white shadow-lg shadow-blue-600/25 transition hover:-translate-y-0.5 hover:shadow-xl">
                                Sign in
                            </button>
                        </form>

                        <div class="mt-7 border-t border-slate-100 pt-6 text-center text-sm text-slate-600 dark:border-slate-800 dark:text-slate-300">
                            <a href="{{ route('login.options') }}" class="mb-4 block font-bold text-slate-700 transition hover:text-slate-950 dark:text-slate-200 dark:hover:text-white">Choose another portal</a>
                            Don't have an account?
                            <a href="{{ route('register') }}" class="font-bold text-blue-600 transition hover:text-blue-700 dark:text-blue-300">Create one</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</x-guest-layout>
