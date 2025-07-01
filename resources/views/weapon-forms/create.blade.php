<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('weaponf.new') }}
            </h2>

        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('weapon-forms.store') }}"
                    class="p-6 text-background-900 dark:text-background-100">
                    @csrf
                    <div class="flex items-center justify-between">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('weaponf.create') }}
                        </h3>

                    </div>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <div class="w-1/2">
                        <x-form.input label="Name" name="name" />
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <x-primary-button type="submit">
                            {{ __('weaponf.create') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
