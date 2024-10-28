<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12">
            <h1
                class="text-6xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                {{ __('website.events_list_title') }}
            </h1>

            <p class="text-background-800 dark:text-background-200 text-justify">{{ __('website.events_list_text') }}
            </p>

            <div class="w-full flex items-end" x-data="{
                nationFilter: '{{ $nationFilter }}'
            }" x-init="$watch('nationFilter', (value) => window.location.href = `{{ env('APP_URL') }}/events-list?nation=${value}`)">
                <div class="flex-1">
                    <x-form.select name="country" label="{{ __('website.academies_map_nations') }}" x-model="nationFilter"
                        shouldHaveEmptyOption="false" :options="$continents" />
                </div>
                <a href="{{ route('events-list') }}" class="ml-2">
                    <x-primary-button>
                        <x-lucide-refresh-ccw class="w-4 h-6" />
                    </x-primary-button>
                </a>
            </div>

            <div class="flex flex-col gap-2 py-2">

                @if ($events->isEmpty())
                    <div
                        class="bg-white dark:bg-background-800 rounded dark:text-background-300 p-4 flex flex-col justify-between gap-2">
                        <p class="text-2xl font-semibold group-hover:text-primary-500">
                            {{ __('website.events_list_no_events') }}
                        </p>
                    </div>
                @endif

                @foreach ($events as $event)
                    <div x-data="{
                        start_date: '{{ $event->start_date }}',
                        end_date: '{{ $event->end_date }}',
                    }"
                        class="bg-white text-background-800 dark:bg-background-800 rounded dark:text-background-300 p-4 flex flex-col justify-between gap-2">
                        <p class="text-2xl font-semibold group-hover:text-primary-500">
                            {{ $event->name }}
                        </p>
                        <div class="flex items-center gap-1">
                            <x-lucide-calendar-days class="w-4 h-4 text-primary-500" />
                            <div class="flex flex-row gap-2">
                                <p x-text="new Date(start_date).toLocaleDateString('it-IT', {
                                    hour: 'numeric', 
                                    minute: 'numeric' 
                                })"
                                    class="text-xs"></p>
                                <span class="text-xs"> - </span>
                                <p x-text="new Date(end_date).toLocaleDateString('it-IT', {
                                    hour: 'numeric', 
                                    minute: 'numeric' 
                                })"
                                    class="text-xs"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <x-lucide-map-pin class="w-4 h-4 text-primary-500" />
                            <span
                                class="text-sm font-semibold group-hover:text-primary-500">{{ $event->full_address }}</span>
                        </div>

                        <a href="{{ route('event-detail', $event->slug) }}">
                            <x-primary-button>
                                {{ __('website.events_list_button') }}
                            </x-primary-button>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-website-layout>
