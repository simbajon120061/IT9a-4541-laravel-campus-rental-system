<x-guest-layout>
    <div class="min-h-screen bg-slate-50 text-slate-950 dark:bg-slate-950 dark:text-slate-100">
        <div class="mx-auto grid min-h-screen max-w-7xl grid-cols-1 lg:grid-cols-2">
            <section class="hidden border-r border-slate-200 bg-white px-10 py-6 dark:border-slate-800 dark:bg-slate-900 lg:flex lg:flex-col xl:px-14">
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-violet-600 text-white shadow-lg shadow-blue-600/25">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 8-9-5-9 5m18 0-9 5m9-5v8l-9 5m0-8L3 8m9 5v8M3 8v8l9 5" />
                        </svg>
                    </span>
                    <span class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">Campus<span class="text-violet-600 dark:text-violet-400">Rent</span></span>
                </a>

                <div class="mt-16 max-w-xl">
                    <p class="inline-flex rounded-full bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/20">Student sharing made simple</p>
                    <h1 class="mt-6 text-5xl font-extrabold tracking-normal text-slate-950 dark:text-white">Join the campus rental community.</h1>
                    <p class="mt-5 text-xl leading-relaxed text-slate-600 dark:text-slate-300">Borrow what you need, lend what you own, and keep useful items circulating around campus.</p>
                </div>

                <div class="mt-16 rounded-2xl border border-slate-200 bg-slate-50 p-6 dark:border-slate-800 dark:bg-slate-950">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-3xl font-extrabold text-blue-600 dark:text-blue-300">01</p>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Create account</p>
                        </div>
                        <div>
                            <p class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-300">02</p>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Browse items</p>
                        </div>
                        <div>
                            <p class="text-3xl font-extrabold text-purple-600 dark:text-purple-300">03</p>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Rent or list</p>
                        </div>
                    </div>
                </div>
            </section>

            <main class="flex h-screen items-center justify-center overflow-hidden px-4 py-8 sm:px-6 lg:px-12 xl:px-16">
                <div class="w-full max-w-lg">
                    <div class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-violet-600 text-white shadow-lg shadow-blue-600/25">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 8-9-5-9 5m18 0-9 5m9-5v8l-9 5m0-8L3 8m9 5v8M3 8v8l9 5" />
                            </svg>
                        </span>
                        <span class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">Campus<span class="text-violet-600 dark:text-violet-400">Rent</span></span>
                    </div>

                    <div class="max-h-[calc(100vh-10rem)] overflow-y-auto overscroll-contain rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 dark:shadow-slate-950/40 sm:p-9 lg:max-h-[calc(100vh-4rem)]">
                        <div>
                            <h2 class="text-3xl font-extrabold tracking-normal text-slate-950 dark:text-white">Create account</h2>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Sign up with your University of Mindanao email.</p>
                        </div>

                        <x-validation-errors class="mt-6 text-left" />

                        <form method="POST" action="{{ route('register') }}" class="mt-7 space-y-8">
                            @csrf

                            <div class="grid grid-cols-1 gap-6">
                                <div class="space-y-2 pt-4">
                                    <x-label for="first_name" value="{{ __('First Name') }}" class="text-sm font-semibold text-slate-700 dark:text-slate-200" />
                                    <x-input id="first_name" class="block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-950 transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="given-name" placeholder="Juan" />
                                </div>

                                <div class="space-y-2 pt-4">
                                    <x-label for="last_name" value="{{ __('Last Name') }}" class="text-sm font-semibold text-slate-700 dark:text-slate-200" />
                                    <x-input id="last_name" class="block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-950 transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" placeholder="Dela Cruz" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6">
                                <div class="space-y-2 pt-4">
                                    <x-label for="email" value="{{ __('Email Address') }}" class="text-sm font-semibold text-slate-700 dark:text-slate-200" />
                                    <x-input id="email" class="block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-950 transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="juan@umindanao.edu.ph" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6">
                                <div class="space-y-2" pt-4>
                                    <x-label for="phone_number" value="{{ __('Phone Number') }}" class="text-sm font-semibold text-slate-700 dark:text-slate-200" />
                                    <x-input id="phone_number" class="block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-950 transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white" type="tel" name="phone_number" :value="old('phone_number')" required maxlength="11" inputmode="numeric" pattern="[0-9]{11}" autocomplete="tel" placeholder="09123456789" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6">
                                <div class="space-y-2" pt-4>
                                    <x-label for="course" value="{{ __('Program') }}" class="text-sm font-semibold text-slate-700 dark:text-slate-200" />
                                    <select id="course" name="course" required class="block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-950 transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                        <option value="">Select Program</option>
                                        @foreach (\App\Models\User::PROGRAMS as $program)
                                            <option value="{{ $program }}" @selected(old('course') === $program)>{{ $program }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-2" pt-4>
                                <x-label for="year_level" value="{{ __('Year Level') }}" class="text-sm font-semibold text-slate-700 dark:text-slate-200" />
                                <select id="year_level" name="year_level" required class="block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-950 transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                    <option value="">Select Year Level</option>
                                    @foreach (\App\Models\User::SCHOOL_LEVELS as $schoolLevel)
                                        <option value="{{ $schoolLevel }}" @selected(old('year_level') === $schoolLevel)>{{ $schoolLevel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2 pt-2">
                                <x-label for="password" value="{{ __('Password') }}" class="text-sm font-semibold text-slate-700 dark:text-slate-200" />
                                <x-input id="password" class="block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-950 transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white" type="password" name="password" required placeholder="********" />
                            </div>

                            <div class="space-y-2 pt-4">
                                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" class="text-sm font-semibold text-slate-700 dark:text-slate-200" />
                                <x-input id="password_confirmation" class="block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-950 transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-950 dark:text-white" type="password" name="password_confirmation" required placeholder="********" />
                            </div>

                            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                                <div class="pt-1">
                                    <x-label for="terms">
                                        <div class="flex items-start gap-3 rounded-xl bg-slate-50 p-4 dark:bg-slate-950">
                                            <x-checkbox name="terms" id="terms" required class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20 dark:border-slate-700" />
                                            <div class="text-xs leading-relaxed text-slate-600 dark:text-slate-300">
                                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300">'.__('Terms').'</a>',
                                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-300">'.__('Privacy').'</a>',
                                                ]) !!}
                                            </div>
                                        </div>
                                    </x-label>
                                </div>
                            @endif

                            <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-blue-600 to-violet-600 px-5 py-3.5 font-bold text-white shadow-lg shadow-blue-600/25 transition hover:-translate-y-0.5 hover:shadow-xl">
                                Create Account
                            </button>
                        </form>

                        <div class="mt-7 border-t border-slate-100 pt-6 text-center text-sm text-slate-600 dark:border-slate-800 dark:text-slate-300">
                            Already have an account?
                            <a href="{{ route('login.options') }}" class="font-bold text-blue-600 transition hover:text-blue-700 dark:text-blue-300">Sign in</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</x-guest-layout>
