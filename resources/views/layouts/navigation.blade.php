<nav x-data="{ open: false }"
    class="bg-white dark:bg-background-800 border-b border-background-100 dark:border-background-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo
                            class="block h-9 w-auto fill-current text-background-800 dark:text-background-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if (Auth::user()->getRole() !== 'admin')
                        @foreach (Auth::user()->routes() as $route)
                            <x-nav-link :href="route($route->name)" :active="request()->routeIs($route->active)">
                                {{ __('navigation.' . $route->label) }}
                            </x-nav-link>
                        @endforeach
                    @else
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                            {{ __('navigation.users') }}
                        </x-nav-link>

                        <x-nav-link :href="route('nations.index')" :active="request()->routeIs('nations.*')">
                            {{ __('navigation.nazioni') }}
                        </x-nav-link>
                        <x-nav-link :href="route('academies.index')" :active="request()->routeIs('academies.*')">
                            {{ __('navigation.accademie') }}
                        </x-nav-link>
                        <x-nav-link :href="route('schools.index')" :active="request()->routeIs('schools.*')">
                            {{ __('navigation.scuole') }}
                        </x-nav-link>
                        <x-nav-link :href="route('clans.index')" :active="request()->routeIs('clans.*')">
                            {{ __('navigation.clan') }}
                        </x-nav-link>
                        <x-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">
                            {{ __('navigation.eventi') }}
                        </x-nav-link>
                        <x-nav-link :href="route('imports.index')" :active="false">
                            {{ __('navigation.imports') }}
                        </x-nav-link>
                        <x-nav-link :href="route('dashboard')" :active="false">
                            {{ __('navigation.classifiche') }}
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
                            <div>{{ Auth::user()->name }}</div>

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
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

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
    </div>
</nav>
