@php
    $routeRental = request()->route('rental');
    $isRenterRentalThread = request()->routeIs('rental-requests.show')
        && Auth::check()
        && $routeRental instanceof \App\Models\Rental
        && (int) $routeRental->renter_id === (int) Auth::id()
        && request('portal') !== 'lister';
    $isListerPortal = request()->routeIs('lister.*', 'my-listings', 'add-item', 'edit-item', 'rent-inventory-management')
        || (request()->routeIs('rental-requests.*') && ! $isRenterRentalThread)
        || (
            request()->routeIs('profile.show')
            && request('portal') === 'lister'
            && Auth::check()
            && ! Auth::user()?->isAdministrator()
        );
    $portalHomeRoute = Auth::user()?->isAdministrator()
        ? 'admin.dashboard'
        : ($isListerPortal ? 'lister.dashboard' : 'renter.dashboard');
    $listerLinks = [
        ['route' => 'lister.dashboard', 'label' => 'Lister Dashboard', 'active' => ['lister.dashboard'], 'icon' => 'dashboard'],
        ['route' => 'lister.my-listings', 'label' => 'My Listings', 'active' => ['lister.my-listings', 'my-listings', 'add-item', 'edit-item'], 'icon' => 'listings'],
        ['route' => 'lister.inventory', 'label' => 'Inventory', 'active' => ['lister.inventory', 'rent-inventory-management'], 'icon' => 'inventory'],
        ['route' => 'lister.rental-requests', 'label' => 'Rental Requests', 'active' => ['lister.rental-requests', 'rental-requests.*'], 'icon' => 'requests'],
        ['route' => 'lister.payments', 'label' => 'Payments', 'active' => ['lister.payments'], 'icon' => 'payments'],
        ['route' => 'lister.messages', 'label' => 'Messages', 'active' => ['lister.messages'], 'icon' => 'messages'],
    ];
@endphp

@if ($isListerPortal && Auth::check() && ! Auth::user()?->isAdministrator())
<div>
    <aside
        class="fixed inset-y-0 left-0 z-50 hidden border-r border-slate-200 bg-white/95 px-5 py-6 shadow-xl shadow-slate-900/5 backdrop-blur-xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-950/95 xl:flex xl:flex-col"
        :class="sidebarCollapsed ? 'w-24' : 'w-72'"
    >
        <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'justify-between gap-3'">
            <a href="{{ route('lister.dashboard') }}" class="flex min-w-0 items-center gap-3" :class="sidebarCollapsed ? 'justify-center' : ''">
                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-violet-600 text-white shadow-lg shadow-blue-600/25">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 8-9-5-9 5m18 0-9 5m9-5v8l-9 5m0-8L3 8m9 5v8M3 8v8l9 5" />
                    </svg>
                </span>
                <span x-show="!sidebarCollapsed" x-transition class="truncate text-2xl font-extrabold text-blue-600 dark:text-blue-400">Campus<span class="text-violet-600 dark:text-violet-400">Rent</span></span>
            </a>

            <button
                x-show="!sidebarCollapsed"
                @click="sidebarCollapsed = true"
                type="button"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-900 dark:hover:text-white"
                aria-label="Collapse sidebar"
                title="Collapse sidebar"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        </div>

        <button
            x-show="sidebarCollapsed"
            @click="sidebarCollapsed = false"
            type="button"
            class="mt-5 inline-flex h-10 w-10 items-center justify-center self-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-900 dark:hover:text-white"
            aria-label="Expand sidebar"
            title="Expand sidebar"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <div
            class="mt-8 rounded-lg border border-violet-200 bg-violet-50 p-4 transition dark:border-violet-800/60 dark:bg-violet-950/40"
            :class="sidebarCollapsed ? 'px-2 text-center' : ''"
        >
            <p class="text-xs font-bold uppercase tracking-wider text-violet-700 dark:text-violet-300" :class="sidebarCollapsed ? 'sr-only' : ''">Lister Portal</p>
            <p class="mt-1 truncate text-sm text-violet-900 dark:text-violet-100" :class="sidebarCollapsed ? 'mt-0 font-bold' : ''">
                <span x-show="!sidebarCollapsed">{{ Auth::user()->name }}</span>
                <span x-show="sidebarCollapsed">{{ collect(explode(' ', Auth::user()->name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') }}</span>
            </p>
        </div>

        <nav class="mt-6 flex flex-1 flex-col gap-2">
            @foreach ($listerLinks as $link)
                <a
                    href="{{ route($link['route']) }}"
                    class="{{ request()->routeIs(...$link['active']) ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 dark:hover:text-white' }} flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-bold transition"
                    :class="sidebarCollapsed ? 'justify-center px-3' : ''"
                    title="{{ $link['label'] }}"
                >
                    @if ($link['icon'] === 'dashboard')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l8-8 8 8M5 10v10h14V10" /></svg>
                    @elseif ($link['icon'] === 'listings')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                    @elseif ($link['icon'] === 'inventory')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" /></svg>
                    @elseif ($link['icon'] === 'requests')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.5L19 9.5V19a2 2 0 0 1-2 2z" /></svg>
                    @elseif ($link['icon'] === 'payments')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m4.5-14.5H10a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6H6.5" /></svg>
                    @else
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10.5h8M8 14h5m-8 5 3.5-3.5H18a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v5.5a3 3 0 0 0 2 2.83V19Z" /></svg>
                    @endif
                    <span x-show="!sidebarCollapsed" x-transition class="truncate">{{ $link['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </aside>

    <header
        class="fixed right-0 top-0 z-40 hidden h-16 items-center justify-between border-b border-slate-200/80 bg-white/90 px-8 shadow-sm shadow-slate-900/5 backdrop-blur-xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-950/90 xl:flex"
        :class="sidebarCollapsed ? 'left-24' : 'left-72'"
    >
        <div class="min-w-0">
            <p class="text-xs font-bold uppercase tracking-wider text-violet-700 dark:text-violet-300">Lister Portal</p>
            <p class="truncate text-sm font-semibold text-slate-600 dark:text-slate-300">{{ Auth::user()->name }}</p>
        </div>

        <div class="flex items-center gap-3">
            @livewire('notifications-dropdown')
            <x-dark-mode-toggle />
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button type="button" class="group inline-flex items-center rounded-full px-1 py-1 ring-1 ring-transparent transition hover:ring-slate-200 dark:hover:ring-slate-700">
                        <x-profile-avatar :user="Auth::user()" size="nav" showChevron />
                    </button>
                </x-slot>
                <x-slot name="content">
                    <x-dropdown-link href="{{ route('profile.show', ['portal' => 'lister']) }}">{{ __('Profile Settings') }}</x-dropdown-link>
                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf
                        <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">{{ __('Log Out') }}</x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </header>

    <nav class="sticky top-0 z-40 hidden border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/90 sm:block xl:hidden">
        <div class="flex h-16 items-center justify-between gap-4 px-4 lg:px-6">
            <a href="{{ route('lister.dashboard') }}" class="flex min-w-0 items-center gap-3" title="Lister Dashboard">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-violet-600 text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 8-9-5-9 5m18 0-9 5m9-5v8l-9 5m0-8L3 8m9 5v8M3 8v8l9 5" /></svg>
                </span>
                <span class="hidden truncate text-xl font-extrabold text-blue-600 md:inline dark:text-blue-400">Lister</span>
            </a>

            <div class="flex min-w-0 flex-1 justify-center">
                <div class="flex min-w-0 items-center gap-1 rounded-full bg-slate-50 p-1 ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                    @foreach ($listerLinks as $link)
                        <a
                            href="{{ route($link['route']) }}"
                            class="{{ request()->routeIs(...$link['active']) ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }} inline-flex h-10 min-w-10 items-center justify-center rounded-full px-3 text-sm font-semibold transition"
                            aria-label="{{ $link['label'] }}"
                            title="{{ $link['label'] }}"
                        >
                            @if ($link['icon'] === 'dashboard')
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l8-8 8 8M5 10v10h14V10" /></svg>
                            @elseif ($link['icon'] === 'listings')
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                            @elseif ($link['icon'] === 'inventory')
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" /></svg>
                            @elseif ($link['icon'] === 'requests')
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.5L19 9.5V19a2 2 0 0 1-2 2z" /></svg>
                            @elseif ($link['icon'] === 'payments')
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m4.5-14.5H10a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6H6.5" /></svg>
                            @else
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10.5h8M8 14h5m-8 5 3.5-3.5H18a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v5.5a3 3 0 0 0 2 2.83V19Z" /></svg>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                @livewire('notifications-dropdown')
                <x-dark-mode-toggle />
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" class="group inline-flex items-center rounded-full px-1 py-1 ring-1 ring-transparent transition hover:ring-slate-200 dark:hover:ring-slate-700">
                            <x-profile-avatar :user="Auth::user()" size="nav" showChevron />
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link href="{{ route('profile.show', ['portal' => 'lister']) }}">{{ __('Profile Settings') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">{{ __('Log Out') }}</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </nav>

    <nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/90 sm:hidden">
        <div class="flex h-16 items-center justify-between px-4">
            <a href="{{ route('lister.dashboard') }}" class="flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-violet-600 text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 8-9-5-9 5m18 0-9 5m9-5v8l-9 5m0-8L3 8m9 5v8M3 8v8l9 5" /></svg>
                </span>
                <span class="text-xl font-extrabold text-blue-600 dark:text-blue-400">Lister</span>
            </a>
            <div class="flex items-center gap-1.5">
                @livewire('notifications-dropdown')
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" class="group inline-flex items-center rounded-full px-1 py-1 ring-1 ring-transparent transition hover:ring-slate-200 dark:hover:ring-slate-700">
                            <x-profile-avatar :user="Auth::user()" size="nav" />
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link href="{{ route('profile.show', ['portal' => 'lister']) }}">{{ __('Profile Settings') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">{{ __('Log Out') }}</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
                <button @click="open = !open" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-900" aria-label="Toggle navigation menu">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /><path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>
        <div x-show="open" x-cloak class="space-y-2 border-t border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-950">
            @foreach ($listerLinks as $link)
                <a href="{{ route($link['route']) }}" class="{{ request()->routeIs(...$link['active']) ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900' }} block rounded-lg px-4 py-3 text-sm font-bold">{{ $link['label'] }}</a>
            @endforeach
        </div>
    </nav>
</div>
@else
<nav x-data="{ open: false }" class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-slate-700/80 dark:bg-slate-900/90">
    <div class="mx-auto flex min-h-16 w-full max-w-none flex-wrap items-center gap-2 px-3 py-2 sm:h-16 sm:flex-nowrap sm:px-4 sm:py-0 lg:px-6">
        
        <div class="flex min-w-0 items-center sm:flex-1">
            <a href="{{ route($portalHomeRoute) }}" class="group flex items-center gap-3">
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
                    @if (Auth::user()?->isAdministrator())
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }} inline-flex h-10 min-w-10 items-center justify-center gap-2 rounded-full px-3 text-sm font-semibold transition xl:px-4" title="Admin Dashboard">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l8-8 8 8M5 10v10h14V10" /></svg>
                            <span class="hidden whitespace-nowrap xl:inline">Admin Dashboard</span>
                        </a>
                        <a href="{{ route('admin.marketplace') }}" class="{{ request()->routeIs('admin.marketplace', 'item.view') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }} inline-flex h-10 min-w-10 items-center justify-center gap-2 rounded-full px-3 text-sm font-semibold transition xl:px-4" title="Marketplace">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                            <span class="hidden whitespace-nowrap xl:inline">Marketplace</span>
                        </a>
                        <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }} inline-flex h-10 min-w-10 items-center justify-center gap-2 rounded-full px-3 text-sm font-semibold transition xl:px-4" title="User Management">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 0 0-4-4h-1M9 20H4v-2a4 4 0 0 1 4-4h1m6-4a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-8 0a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg>
                            <span class="hidden whitespace-nowrap xl:inline">User Management</span>
                        </a>
                        <a href="{{ route('admin.reports') }}" class="{{ request()->routeIs('admin.reports') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }} inline-flex h-10 min-w-10 items-center justify-center gap-2 rounded-full px-3 text-sm font-semibold transition xl:px-4" title="Reports & Complaints">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.3 4.3 2.8 17.3A2 2 0 0 0 4.5 20h15a2 2 0 0 0 1.7-2.7L13.7 4.3a2 2 0 0 0-3.4 0Z" /></svg>
                            <span class="hidden whitespace-nowrap xl:inline">Reports & Complaints</span>
                        </a>
                    @else
                        @if ($isListerPortal)
                            <a href="{{ route('lister.dashboard') }}" class="{{ request()->routeIs('lister.dashboard') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }} inline-flex h-10 min-w-10 items-center justify-center gap-2 rounded-full px-3 text-sm font-semibold transition xl:px-4" title="Lister Dashboard">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l8-8 8 8M5 10v10h14V10" /></svg>
                                <span class="hidden whitespace-nowrap xl:inline">Lister Dashboard</span>
                            </a>
                            <a href="{{ route('lister.my-listings') }}" class="{{ request()->routeIs('lister.my-listings', 'my-listings', 'add-item', 'edit-item', 'rental-requests.*') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }} inline-flex h-10 min-w-10 items-center justify-center gap-2 rounded-full px-3 text-sm font-semibold transition xl:px-4" title="My Listings">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                <span class="hidden whitespace-nowrap xl:inline">My Listings</span>
                            </a>
                            <a href="{{ route('lister.inventory') }}" class="{{ request()->routeIs('lister.inventory', 'rent-inventory-management') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }} inline-flex h-10 min-w-10 items-center justify-center gap-2 rounded-full px-3 text-sm font-semibold transition xl:px-4" title="Inventory">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" /></svg>
                                <span class="hidden whitespace-nowrap xl:inline">Inventory</span>
                            </a>
                        @else
                            <a href="{{ route('renter.dashboard') }}" class="{{ request()->routeIs('dashboard', 'renter.dashboard') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }} inline-flex h-10 min-w-10 items-center justify-center gap-2 rounded-full px-3 text-sm font-semibold transition xl:px-4" title="Renter Dashboard">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l8-8 8 8M5 10v10h14V10" /></svg>
                                <span class="hidden whitespace-nowrap xl:inline">Renter Dashboard</span>
                            </a>
                            <a href="{{ route('renter.marketplace') }}" class="{{ request()->routeIs('home', 'renter.marketplace', 'categories.show', 'item.view') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }} inline-flex h-10 min-w-10 items-center justify-center gap-2 rounded-full px-3 text-sm font-semibold transition xl:px-4" title="Marketplace">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                <span class="hidden whitespace-nowrap xl:inline">Marketplace</span>
                            </a>
                            <a href="{{ route('renter.my-rentals') }}" class="{{ request()->routeIs('my-rentals', 'renter.my-rentals') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-100' }} inline-flex h-10 min-w-10 items-center justify-center gap-2 rounded-full px-3 text-sm font-semibold transition xl:px-4" title="My Rentals">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" /></svg>
                                <span class="hidden whitespace-nowrap xl:inline">My Rentals</span>
                            </a>
                        @endif
                    @endif
                </div>
            @endauth
        </div>

        <div class="order-2 ml-auto flex items-center justify-end gap-2 sm:order-none sm:flex-1 sm:gap-3">
            @auth
                @livewire('notifications-dropdown')
                <a href="{{ route('renter.messages') }}" class="{{ request()->routeIs('renter.messages') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white shadow-sm shadow-blue-600/20 ring-transparent' : 'bg-slate-50 text-slate-600 ring-slate-200 hover:bg-white hover:text-slate-900 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700 dark:hover:bg-slate-700 dark:hover:text-white' }} relative inline-flex h-10 w-10 items-center justify-center rounded-full ring-1 transition" aria-label="Messages">
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
                @if (Auth::user()?->isAdministrator())
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }} rounded-lg px-4 py-3 text-sm font-bold">Admin Dashboard</a>
                    <a href="{{ route('admin.marketplace') }}" class="{{ request()->routeIs('admin.marketplace', 'item.view') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }} rounded-lg px-4 py-3 text-sm font-bold">Marketplace</a>
                    <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }} rounded-lg px-4 py-3 text-sm font-bold">User Management</a>
                    <a href="{{ route('admin.reports') }}" class="{{ request()->routeIs('admin.reports') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }} rounded-lg px-4 py-3 text-sm font-bold">Reports & Complaints</a>
                @elseif ($isListerPortal)
                    <a href="{{ route('lister.dashboard') }}" class="{{ request()->routeIs('lister.dashboard') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }} rounded-lg px-4 py-3 text-sm font-bold">Lister Dashboard</a>
                    <a href="{{ route('lister.my-listings') }}" class="{{ request()->routeIs('lister.my-listings', 'my-listings', 'add-item', 'edit-item', 'rental-requests.*') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }} rounded-lg px-4 py-3 text-sm font-bold">My Listings</a>
                    <a href="{{ route('lister.inventory') }}" class="{{ request()->routeIs('lister.inventory', 'rent-inventory-management') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }} rounded-lg px-4 py-3 text-sm font-bold">Inventory</a>
                @else
                    <a href="{{ route('renter.dashboard') }}" class="{{ request()->routeIs('dashboard', 'renter.dashboard') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }} rounded-lg px-4 py-3 text-sm font-bold">Renter Dashboard</a>
                    <a href="{{ route('renter.marketplace') }}" class="{{ request()->routeIs('home', 'renter.marketplace', 'categories.show', 'item.view') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }} rounded-lg px-4 py-3 text-sm font-bold">Marketplace</a>
                    <a href="{{ route('renter.my-rentals') }}" class="{{ request()->routeIs('my-rentals', 'renter.my-rentals') ? 'bg-gradient-to-r from-blue-600 to-violet-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }} rounded-lg px-4 py-3 text-sm font-bold">My Rentals</a>
                @endif
            </div>
        </div>
    @endauth
</nav>
@endif
