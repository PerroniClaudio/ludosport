<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('announcements.edit_title') }}
            </h2>

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <form method="POST" action="{{ route('announcements.update', $announcement->id) }}"
                    class="flex flex-col gap-4">
                    @csrf

                    <x-form.input name="object" label="Object" type="text" required="{{ true }}"
                        value="{{ $announcement->object }}" placeholder="{{ fake()->sentence() }}" />

                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <x-form.select name="role" label="Role" required="{{ true }}"
                                :options="$roles" value="{{ $announcement->role_id }}" shouldHaveEmptyOption="true" />
                        </div>
                        <div class="flex-1">
                            <x-form.select name="type" label="Type" required="{{ true }}"
                                :options="$types" value="{{ $announcement->type }}" shouldHaveEmptyOption="true" />
                        </div>
                    </div>

                    <x-form.textarea name="content" label="Content" required="{{ true }}"
                        value="{{ $announcement->content }}" placeholder="Write a message..." />

                    <div class="flex items-end justify-end gap-4">
                        <x-primary-button>
                            {{ __('announcements.save') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>

            <x-announcements.disable-announcement :announcement="$announcement" />
        </div>
    </div>

</x-app-layout>
