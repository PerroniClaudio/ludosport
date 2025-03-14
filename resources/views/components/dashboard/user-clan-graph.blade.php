@props(['schoolId' => 1])
@php
    $authRole = auth()->user()->getRole();
@endphp
<div x-load x-data="usersclangraph({{ $schoolId }}, '{{ $authRole }}')">

    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-background-900 dark:text-background-100">
            <h3 class="text-background-800 dark:text-background-200 text-2xl">
                {{ __('dashboard.dean_athletes_courses_title') }}
            </h3>
            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <canvas id="usersclangraph"></canvas>
                </div>
                <div class="flex flex-col gap-4">
                    <h3 class="text-background-800 dark:text-background-200 text-lg">
                        {{ __('dashboard.dean_courses_with_athletes_chart') }}
                    </h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <ul class="flex flex-col gap-2 px-0">
                        <template x-for="clan in clanData" :key="clan.id">
                            <li class="flex items-center justify-between">
                                <span x-text="clan.name"></span>
                                <span x-text="clan.athletes"></span>
                            </li>
                        </template>
                    </ul>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-4 bg-background-100 dark:bg-background-700 rounded-lg">
                            <p>{{ __('dashboard.rector_users_last_year') }}</p>
                            <p class="text-primary-600 dark:text-primary-500 text-3xl" x-text="yearData.last_year"></p>
                        </div>
                        <div class="p-4 bg-background-100 dark:bg-background-700 rounded-lg">
                            <p>{{ __('dashboard.rector_users_this_year') }}</p>
                            <p class="text-primary-600 dark:text-primary-500 text-3xl" x-text="yearData.this_year"></p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
