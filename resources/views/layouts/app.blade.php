<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }" @load="$watch('darkMode', (val) => document.documentElement.classList.toggle('dark', val))">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles

        <script>
            // Prevent flash of unstyled content in dark mode
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        </script>
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
        @php
            $routeRental = request()->route('rental');
            $isRenterRentalThread = request()->routeIs('rental-requests.show')
                && Auth::check()
                && $routeRental instanceof \App\Models\Rental
                && (int) $routeRental->renter_id === (int) Auth::id()
                && request('portal') !== 'lister';
            $usesListerSidebar = request()->routeIs('lister.*', 'my-listings', 'add-item', 'edit-item', 'rent-inventory-management')
                || (request()->routeIs('rental-requests.*') && ! $isRenterRentalThread)
                || (
                    request()->routeIs('profile.show')
                    && request('portal') === 'lister'
                    && Auth::check()
                    && ! Auth::user()?->isAdministrator()
                );
        @endphp

        <x-banner />

        <div
            class="min-h-screen bg-gradient-to-b from-slate-50 via-white to-slate-100 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950"
            x-data="{ sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }"
            x-init="$watch('sidebarCollapsed', (value) => localStorage.setItem('sidebarCollapsed', value ? 'true' : 'false'))"
        >
            @livewire('navigation-menu')

            <div
                class="{{ $usesListerSidebar ? 'lg:pt-20' : '' }}"
                @if ($usesListerSidebar)
                    :class="sidebarCollapsed ? 'lg:pl-24' : 'lg:pl-72'"
                @endif
            >
                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white dark:bg-slate-900 shadow dark:shadow-slate-900/50">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>

                <x-site-footer />
            </div>
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>
