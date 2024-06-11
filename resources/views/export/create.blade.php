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
                <div class="p-6 text-background-900 dark:text-background-100">
                    <div class="flex flex-col gap-2 w-1/2">
                        <x-form.select name="type" label="Type" required="{{ true }}" :options="$types"
                            x-model="selectedType" shouldHaveEmptyOption="true" />
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>
