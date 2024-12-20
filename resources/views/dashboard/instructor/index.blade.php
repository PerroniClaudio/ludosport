<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
            {{ __('dashboard.title') }}
        </h2>
    </x-slot>
    
    
    <div class="py-12">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">
                        {{ __('dashboard.instructor_rank_requests') }}
                    </h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <p>{{ __('dashboard.instructor_rank_requests_text') }}</p>
                    <div class="flex justify-end">
                        <a href="{{ route('users.rank.request') }}">
                            <x-primary-button>
                                <x-lucide-arrow-right class="h-6 w-6 text-white" />
                            </x-primary-button>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6" x-data="{
            selectedCourse: {{ $course_id }},
            changeSelectedCourse() {
                window.location.href = '/dashboard?course_id=' + this.selectedCourse;
            }
        }">

            <div class="flex justify-end">
                <div>
                    <x-input-label for="course" value="{{ __('dashboard.instructor_courses') }}" />
                    <select x-model="selectedCourse" @change="changeSelectedCourse"
                        class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm">
                        <option value="0">{{ __('All courses') }}</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-col gap-4 mt-4">

                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-background-900 dark:text-background-100">
                        <div class="grid grid-cols-4 gap-4 my-4">
                            <div class="p-4 bg-background-100 dark:bg-background-700 rounded-lg">
                                <h4 class="text-background-800 dark:text-background-200  xl:text-lg">
                                    {{ __('dashboard.rector_active_users') }}</h4>
                                <p class="text-primary-600 dark:text-primary-500 text-3xl">
                                    {{ $active_users_count }}
                                </p>
                            </div>

                            <div class="p-4 bg-background-100 dark:bg-background-700 rounded-lg">
                                <h4 class="text-background-800 dark:text-background-200  xl:text-lg">
                                    {{ __('dashboard.instructor_inactive_users') }}</h4>
                                <p class="text-primary-600 dark:text-primary-500 text-3xl">
                                    {{ $inactive_users_count }}
                                </p>
                            </div>
                        </div>

                        <x-table striped="false" :columns="[
                            [
                                'name' => 'Name',
                                'field' => 'name',
                                'columnClasses' => '', // classes to style table th
                                'rowClasses' => '', // classes to style table td
                            ],
                            [
                                'name' => 'Surname',
                                'field' => 'surname',
                                'columnClasses' => '', // classes to style table th
                                'rowClasses' => '', // classes to style table td
                            ],
                            [
                                'name' => 'Email',
                                'field' => 'email',
                                'columnClasses' => '',
                                'rowClasses' => '',
                            ],
                            [
                                'name' => 'Course',
                                'field' => 'course_name',
                                'columnClasses' => '',
                                'rowClasses' => '',
                            ],
                            [
                                'name' => 'Fee',
                                'field' => 'has_paid_fee',
                                'columnClasses' => '',
                                'rowClasses' => '',
                            ],
                        ]" :rows="$users">
                            <x-slot name="tableRows">
                                <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                    x-text="row.name"></td>
                                <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                    x-text="row.surname"></td>
                                <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                    x-text="row.email"></td>

                                <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                    x-text="row.course_name"></td>
                                <td
                                    class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                    <x-lucide-badge-check class="w-5 h-5 text-primary-800 dark:text-primary-500"
                                        x-show="row.has_paid_fee == 1" />
                                    <x-lucide-badge-info class="w-5 h-5 text-red-800 dark:text-red-500"
                                        x-show="row.has_paid_fee == 0" />
                                </td>
                                {{-- <td
                                    class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                    <a x-bind:href="'/instructor/users/' + row.id">
                                        <x-lucide-pencil
                                            class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                    </a>
                                </td> --}}
                            </x-slot>

                            {{-- <x-slot name="tableActions">

                            </x-slot> --}}

                        </x-table>


                    </div>
                </div>



            </div>

        </div>
    </div>
</x-app-layout>
