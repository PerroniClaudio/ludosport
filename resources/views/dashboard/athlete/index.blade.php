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
                        {{ __('dashboard.athlete_announcements') }}
                    </h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    @if (collect($announcements)->count() > 0)
                        <p>
                            {{ __('dashboard.athlete_announcements_text', [
                                'count' => collect($announcements)->count(),
                            ]) }}
                        </p>
                    @else
                        <p>
                            {{ __('dashboard.athlete_no_announcements') }}
                        </p>
                    @endif

                    <div class="flex justify-end">
                        <a href="{{ route('athlete.announcements.index') }}">
                            <x-primary-button>
                                <x-lucide-arrow-right class="h-6 w-6 text-white" />
                            </x-primary-button>
                        </a>
                    </div>
                </div>
            </div>


        </div>
    </div>
</x-app-layout>
