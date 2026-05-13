<div class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased transition-colors dark:bg-slate-950 dark:text-slate-100">
    <nav x-data="{ open: false }" class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/85 backdrop-blur-xl dark:border-slate-700/80 dark:bg-slate-900/85">
    <div class="mx-auto flex min-h-16 w-full max-w-none flex-wrap items-center gap-2 px-3 py-2 sm:h-16 sm:flex-nowrap sm:px-4 sm:py-0 lg:px-6">
        
        <div class="flex min-w-0 items-center sm:flex-1">
            <a href="{{ route('renter.dashboard') }}" class="group flex items-center gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-violet-600 text-white shadow-lg shadow-blue-600/25 transition-all duration-300 group-hover:-translate-y-0.5 sm:h-11 sm:w-11 sm:rounded-xl">
                    <svg class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 8-9-5-9 5m18 0-9 5m9-5v8l-9 5m0-8L3 8m9 5v8M3 8v8l9 5" />
                    </svg>
                </span>
                <span class="hidden truncate text-xl font-extrabold tracking-normal text-blue-600 sm:inline sm:text-2xl dark:text-blue-400">Campus<span class="text-violet-600 dark:text-violet-400">Rent</span></span>
            </a>
        </div>

        <div class="order-3 hidden w-full min-w-0 items-center overflow-x-auto border-t border-slate-100 pt-2 sm:order-none sm:flex sm:w-auto sm:flex-1 sm:justify-center sm:border-t-0 sm:pt-0">
            @auth
                <div class="flex min-w-max items-center gap-1 rounded-full bg-slate-50 p-1 ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700 sm:min-w-0">
                    <a href="{{ route('renter.dashboard') }}" class="{{ request()->routeIs('dashboard', 'renter.dashboard') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }} inline-flex h-10 min-w-10 items-center justify-center gap-2 rounded-full px-3 text-sm font-semibold transition xl:px-4" title="Renter Dashboard">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l8-8 8 8M5 10v10h14V10" />
                        </svg>
                        <span class="hidden whitespace-nowrap xl:inline">Renter Dashboard</span>
                    </a>
                    <a href="{{ route('renter.marketplace') }}" class="{{ request()->routeIs('home', 'renter.marketplace', 'categories.show') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }} inline-flex h-10 min-w-10 items-center justify-center gap-2 rounded-full px-3 text-sm font-semibold transition xl:px-4" title="Marketplace">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span class="hidden whitespace-nowrap xl:inline">Marketplace</span>
                    </a>
                    <a href="{{ route('renter.my-rentals') }}" class="{{ request()->routeIs('my-rentals', 'renter.my-rentals') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }} inline-flex h-10 min-w-10 items-center justify-center gap-2 rounded-full px-3 text-sm font-semibold transition xl:px-4" title="My Rentals">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="hidden whitespace-nowrap xl:inline">My Rentals</span>
                    </a>
                </div>
            @endauth
        </div>

        <div class="order-2 ml-auto flex items-center justify-end gap-2 sm:order-none sm:flex-1 sm:gap-3">
            @auth
                @livewire('notifications-dropdown')
                <a href="{{ route('renter.messages') }}" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-600 ring-1 ring-slate-200 transition hover:bg-white hover:text-slate-900 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700 dark:hover:bg-slate-700 dark:hover:text-white" aria-label="Messages">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10.5h8M8 14h5m-8 5 3.5-3.5H18a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v5.5a3 3 0 0 0 2 2.83V19Z" />
                    </svg>
                </a>
                <x-dark-mode-toggle class="hidden sm:inline-flex" />
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" class="group inline-flex items-center rounded-full px-1 py-1 ring-1 ring-transparent transition hover:ring-slate-200 dark:hover:ring-slate-700">
                            <x-profile-avatar :user="Auth::user()" size="nav" showChevron />
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <div class="block px-4 py-2 text-xs text-gray-400 uppercase tracking-wider">
                            {{ __('Manage Account') }}
                        </div>

                        <x-dropdown-link href="{{ route('profile.show') }}" class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 me-2 text-blue-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            {{ __('Profile Settings') }}
                        </x-dropdown-link>

                        <div class="border-t border-gray-200 dark:border-slate-700"></div>

                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();" class="flex items-center text-red-600 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 me-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                </svg>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
                <button @click="open = !open" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100 sm:hidden" aria-label="Toggle navigation menu">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /><path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            @else
                <x-dark-mode-toggle />
                <a href="{{ route('login.options') }}" class="text-sm font-medium text-slate-600 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">Sign In</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-gradient-to-r from-blue-600 to-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-600/30 transition hover:-translate-y-0.5 hover:shadow-xl">Sign Up</a>
            @endauth
        </div>
    </div>

    @auth
        <div x-show="open" x-cloak class="border-t border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900 sm:hidden">
            <div class="flex flex-col gap-2">
                <a href="{{ route('renter.dashboard') }}" class="{{ request()->routeIs('dashboard', 'renter.dashboard') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }} rounded-lg px-4 py-3 text-sm font-bold">Renter Dashboard</a>
                <a href="{{ route('renter.marketplace') }}" class="{{ request()->routeIs('home', 'renter.marketplace', 'categories.show') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }} rounded-lg px-4 py-3 text-sm font-bold">Marketplace</a>
                <a href="{{ route('renter.my-rentals') }}" class="{{ request()->routeIs('my-rentals', 'renter.my-rentals') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }} rounded-lg px-4 py-3 text-sm font-bold">My Rentals</a>
            </div>
        </div>
    @endauth
    </nav>

    <section class="relative overflow-hidden bg-gradient-to-br from-blue-700 via-violet-700 to-slate-900 text-white">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -left-16 -top-12 h-72 w-72 rounded-full bg-cyan-300/20 blur-3xl"></div>
            <div class="absolute -bottom-20 right-0 h-80 w-80 rounded-full bg-fuchsia-300/20 blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.16),_transparent_45%)]"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20">
            <div class="mx-auto max-w-4xl text-center">
                <p class="mb-6 inline-flex rounded-full border border-white/25 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-blue-50 backdrop-blur-xl">
                    Campus Marketplace
                </p>
                <h1 class="text-6xl sm:text-7xl lg:text-8xl font-black tracking-tighter leading-tight mb-6">
                    <span class="block">Share Resources,</span>
                    <span class="block bg-gradient-to-r from-white to-blue-100 dark:to-accent-200 bg-clip-text text-transparent">Save Money</span>
                </h1>

                <p class="mx-auto max-w-2xl text-lg sm:text-xl text-blue-100 dark:text-accent-200 mb-8 leading-relaxed">
                    Rent and lend items within the school campus community. Connect with fellow students, save money, and share resources effortlessly.
                </p>
            </div>
        </div>
    </section>

    <section class="border-b border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            @php
                $categoryDescriptions = [
                    'books' => 'Reviewers, textbooks, and reference materials ready for borrowing.',
                    'electronics-gadgets' => 'Laptops, cameras, and useful gadgets for projects and events.',
                    'electronics' => 'Laptops, cameras, and useful gadgets for projects and events.',
                    'gadgets' => 'Laptops, cameras, and useful gadgets for projects and events.',
                    'tools' => 'Toolkits and equipment for practical classes and repairs.',
                    'sports-pe' => 'Sports gear and PE essentials for training and activities.',
                    'school-supplies-accessories' => 'Daily school supplies and must-have accessories for class.',
                    'accessories' => 'Daily school supplies and must-have accessories for class.',
                    'clothing' => 'Uniforms and clothing pieces available for short-term use.',
                ];
            @endphp

            
            <h2 class="mb-6 text-2xl font-bold text-slate-900 dark:text-slate-50">Browse Available Items By Category</h2>

            <div class="mb-4 flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    wire:click="clearCategorySelection"
                    class="{{ is_null($selectedCategory) ? 'border-blue-200 bg-blue-100 text-slate-900 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-100' : 'border-slate-200 bg-white text-slate-700 hover:border-blue-200 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-700 dark:hover:bg-blue-900/20' }} inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold transition"
                >
                    All Categories
                </button>
                @if($selectedCategory)
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        Filtering: {{ $selectedCategory->name }}
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7">
                @foreach ($categories as $category)
                    <button type="button" wire:click="selectCategory({{ $category->id }})" class="{{ optional($selectedCategory)->is($category) ? 'border-blue-200 bg-blue-100 text-slate-900 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-100' : 'border-slate-200 bg-white text-slate-900 hover:bg-slate-100 hover:border-blue-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-blue-900/20 dark:hover:border-blue-700' }} flex h-full min-h-44 w-full flex-col rounded-lg border p-3 text-left shadow-sm transition sm:min-h-48">
                        <div class="mb-2 inline-flex h-8 w-8 items-center justify-center rounded-md bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300">
                            @if ($category->icon === 'book')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 17A2.5 2.5 0 0 0 4 19.5m2.5-2.5V5A2 2 0 0 1 8.5 3H20v14M6.5 17H20" /></svg>
                            @elseif ($category->icon === 'device')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z" /></svg>
                            @elseif ($category->icon === 'pencil')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 5 4 4M4 20l4.5-1 9-9a2.121 2.121 0 0 0-3-3l-9 9L4 20Z" /></svg>
                            @elseif ($category->icon === 'tool')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a4 4 0 0 0-5.4 5.87l-5.01 5.02a1.5 1.5 0 1 0 2.12 2.12l5.02-5.01a4 4 0 0 0 5.87-5.4l-2.1 2.1-2.83-.71-.71-2.83 2.1-2.16Z" /></svg>
                            @elseif ($category->icon === 'fitness')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 10V8a1 1 0 0 1 1-1h1v10H7a1 1 0 0 1-1-1v-2m12-4V8a1 1 0 0 0-1-1h-1v10h1a1 1 0 0 0 1-1v-2M8 12h8" /></svg>
                            @elseif ($category->icon === 'shirt')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4.5 5.5 7 3 5.5l2.5-2.5L8 4.5Zm8 0L18.5 7 21 5.5l-2.5-2.5L16 4.5ZM7 7l1-2.5h8L17 7v12a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V7Z" /></svg>
                            @else
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l.7 2.154a1 1 0 0 0 .95.69h2.264c.969 0 1.371 1.24.588 1.81l-1.832 1.331a1 1 0 0 0-.364 1.118l.7 2.154c.3.921-.755 1.688-1.539 1.118l-1.832-1.331a1 1 0 0 0-1.176 0l-1.832 1.331c-.783.57-1.838-.197-1.539-1.118l.7-2.154a1 1 0 0 0-.364-1.118L5.547 7.58c-.783-.57-.38-1.81.588-1.81H8.4a1 1 0 0 0 .95-.69l.7-2.154Z" /></svg>
                            @endif
                        </div>
                        <h3 class="line-clamp-2 text-sm font-semibold leading-tight">{{ $category->name }}</h3>
                        <p class="{{ optional($selectedCategory)->is($category) ? 'text-slate-600 dark:text-blue-200' : 'text-slate-500 dark:text-slate-400' }} mt-1 line-clamp-3 text-[11px]">
                            {{ $categoryDescriptions[$category->slug] ?? 'Browse listings in this category and quickly find what you need.' }}
                        </p>
                        <div class="{{ optional($selectedCategory)->is($category) ? 'border-blue-200 text-blue-700 dark:border-blue-700 dark:text-blue-300' : 'border-slate-200 text-slate-500 dark:border-slate-700 dark:text-slate-400' }} mt-auto border-t pt-2 text-[11px] font-semibold">
                            {{ $category->available_items_count }} {{ \Illuminate\Support\Str::plural('item', $category->available_items_count) }} available
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    @livewire('item-filter', ['selectedCategoryId' => $selectedCategory?->id], 'item-filter-'.$selectedCategory?->id)
</main>

    <x-site-footer />
</div>
