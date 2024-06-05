@props([
    'data' => '',
])
<div x-data="chart({{ $data }})" class="flex flex-col gap-4">

    <div class="flex items-center">
        <h1 class="text-background-800 dark:text-background-200 text-2xl flex-1"> {{ __('charts.chart_title') }}
            <span
                x-text="new Date(showingDataForDate).toLocaleString('en-US', { month: 'long', year: 'numeric' })"></span>
        </h1>

        <div>
            <x-primary-button @click="previousMonth()">
                <x-lucide-chevron-left class="w-6 h-6 text-white" />
            </x-primary-button>
            <x-primary-button @click="nextMonth()">
                <x-lucide-chevron-right class="w-6 h-6 text-white" />
            </x-primary-button>
        </div>
    </div>

    <div class="flex flex-col gap-2">
        <template x-for="(row, index) in paginatedData">
            <div class="bg-background-900 p-2 flex items-center gap-2 rounded">
                <div class="flex-col items-center justify-center">
                    <x-lucide-swords class="w-6 h-6 text-primary-500" />
                </div>
                <div class="flex-1">
                    <p x-text="row.user.name + ' ' + row.user.surname "></p>
                </div>
                <div class="flex flex-row gap-1 w-1/4">
                    <div class="flex items-center gap-1 flex-1">
                        <x-lucide-sword class="w-6 h-6 text-primary-500" />
                        <p x-text="row.total_war_points"></p>
                    </div>
                    <div class="flex items-center gap-1 flex-1">
                        <x-lucide-sparkles class="w-6 h-6 text-primary-500" />
                        <p x-text="row.total_war_points"></p>
                    </div>
                </div>
            </div>
        </template>

        <div x-show="paginatedData.length === 0"
            class="text-background-500 dark:text-background-300 bg-background-900 p-2 flex items-center gap-2 rounded">
            {{ __('charts.chart_no_data') }}
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <button x-on:click="goToPage(1)" class="mr-2" x-bind:disabled="currentPage === 1">
                    <x-lucide-chevron-first class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                </button>
                <button x-on:click="goToPage(currentPage - 1)" class="mr-2" x-bind:disabled="currentPage === 1"
                    :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }">
                    <x-lucide-chevron-left class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                </button>
                <p class="text-sm text-background-500 dark:text-background-300">Page <span x-text="currentPage"></span>
                    of
                    <span x-text="totalPages"></span>
                </p>
                <button x-on:click="goToPage(currentPage + 1)" class="ml-2"
                    :class="{ 'opacity-50 cursor-not-allowed': currentPage === totalPages }"
                    x-bind:disabled="currentPage === totalPages">
                    <x-lucide-chevron-right class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                </button>
            </div>
        </div>
    </div>

</div>
