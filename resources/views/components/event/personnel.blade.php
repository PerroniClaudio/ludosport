@props(['event' => ''])
@php
    $authRole = auth()->user()->getRole();
    // $exportRoute = $authRole === 'admin' ? 'events.participants.export' : $authRole . '.events.participants.export';
@endphp
<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8" x-data="eventpersonnel({{ $event->id }}, '{{ $authRole }}')">
    <div class="flex justify-between">
        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.personnel') }}</h3>
        {{-- <div>
            <a href="{{ route($exportRoute, $event->id) }}">
                <x-primary-button type="button">
                    {{ __('events.export_participants') }}
                </x-primary-button>
            </a>
        </div> --}}
    </div>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @if ((!$event->is_approved && in_array($authRole, ['admin', 'rector'])) || $authRole === 'admin')
            <div class="bg-background-100 p-4 rounded ">
                <div class="flex justify-between gap-2 items-center">
                    <div class="flex-1">
                        <h4 class="text-background-800 dark:text-background-200 text-lg">{{ __('events.available_personnel') }}
                        </h4>
                    </div>
                    <div>
                        <x-text-input type="text" x-on:input="searchAvailableUsers(event);" placeholder="Search..."
                            class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                    </div>
                </div>

                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                <table
                    class="rounded-md overflow-hidden border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">
                    <thead>
                        <tr class="">
                            <th
                                class="px-1 text-left bg-background-50 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                {{ __('users.name') }}</th>
                            <th
                                class="px-1 text-left bg-background-50 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                {{ __('users.surname') }}</th>
                            <th
                                class="px-1 text-right bg-background-50 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                {{ __('users.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in paginatedUsers">
                            <tr>
                                <td class="px-1 text-background-500 dark:text-background-300 text-sm" x-text="row.name"></td>
                                <td class="px-1 text-background-500 dark:text-background-300 text-sm" x-text="row.surname"></td>
                                <td class="px-1 text-background-500 dark:text-background-300 text-sm text-right p-1">
                                    <button @click="addPersonnel(row.id)">
                                        <x-lucide-plus
                                            class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:text-primary-700" />
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>

                </table>

                <div class="flex items-center">
                    <div class="flex-1">

                    </div>
                    <div class="flex justify-between items-center">
                        <button x-on:click="goToPage(1)" class="mr-2" x-bind:disabled="currentPage === 1">
                            <x-lucide-chevron-first class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                        </button>
                        <button x-on:click="goToPage(currentPage - 1)" class="mr-2" x-bind:disabled="currentPage === 1"
                            :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }">
                            <x-lucide-chevron-left class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                        </button>
                        <p class="text-sm text-background-500 dark:text-background-300">Page <span
                                x-text="currentPage"></span> of
                            <span x-text="totalPages"></span>
                        </p>
                        <button x-on:click="goToPage(currentPage + 1)" class="ml-2"
                            x-bind:disabled="currentPage === totalPages"
                            :class="{ 'opacity-50 cursor-not-allowed': currentPage === totalPages }">
                            <x-lucide-chevron-right class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                        </button>
                        <button x-on:click="goToPage(totalPages)" class="ml-2"
                            x-bind:disabled="currentPage === totalPages">
                            <x-lucide-chevron-last class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                        </button>
                    </div>
                </div>



            </div>
        @endif
        <div class="bg-background-100 p-4 rounded ">

            <div class="flex justify-between gap-2 items-center">
                <div class="flex-1">
                    <h4 class="text-background-800 dark:text-background-200 text-lg">{{ __('events.selected_personnel') }}
                    </h4>
                </div>
                <div>
                    <x-text-input type="text" x-on:input="searchPersonnel(event);" placeholder="Search..."
                        class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                </div>
            </div>
            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
            <table
                class="rounded-md overflow-hidden border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">
                <thead>
                    <tr class="">
                        <th
                            class="px-1 text-left bg-background-50 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                            {{ __('users.name') }}</th>
                        <th
                            class="px-1 text-left bg-background-50 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                            {{ __('users.surname') }}</th>
                        @if ((!$event->is_approved && in_array($authRole, ['admin', 'rector'])) || $authRole === 'admin')
                            <th
                                class="px-1 text-right bg-background-50 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                {{ __('users.actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(personnel, index) in eventPersonnel">
                        <tr>
                            <td class="px-1 text-background-500 dark:text-background-300 text-sm" x-text="personnel.name">
                            </td>
                            <td class="px-1 text-background-500 dark:text-background-300 text-sm"
                                x-text="personnel.surname"></td>
                            @if ((!$event->is_approved && in_array($authRole, ['admin', 'rector'])) || $authRole === 'admin')
                                <td class="px-1 text-background-500 dark:text-background-300 text-sm text-right p-1">
                                    <button @click="removePersonnel(personnel.id)">
                                        <x-lucide-minus
                                            class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:text-primary-700" />
                                    </button>
                                </td>
                            @endif
                        </tr>
                    </template>
                </tbody>
            </table>

        </div>
    </div>
</div>
