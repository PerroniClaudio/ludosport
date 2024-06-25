<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('clan.new') }}
            </h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <form method="POST" action="{{ route('clans.store') }}">
                    @csrf
                    <div class="flex flex-col gap-2 w-1/2">
                        <x-form.input name="name" label="Name" type="text" required="{{ true }}"
                            value="{{ old('name') }}" placeholder="{{ fake()->company() }}" />
                        {{-- 
                        <x-form.select name="school_id" label="School" required="{{ true }}" :options="$schools"
                            value="{{ old('school_id') }}" /> --}}

                        <x-clan.school />

                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <x-primary-button type="submit">
                            {{ __('clan.create') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
</x-app-layout>
