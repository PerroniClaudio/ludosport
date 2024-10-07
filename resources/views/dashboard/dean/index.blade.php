<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
            {{ __('dashboard.title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex flex-col gap-4">

                @if (Auth()->user()->primarySchool())
                    <x-dashboard.user-school-numbers schoolId="{{ Auth()->user()->primarySchool()->id }}" />

                    <x-dashboard.user-clan-graph schoolId="{{ Auth()->user()->primarySchool()->id }}" />
                @else
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-background-900 dark:text-background-100">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                {{ __('dashboard.dean_no_school') }}
                            </h3>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
