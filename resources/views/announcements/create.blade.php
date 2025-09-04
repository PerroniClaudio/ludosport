<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('announcements.new') }}
            </h2>

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <form method="POST" action="{{ route('announcements.store') }}" class="flex flex-col gap-4">
                    @csrf

                    <x-form.input name="object" label="Object" type="text" required="{{ true }}"
                        value="{{ old('object') }}" placeholder="{{ fake()->sentence() }}" />


                    <x-form.select name="type" label="Type" required="{{ true }}" :options="$types"
                        value="{{ old('type') }}" shouldHaveEmptyOption="true" />


                    <div class="flex items-top gap-4">
                        <div class="flex-1">
                            <x-announcements.roles-select :roles="$roles" />
                        </div>
                        <div class="flex-1">
                            <x-announcements.nation-select :nations="collect($nations)" />
                        </div>
                        <div class="flex-1">
                            <x-announcements.academies-select :academies="collect($academies)" />
                        </div>
                    </div>

                    <x-rich-text-editor name="content" label="Content" required="{{ true }}"
                        value="{{ old('content') }}" placeholder="Write a message..." />

                    <div class="flex items-end justify-end gap-4">
                        <x-primary-button>
                            {{ __('announcements.send') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>
