<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('exports.new') }}
            </h2>

        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100" x-data="{
                    selectedType: 'users_academy',
                }">
                    <div class="flex flex-col gap-2 w-1/2">
                        <x-form.select name="type" label="Type" required="{{ true }}" :options="$types"
                            x-model="selectedType" shouldHaveEmptyOption="true" />
                    </div>

                    <div>

                        <div x-show="selectedType == 'users'">
                            <x-exports.users />
                        </div>

                        <div x-show="selectedType == 'user_roles'">
                            <x-exports.user-roles :roles="$roles" />
                        </div>

                        <div x-show="selectedType == 'users_course'">
                            <x-exports.user-course />
                        </div>

                        <div x-show="selectedType == 'users_academy'">
                            <x-exports.user-academy />
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
