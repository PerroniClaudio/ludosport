@props(['school', 'selectedSchoolData' => [], 'schoolsData' => []])
@php
    $authRole = auth()->user()->getRole();
@endphp
<div x-load x-data="usercoursegraphadmin('{{ $authRole }}', {{ $school }}, {{ $selectedSchoolData }})">

    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-background-900 dark:text-background-100">
            <h3 class="text-background-800 dark:text-background-200 text-2xl"
                x-text="`{{ __('dashboard.athletes_courses_title', ['school' => '${school.name}']) }}`">
            </h3>
            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <canvas id="usercoursegraph"></canvas>
                </div>
                <div class="flex flex-col gap-8">
                    <div class="flex flex-col gap-4 grow">
                        <div class="flex justify-between">
                            <h3 class="text-background-800 dark:text-background-200 text-lg">
                                {{ __('dashboard.courses_with_athletes_char') }}
                            </h3>
                            <button @click="$data.setLevel('academy');" class="p-1 bg-primary-500 rounded">
                                <x-lucide-arrow-left class="h-6 w-6 text-white" />
                            </button>
                        </div>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <x-text-input type="text" x-on:input="searchCourseByValue(event)" placeholder="Search..."
                            class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                        <ul class="flex flex-col gap-2 px-0 grow">
                            <template x-for="course in paginatedCourses" :key="course.id">
                                <li class="flex items-center justify-between">
                                    <div>
                                        <span x-text="course.name"></span>
                                        <span x-text="course.athletes"></span>
                                    </div>
                                    <div class="flex gap-2">
                                        <x-primary-link-button-small x-bind:href="'/courses/' + course.id">
                                            <x-lucide-pencil class="h-6 w-6 text-white" />
                                        </x-primary-link-button-small>
                                    </div>
                                </li>
                            </template>
                        </ul>

                        <div class="flex justify-between ">
                            <x-primary-button-small @click="previousPage" x-bind:disabled="currentCoursesPage === 1">
                                <x-lucide-chevron-left class="h-6 w-6 text-white" />
                            </x-primary-button-small>
                            <span>Page <span x-text="currentCoursesPage"></span> of <span
                                    x-text="totalCoursesPages"></span></span>
                            <x-primary-button-small @click="nextPage"
                                x-bind:disabled="currentCoursesPage === totalCoursesPages">
                                <x-lucide-chevron-right class="h-6 w-6 text-white" />
                            </x-primary-button-small>
                        </div>


                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-4 bg-background-100 dark:bg-background-700 rounded-lg">
                            <p>{{ __('dashboard.rector_users_last_year') }}</p>
                            <p class="text-primary-600 dark:text-primary-500 text-3xl"
                                x-text="schoolYearData.last_year"></p>
                        </div>
                        <div class="p-4 bg-background-100 dark:bg-background-700 rounded-lg">
                            <p>{{ __('dashboard.rector_users_this_year') }}</p>
                            <p class="text-primary-600 dark:text-primary-500 text-3xl"
                                x-text="schoolYearData.this_year"></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
