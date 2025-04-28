<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="{{ route('favicon') }}" type="image/x-icon">


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-background-900 antialiased bg-background-100 dark:bg-background-800"
    x-data="{ open: false }">
    <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('homepage') }}">
                        {{-- <x-application-logo
                            class="block h-9 w-auto fill-current text-background-800 dark:text-background-200" /> --}}
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

                    @if (Auth::check())
                        <x-nav-link :href="route('events-list')" :active="request()->routeIs('events-list')">
                            {{ __('website.events_list') }}
                        </x-nav-link>
                    @endif

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-background-500 dark:text-background-400 bg-white dark:bg-background-800 hover:text-background-700 dark:hover:text-background-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name ?? __('Log in') }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">

                        <!-- Only show if user is authenticated -->
                        @if (Auth::check())
                            <x-dropdown-link :href="route('dashboard')">
                                {{ __('dashboard.title') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            
                            @if (count(Auth::user()->allowedRoles()) > 1)
                                <x-dropdown-link :href="route('role-selector')">
                                    {{ __('users.select_role') }}
                                </x-dropdown-link>
                            @endif

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        @else
                            <x-dropdown-link :href="route('login')">
                                {{ __('Log in') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('register')">
                                {{ __('Register') }}
                            </x-dropdown-link>
                        @endif




                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-background-400 dark:text-background-500 hover:text-background-500 dark:hover:text-background-400 hover:bg-background-100 dark:hover:bg-background-900 focus:outline-none focus:bg-background-100 dark:focus:bg-background-900 focus:text-background-500 dark:focus:text-background-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="relative z-20 hidden sm:hidden bg-center bg-contain bg-no-repeat"
        style="background-image: url('{{ env('APP_URL') }}/logo-saber-k');">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('dashboard.title') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('schools-map')" :active="request()->routeIs('schools-map')">
                {{ __('website.schools_map') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('rankings-website')" :active="request()->routeIs('rankings-website')">
                {{ __('website.rankings') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('shop')" :active="request()->routeIs('shop')">
                {{ __('website.shop') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('user-search')" :active="request()->routeIs('user-search')">
                {{ __('website.user_search') }}
            </x-responsive-nav-link>
            @if (Auth::check())
                <x-responsive-nav-link  :href="route('events-list')" :active="request()->routeIs('events-list')">
                    {{ __('website.events_list') }}
                </x-responsive-nav-link >
            @endif

        </div>

        @if (Auth::check())
            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-background-200 dark:border-background-600">
                <div class="px-4">
                    <div class="font-medium text-base text-background-800 dark:text-background-200">
                        {{ Auth::user()->name }}
                    </div>
                    <div class="font-medium text-sm text-background-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    @if (count(Auth::user()->allowedRoles()) > 1)
                        <x-responsive-nav-link :href="route('role-selector')">
                            {{ __('users.select_role') }}
                        </x-responsive-nav-link>
                    @endif

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-1 border-t border-background-200 dark:border-background-600">
                <div class="px-4">
                    <div class="font-medium text-base text-background-800 dark:text-background-200">
                        {{ __('Log in') }}
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')">
                        {{ __('Log in') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')">
                        {{ __('Register') }}
                    </x-responsive-nav-link>
                </div>
            </div>
        @endif
    </div>
    <main class="relative z-10 min-h-screen bg-background-200 dark:bg-background-900">
        {{ $slot }}
    </main>

    <x-flash />
    <x-footer />

    {{-- Cookie Policy e Privacy Policy banner --}}
    <x-policy-banner />

</body>


</html>
