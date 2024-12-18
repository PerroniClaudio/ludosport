@props(['event' => '', 'results' => []])
@php
    $authRole = auth()->user()->getRole();
    $exportRoute = $authRole === 'admin' ? 'events.participants.export' : $authRole . '.events.participants.export';
@endphp
<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8" x-load x-data="participants({{ $event->id }}, '{{ $authRole }}', {{ collect($results) }})">
    <div class="flex justify-between">
        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.participants') }}</h3>
        <div>
            <a href="{{ route($exportRoute, $event->id) }}">
                <x-primary-button type="button">
                    {{ __('events.export_participants') }}
                </x-primary-button>
            </a>
        </div>
    </div>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-background-100 dark:bg-background-900 p-4 rounded ">
            <div class="flex justify-between gap-2 items-center">
                <div class="flex-1 flex gap-2 items-center">
                    <h4 class="text-background-800 dark:text-background-200 text-lg">{{ __('events.available_users') }}
                    </h4>
                    <div class='has-tooltip'>
                        <span class='tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 -mt-8'>
                            @if($authRole == 'admin')
                                {{ __('events.event_admin_participants_tooltip') }}
                            @elseif(in_array(strtolower($event->type->name), ['school tournament', 'academy tournament']))
                                {{ __('events.event_academy_participants_tooltip') }}
                            @elseif(strtolower($event->type->name) == 'national tournament')
                                {{ __('events.event_nation_participants_tooltip') }}
                            @else
                                {{ __('events.event_others_participants_tooltip') }}
                            @endif
                        </span>
                        <x-lucide-info class="h-4 text-background-400" />
                    </div>
                </div>
                <div>
                    <input x-model="searchAvailablesValue" x-on:input="searchAvailableUsers(event);" type="text"
                        placeholder="Search..."
                        class='border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm'>
                    {{-- <x-text-input type="text" x-on:input="searchAvailableUsers(event);" placeholder="Search..."
                        class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" /> --}}
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
                            <td class="px-1 text-background-500 dark:text-background-300 text-sm" x-text="row.name">
                            </td>
                            <td class="px-1 text-background-500 dark:text-background-300 text-sm" x-text="row.surname">
                            </td>
                            <td class="px-1 text-background-500 dark:text-background-300 text-sm text-right p-1">
                                {{-- Il tecnico non può modificare i partecipanti in nessun caso. Solo gli admin possono modificare i partecipanti di eventi a pagamento, e solo entro il mimite massimo (0 è illimitato). --}}
                                @if ($authRole != 'technician' && ($event->isFree() || $authRole == 'admin'))
                                    <template
                                        x-if="({{$event->max_participants}} == 0 || ({{ $event->max_participants > 0 ? $event->max_participants : 0 }} > (participants.length + {{ $event->waitingList->count() }})))">
                                        <button @click="addParticipant(row.id)">
                                            <x-lucide-plus
                                                class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:text-primary-700" />
                                        </button>
                                    </template>
                                    <template
                                        x-if="!({{$event->max_participants}} == 0 || ({{ $event->max_participants > 0 ? $event->max_participants : 0 }} > (participants.length + {{ $event->waitingList->count() }})))">
                                        <button disabled>
                                            <x-lucide-ban class="w-4 h-4 text-secondary-500 dark:text-secondary-400" />
                                        </button>
                                    </template>
                                @else
                                    <button disabled>
                                        <x-lucide-ban class="w-4 h-4 text-secondary-500 dark:text-secondary-400" />
                                    </button>
                                @endif
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
        <div class="bg-background-100 dark:bg-background-900 p-4 rounded ">

            <div class="flex justify-between gap-2 items-center">
                <div class="flex-1">
                    <h4 class="text-background-800 dark:text-background-200 text-lg">{{ __('events.selected_users') }}
                    </h4>
                </div>
                <div>
                    <input x-model="searchParticipantsValue" x-on:input="searchParticipants($event);" type="text"
                        placeholder="Search..."
                        class='border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm'>
                    {{-- <x-text-input type="text" x-on:input="searchParticipants(event);" placeholder="Search..."
                        class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" /> --}}
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
                    <template x-for="(participant, index) in participants">
                        <tr>
                            <td class="px-1 text-background-500 dark:text-background-300 text-sm"
                                x-text="participant.name">
                            </td>
                            <td class="px-1 text-background-500 dark:text-background-300 text-sm"
                                x-text="participant.surname"></td>
                            <td class="px-1 text-background-500 dark:text-background-300 text-sm text-right p-1">
                                {{-- Il tecnico non può modificare i partecipanti in nessun caso. Solo gli admin possono modificare i partecipanti di eventi a pagamento. --}}
                                @if ($authRole != 'technician' && ($event->isFree() || $authRole == 'admin'))
                                    <button x-show="!hasRankingResult(participant.id)"
                                        @click="removeParticipant(participant.id)">
                                        <x-lucide-minus
                                            class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:text-primary-700" />
                                    </button>
                                    <button x-show="hasRankingResult(participant.id)" disabled>
                                        <x-lucide-ban class="w-4 h-4 text-secondary-500 dark:text-secondary-400" />
                                    </button>
                                @else
                                    <button disabled>
                                        <x-lucide-ban class="w-4 h-4 text-secondary-500 dark:text-secondary-400" />
                                    </button>
                                @endif
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

        </div>
    </div>
</div>
