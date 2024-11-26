<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('events.title') }}
            </h2>
            <div>
                <x-create-new-button :href="route('dean.events.create')" />
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-12 gap-4" x-load x-data="calendar('{{ route('dean.events.calendar') }}')">

                <div class="col-span-12 md:col-span-3 flex flex-col gap-4">
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <details class="p-6 text-background-900 dark:text-background-100">
                            <summary class="text-background-800 dark:text-background-200 text-2xl">
                                {{ __('events.pending') }}</summary>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            <div class="flex flex-col gap-2">
                                <template x-for="(event, index) in pending_events">
                                    <a x-bind:href="event.url" target="_blank"
                                        class="flex flex-col gap-1 p-2 rounded-md hover:bg-background-100 dark:hover:bg-background-900 group">
                                        <p x-text="event.title" class="font-semibold group-hover:text-primary-500 m-0">
                                        </p>
                                        <div class="flex items-center gap-1">
                                            <x-lucide-calendar-days class="w-4 h-4 text-primary-500" />
                                            <div class="flex flex-col">
                                                <p x-text="new Date(event.start).toLocaleDateString('it-IT', {
                                                    hour: 'numeric', 
                                                    minute: 'numeric' 
                                                })"
                                                    class="text-xs"></p>
                                                <p x-text="new Date(event.end).toLocaleDateString('it-IT', {
                                                    hour: 'numeric', 
                                                    minute: 'numeric' 
                                                })"
                                                    class="text-xs"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1 text-xs">
                                            <x-lucide-user class="w-4 h-4 text-primary-500" />
                                            <span x-text="event.user"></span>
                                        </div>
                                        <div class="flex items-center gap-1 text-xs">
                                            <x-lucide-graduation-cap class="w-4 h-4 text-primary-500" />
                                            <span x-text="event.academy"></span>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </details>
                    </div>
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <details class="p-6 text-background-900 dark:text-background-100">
                            <summary class="text-background-800 dark:text-background-200 text-2xl">
                                {{ __('events.approved') }}</summary>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            <div class="flex flex-col gap-2">
                                <template x-for="(event, index) in approved_events">
                                    <a x-bind:href="event.url" target="_blank"
                                        class="flex flex-col gap-1 p-2 rounded-md hover:bg-background-100 dark:hover:bg-background-900 group">
                                        <p x-text="event.title" class="font-semibold group-hover:text-primary-500"></p>
                                        <div class="flex items-center gap-1">
                                            <x-lucide-calendar-days class="w-4 h-4 text-primary-500" />
                                            <div class="flex flex-col">
                                                <p x-text="new Date(event.start).toLocaleDateString('it-IT', {
                                                    hour: 'numeric', 
                                                    minute: 'numeric' 
                                                })"
                                                    class="text-xs"></p>
                                                <p x-text="new Date(event.end).toLocaleDateString('it-IT', {
                                                    hour: 'numeric', 
                                                    minute: 'numeric' 
                                                })"
                                                    class="text-xs"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1 text-xs">
                                            <x-lucide-user class="w-4 h-4 text-primary-500" />
                                            <span x-text="event.user"></span>
                                        </div>
                                        <div class="flex items-center gap-1 text-xs">
                                            <x-lucide-graduation-cap class="w-4 h-4 text-primary-500" />
                                            <span x-text="event.academy"></span>
                                        </div>
                                        <div x-show="event.is_published" class="flex items-center gap-1 text-xs">
                                            <x-lucide-eye class="w-4 h-4 text-primary-500" />
                                            <span>{{ __('events.published') }}</span>
                                        </div>
                                        <div x-show="!event.is_published" class="flex items-center gap-1 text-xs">
                                            <x-lucide-eye-off class="w-4 h-4 text-primary-500" />
                                            <span>{{ __('events.hidden') }}</span>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </details>
                    </div>
                </div>
                <div class="col-span-12 md:col-span-9">
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div x-ref="calendar" class="p-6 text-background-900 dark:text-background-100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
