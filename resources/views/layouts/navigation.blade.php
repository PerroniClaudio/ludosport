<nav x-data="{ open: false }"
    class="bg-white dark:bg-background-800 border-b border-background-100 dark:border-background-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 lg:px-6 xl:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center ">
                    <a href="{{ route('dashboard') }}" class="bg-background-800 p-1 rounded">
                        {{-- <x-application-logo
                            class="block h-9 w-auto fill-current text-background-800 dark:text-background-200" /> --}}
                        <x-application-logo class="block h-9 w-9" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-2 lg:space-x-4 xl:space-x-8 lg:-my-px lg:ms-10 lg:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('dashboard.title') }}
                    </x-nav-link>

                    @if (Auth::user()->getRole() !== 'admin')
                        @foreach (Auth::user()->routes() as $route)
                            <x-nav-link :href="route($route->name)" :active="request()->routeIs($route->active)">
                                {{ __('navigation.' . $route->label) }}
                            </x-nav-link>
                        @endforeach
                    @else
                        @php
                            $navLang = 'users';
                            if (request()->routeIs('users.*')) {
                                $navLang = 'users';
                            }
                            if (request()->routeIs('nations.*')) {
                                $navLang = 'nazioni';
                            }
                            if (request()->routeIs('academies.*')) {
                                $navLang = 'accademie';
                            }
                            if (request()->routeIs('schools.*')) {
                                $navLang = 'scuole';
                            }
                            if (request()->routeIs('clans.*')) {
                                $navLang = 'clan';
                            }
                            if (request()->routeIs('weapon-forms.*')) {
                                $navLang = 'weapon_forms';
                            }
                        @endphp
                        <x-nav-link-parent :href="'#'" :active="request()->routeIs('users.*') ||
                            request()->routeIs('nations.*') ||
                            request()->routeIs('academies.*') ||
                            request()->routeIs('schools.*') ||
                            request()->routeIs('clans.*') ||
                            request()->routeIs('weapon-forms.*')">
                            <x-slot name="name">{{ __('navigation.' . $navLang) }}</x-slot>
                            <x-slot name="children">
                                <a href="{{ route('users.index') }}">{{ __('navigation.users') }}</a>
                                <span class="separator"></span>
                                <a href="{{ route('nations.index') }}">{{ __('navigation.nazioni') }}</a>
                                <span class="separator"></span>
                                <a href="{{ route('academies.index') }}">{{ __('navigation.accademie') }}</a>
                                <span class="separator"></span>
                                <a href="{{ route('schools.index') }}">{{ __('navigation.scuole') }}</a>
                                <span class="separator"></span>
                                <a href="{{ route('clans.index') }}">{{ __('navigation.clan') }}</a>
                                <span class="separator"></span>
                                <a href="{{ route('weapon-forms.index') }}">{{ __('navigation.weapon_forms') }}</a>
                                <span class="separator"></span>
                                <a href="{{ route('rank-requests.index') }}">{{ __('navigation.rank_requests') }}</a>
                            </x-slot>
                        </x-nav-link-parent>
                        <x-nav-link :href="route('announcements.index')" :active="request()->routeIs('announcements.*')">
                            {{ __('navigation.announcements') }}
                        </x-nav-link>
                        <x-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">
                            {{ __('navigation.eventi') }}
                        </x-nav-link>
                        <x-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                            {{ __('navigation.ordini') }}
                        </x-nav-link>
                        <x-nav-link :href="route('rankings.index')" :active="false">
                            {{ __('navigation.classifiche') }}
                        </x-nav-link>
                        <x-nav-link-parent :href="'#'" :active="request()->routeIs('imports.*') ||
                            request()->routeIs('exports.*') ||
                            request()->routeIs('deleted-elements.*')">
                            <x-slot name="name">{{ __('navigation.operations') }}</x-slot>
                            <x-slot name="children">
                                <a href="{{ route('imports.index') }}">{{ __('navigation.imports') }}</a>
                                <span class="separator"></span>
                                <a href="{{ route('exports.index') }}">{{ __('navigation.exports') }}</a>
                                <span class="separator"></span>
                                <a
                                    href="{{ route('deleted-elements.index') }}">{{ __('navigation.deleted_elements') }}</a>
                            </x-slot>
                        </x-nav-link-parent>
                    @endif

                    <x-nav-link :href="route('homepage')">
                        {{ __('navigation.website') }}
                    </x-nav-link>

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden lg:flex lg:items-center lg:ms-6">
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
            <div class="-me-2 flex items-center lg:hidden">
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
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden lg:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('dashboard.title') }}
            </x-responsive-nav-link>
            @if (Auth::user()->getRole() !== 'admin')
                @foreach (Auth::user()->routes() as $route)
                    <x-responsive-nav-link :href="route($route->name)" :active="request()->routeIs($route->active)">
                        {{ __('navigation.' . $route->label) }}
                    </x-responsive-nav-link>
                @endforeach
            @else
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    {{ __('navigation.users') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('nations.index')" :active="request()->routeIs('nations.*')">
                    {{ __('navigation.nazioni') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('academies.index')" :active="request()->routeIs('academies.*')">
                    {{ __('navigation.accademie') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('schools.index')" :active="request()->routeIs('schools.*')">
                    {{ __('navigation.scuole') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('clans.index')" :active="request()->routeIs('clans.*')">
                    {{ __('navigation.clan') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('weapon-forms.index')" :active="request()->routeIs('weapon-forms.*')">
                    {{ __('navigation.weapon_forms') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('announcements.index')" :active="request()->routeIs('announcements.*')">
                    {{ __('navigation.announcements') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">
                    {{ __('navigation.eventi') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                    {{ __('navigation.ordini') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('rankings.index')" :active="request()->routeIs('rankings.*')">
                    {{ __('navigation.classifiche') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('imports.index')" :active="request()->routeIs('imports.*')">
                    {{ __('navigation.imports') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('exports.index')" :active="request()->routeIs('exports.*')">
                    {{ __('navigation.exports') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('deleted-elements.index')" :active="request()->routeIs('deleted-elements.*')">
                    {{ __('navigation.deleted_elements') }}
                </x-responsive-nav-link>
            @endif
                <x-responsive-nav-link :href="route('homepage')" :active="request()->routeIs('homepage')">
                    {{ __('navigation.website') }}
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
    </div>
</nav>
