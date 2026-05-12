<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }" @load="$watch('darkMode', (val) => document.documentElement.classList.toggle('dark', val))">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Campus Rental - Share Resources, Save Money</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="bg-white dark:bg-slate-950 font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors">
    <!-- Navigation -->
    <nav class="border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 sticky top-0 z-50 transition-colors">
        <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-violet-600 text-white shadow-lg shadow-blue-600/25 transition-all duration-300 group-hover:-translate-y-0.5">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 8-9-5-9 5m18 0-9 5m9-5v8l-9 5m0-8L3 8m9 5v8M3 8v8l9 5" />
                    </svg>
                </span>
                <span class="text-xl font-extrabold tracking-normal text-blue-600 sm:text-2xl dark:text-blue-400">Campus<span class="text-violet-600 dark:text-violet-400">Rent</span></span>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('login.options') }}" class="hidden sm:inline-flex text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 transition gap: p-4">Sign In</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-gradient-primary px-4 py-2 text-sm font-semibold text-white hover:shadow-lg hover:shadow-primary-500/50 transition-all duration-300">Sign Up</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-primary dark:bg-gradient-to-br dark:from-primary-900 dark:to-accent-900 text-white">
        <!-- Gradient overlay patterns -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 h-80 w-80 rounded-full bg-accent-500/20 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-80 w-80 rounded-full bg-primary-500/20 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <!-- Badge -->
                <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/10 backdrop-blur-sm px-4 py-2 ring-1 ring-white/20">
                    <span class="inline-flex h-2 w-2 rounded-full bg-green-400"></span>
                    <span class="text-sm font-medium text-white">Welcome to Campus Rental community.</span>
                </div>

                <!-- Main Heading -->
                <h1 class="text-6xl sm:text-7xl lg:text-8xl font-black tracking-tighter leading-tight mb-6">
                    <span class="block">Share Resources,</span>
                    <span class="block bg-gradient-to-r from-white to-blue-100 dark:to-accent-200 bg-clip-text text-transparent">Save Money</span>
                </h1>

                <p class="mx-auto max-w-2xl text-lg sm:text-xl text-blue-100 dark:text-accent-200 mb-8 leading-relaxed">
                    Rent and lend items within the school campus community. Connect with fellow students, save money, and share resources effortlessly.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
                    <a href="{{ route('register') }}" class="px-8 py-4 rounded-xl bg-white text-primary-600 font-bold text-lg hover:bg-blue-50 transition-all duration-300 hover:shadow-2xl hover:shadow-white/50 hover:-translate-y-1 w-full sm:w-auto">
                        Get Started for Free
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="bg-white dark:bg-slate-900 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl sm:text-5xl font-bold text-slate-900 dark:text-slate-50 mb-4">Why Choose Campus Rental?</h2>
                <p class="text-lg text-slate-600 dark:text-slate-400">Everything you need to rent and share within your campus community</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-8 hover:shadow-lg dark:hover:shadow-slate-900/70 transition-all duration-300 hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-lg bg-gradient-primary mb-4 flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-slate-50 mb-2">Save Money</h3>
                    <p class="text-slate-600 dark:text-slate-400">Rent items at 50-80% less than retail prices. Why buy when you can rent?</p>
                </div>

                <!-- Feature 2 -->
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-8 hover:shadow-lg dark:hover:shadow-slate-900/70 transition-all duration-300 hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-lg bg-gradient-primary mb-4 flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10h.01M11 10h.01M7 10h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-slate-50 mb-2">Community Driven</h3>
                    <p class="text-slate-600 dark:text-slate-400">Connect with trusted fellow students from your campus. Build relationships while sharing.</p>
                </div>

                <!-- Feature 3 -->
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-8 hover:shadow-lg dark:hover:shadow-slate-900/70 transition-all duration-300 hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-lg bg-gradient-primary mb-4 flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7.4-6.4a2 2 0 00-2.83 0l-8.82 8.82a2 2 0 000 2.83l8.82 8.82a2 2 0 002.83 0l8.82-8.82a2 2 0 000-2.83L19.4 3.6z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-slate-50 mb-2">Safe & Secure</h3>
                    <p class="text-slate-600 dark:text-slate-400">Verified student accounts and secure transaction system for peace of mind.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Preview -->
    <section class="bg-slate-50 dark:bg-slate-800 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl sm:text-5xl font-bold text-slate-900 dark:text-slate-50 mb-4">Browse Categories</h2>
                <p class="text-lg text-slate-600 dark:text-slate-400">From electronics to sports equipment, find everything you need</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 p-6 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 cursor-pointer group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-primary flex items-center justify-center group-hover:shadow-lg group-hover:shadow-primary-500/50 transition-all">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50">Electronics &amp; Gadgets</h3>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Laptops, cameras, headphones & more</p>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 p-6 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 cursor-pointer group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-primary flex items-center justify-center group-hover:shadow-lg group-hover:shadow-primary-500/50 transition-all">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 5.5 2 7.818 2 11.5S6.5 17.5 12 17.5s10-1.818 10-5.5-4.5-6-10-6z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50">Sports &amp; PE Essentials</h3>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Sports equipment & fitness gear</p>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 p-6 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 cursor-pointer group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-primary flex items-center justify-center group-hover:shadow-lg group-hover:shadow-primary-500/50 transition-all">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 5.5 2 7.818 2 11.5S6.5 17.5 12 17.5s10-1.818 10-5.5-4.5-6-10-6z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50">Books</h3>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Textbooks and study materials</p>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 p-6 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 cursor-pointer group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-primary flex items-center justify-center group-hover:shadow-lg group-hover:shadow-primary-500/50 transition-all">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 5.5 2 7.818 2 11.5S6.5 17.5 12 17.5s10-1.818 10-5.5-4.5-6-10-6z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50">Clothing &amp; Accessories</h3>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Fashion and formal wear</p>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 p-6 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 cursor-pointer group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-primary flex items-center justify-center group-hover:shadow-lg group-hover:shadow-primary-500/50 transition-all">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 5.5 2 7.818 2 11.5S6.5 17.5 12 17.5s10-1.818 10-5.5-4.5-6-10-6z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50">Tools</h3>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Tools and equipment</p>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 p-6 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 cursor-pointer group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg bg-gradient-primary flex items-center justify-center group-hover:shadow-lg group-hover:shadow-primary-500/50 transition-all">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 5.5 2 7.818 2 11.5S6.5 17.5 12 17.5s10-1.818 10-5.5-4.5-6-10-6z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50">School Supplies</h3>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400">Pens, organizers, bags, and classroom essentials</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-gradient-primary dark:bg-gradient-to-br dark:from-primary-900 dark:to-accent-900 text-white py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl sm:text-5xl font-bold mb-6">Ready to Get Started?</h2>
            <p class="text-lg text-blue-100 dark:text-accent-200 mb-8 max-w-2xl mx-auto">Join and connect with the students within our Campus Rental community.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="{{ route('register') }}" class="px-8 py-4 rounded-xl bg-white text-primary-600 font-bold text-lg hover:bg-blue-50 transition-all duration-300 hover:shadow-2xl hover:shadow-white/50 hover:-translate-y-1">
                    Sign Up Now
                </a>
                <a href="{{ route('login.options') }}" class="px-8 py-4 rounded-xl bg-white/10 backdrop-blur-sm text-white font-bold text-lg border-2 border-white/30 hover:bg-white/20 transition-all duration-300 hover:border-white/50">
                    Already a Member?
                </a>
            </div>
        </div>
    </section>

    <x-site-footer />
</body>
</html>
