<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('privacy_policy.privacy_policy') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-background-900 antialiased bg-white dark:bg-background-900">
    <div class="min-h-screen flex flex-col">
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
                                        {{ __('auth.logout') }}
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
                <!-- Acceptance Required Alert -->
                @if ($requiresAcceptance)
                    <div class="mb-8 p-4 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-100 rounded-lg border border-yellow-300 dark:border-yellow-700">
                        <p class="font-semibold">{{ __('privacy_policy.please_read_and_accept') }}</p>
                    </div>
                @endif

                <h1 class="text-5xl font-bold mb-8 text-primary-600 dark:text-primary-400">
                    {{ __('privacy_policy.privacy_policy') }}
                </h1>
                @if ($policy && $policy->updated_at)
                    <p class="text-background-500 dark:text-background-400 mb-4">
                        {{ __('Last updated') }}: <strong>{{ $policy->updated_at->format('d/m/Y') }}</strong>
                    </p>
                @endif
                <div class="privacy-policy-content">
                    @if ($policy && $policy->content)
                        {!! $policy->content !!}
                    @else
                        <p class="text-center text-background-600 dark:text-background-400">
                            {{ __('privacy_policy.no_content') }}
                        </p>
                    @endif
                </div>

                <!-- Accept/Decline Buttons -->
                @if ($requiresAcceptance)
                    <div class="mt-12 pt-8 border-t border-background-300 dark:border-background-600">
                        <p class="mb-6 text-sm text-center text-background-700 dark:text-background-300">
                            {{ __('privacy_policy.you_must_accept') }}
                        </p>
                        <div class="flex gap-4 justify-center flex-wrap">
                            <form method="POST" action="{{ route('privacy-policy.decline') }}" class="w-full max-w-sm">
                                @csrf
                                <button type="submit" class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition duration-150">
                                    {{ __('privacy_policy.decline_and_logout') }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('privacy-policy.accept') }}" class="w-full max-w-sm">
                                @csrf
                                <button type="submit" class="w-full px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition duration-150">
                                    {{ __('privacy_policy.accept_and_continue') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </main>

        <!-- Footer -->
        <x-footer />
    </div>
</body>
</html>
