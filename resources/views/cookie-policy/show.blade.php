<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('cookie_policy.cookie_policy') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/cookie-policy-manager.js'])
</head>
<body class="font-sans text-background-900 antialiased bg-white dark:bg-background-900">
    <div class="min-h-screen flex flex-col" @load="initCookiePolicy()">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-background-800 border-b border-background-100 dark:border-background-700 sticky top-0 z-40">
            <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('homepage') }}">
                                <x-application-logo class="block h-9 w-9" />
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            @if (Auth::check())
                                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                    {{ __('dashboard.title') }}
                                </x-nav-link>
                            @endif

                            <x-nav-link :href="route('schools-map')" :active="request()->routeIs('schools-map')">
                                {{ __('website.schools_map') }}
                            </x-nav-link>
                            <x-nav-link :href="route('rankings-website')" :active="request()->routeIs('rankings-website')">
                                {{ __('website.rankings') }}
                            </x-nav-link>
                            <x-nav-link :href="route('shop')" :active="request()->routeIs('shop')">
                                {{ __('website.shop') }}
                            </x-nav-link>
                            <x-nav-link :href="route('user-search')" :active="request()->routeIs('user-search')">
                                {{ __('website.user_search') }}
                            </x-nav-link>

                            <x-nav-link :href="route('events-list')" :active="request()->routeIs('events-list')">
                                {{ __('website.events_list') }}
                            </x-nav-link>
                        </div>
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-background-500 dark:text-background-400 bg-white dark:bg-background-800 hover:text-background-700 dark:hover:text-background-300 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ Auth::user()->name ?? __('navigation.login') }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                @if (Auth::check())
                                    <x-dropdown-link :href="route('dashboard')">
                                        {{ __('dashboard.title') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Profile') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                @else
                                    <x-dropdown-link :href="route('login')">
                                        {{ __('navigation.login') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('register')">
                                        {{ __('auth.register') }}
                                    </x-dropdown-link>
                                @endif
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Content -->
        <main class="flex-grow w-full px-4 py-12 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto">
                <h1 class="text-5xl font-bold mb-8 text-primary-600 dark:text-primary-400">
                    {{ __('cookie_policy.cookie_policy') }}
                </h1>
                @if ($policy?->updated_at)
                    <p class="text-background-500 dark:text-background-400 mb-4">
                        {{ __('Last updated') }}: <strong>{{ $policy->updated_at->format('d/m/Y') }}</strong>
                    </p>
                @endif
                <div class="privacy-policy-content">
                    @if ($policy && $policy->content)
                        {!! $policy->content !!}
                    @else
                        <p class="text-center text-background-600 dark:text-background-400">
                            {{ __('cookie_policy.no_content') }}
                        </p>
                    @endif
                </div>
            </div>
        </main>

        <!-- Footer -->
        <x-footer />
        
        <!-- Cookie Policy Banner -->
        <x-policy-banner />
    </div>
</body>
</html>
