@php
    $authRole = auth()->user()->getRole();
@endphp
<div x-data="usernationgraphadmin('{{ $authRole }}')">

    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-background-900 dark:text-background-100">
            <h3 class="text-background-800 dark:text-background-200 text-2xl">
                {{ __('dashboard.rector_athletes_nations_title') }}
            </h3>
            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <canvas id="usernationgraph"></canvas>
                </div>
                <div class="flex flex-col gap-8">
                    <div class="flex flex-col gap-4 grow">
                        <h3 class="text-background-800 dark:text-background-200 text-lg">
                            {{ __('dashboard.rector_nations_with_athletes_char') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <x-text-input type="text" x-on:input="searchNationByValue(event)" placeholder="Search..."
                            class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                        <ul class="flex flex-col gap-2 px-0 grow">
                            <template x-for="nation in paginatedNations" :key="nation.id">
                                <li class="flex items-center gap-4" >
                                    <div class="grow flex justify-between">
                                        <span x-text="nation.name"></span>
                                        <span x-text="nation.athletes"></span>
                                    </div>
                                    <div class="flex gap-2">
                                        <x-primary-button-small @click="$dispatch('nation-selected', nation.id)">
                                            <x-lucide-arrow-right class="h-6 w-6 text-white" />
                                        </x-primary-button-small>
                                        <x-primary-link-button-small x-bind:href="'/nations/' + nation.id" >
                                            <x-lucide-pencil class="h-6 w-6 text-white" />
                                        </x-primary-link-button-small>
                                    </div>
                                </li>
                            </template>
                        </ul>
    
                        <div class="flex justify-between ">
                            <x-primary-button-small @click="previousPage" x-bind:disabled="currentNationsPage === 1">
                                <x-lucide-chevron-left class="h-6 w-6 text-white" />
                            </x-primary-button-small>
                            <span>Page <span x-text="currentNationsPage"></span> of <span x-text="totalNationsPages"></span></span>
                            <x-primary-button-small @click="nextPage" x-bind:disabled="currentNationsPage === totalNationsPages">
                                <x-lucide-chevron-right class="h-6 w-6 text-white" />
                            </x-primary-button-small>
                        </div>
    
                        
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-4 bg-background-100 dark:bg-background-700 rounded-lg">
                            <p>{{ __('dashboard.rector_users_last_year') }}</p>
                            <p class="text-primary-600 dark:text-primary-500 text-3xl" x-text="worldYearData.last_year"></p>
                        </div>
                        <div class="p-4 bg-background-100 dark:bg-background-700 rounded-lg">
                            <p>{{ __('dashboard.rector_users_this_year') }}</p>
                            <p class="text-primary-600 dark:text-primary-500 text-3xl" x-text="worldYearData.this_year"></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
