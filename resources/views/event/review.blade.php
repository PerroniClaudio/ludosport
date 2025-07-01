<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('events.review_title') }}
            </h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <h1 class="font-semibold text-3xl text-background-800 dark:text-background-200 leading-tight">
                    {{ $event->name }}</h1>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                <section class="text-background-800 dark:text-background-200">
                    {!! $event->description !!}
                </section>

                <section class="grid grid-cols-2 gap-4 my-8">
                    <div class="flex flex-col gap-2 bg-background-100 dark:bg-background-700 rounded-lg p-8">

                        <h2 class="font-semibold text-2xl text-background-800 dark:text-background-200 leading-tight">
                            {{ __('events.info') }}</h2>
                        <div class="border-b border-background-100 dark:border-background-500 my-2"></div>

                        <div class="flex items-center gap-2">
                            <x-lucide-map-pin class="w-10 h-10 text-primary-500 dark:text-primary-600" />
                            <span class="text-background-800 dark:text-background-200 font-4xl">{{ $event->address }},
                                {{ $event->postal_code }}
                                {{ $event->city }}, {{ $event->nation->name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            @php

                                $start_date = new DateTime($event->start_date);
                                $end_date = new DateTime($event->end_date);

                            @endphp

                            <x-lucide-calendar-days class="w-10 h-10 text-primary-500 dark:text-primary-600" />
                            <div class="flex flex-col gap-1">
                                <span
                                    class="text-background-800 dark:text-background-200">{{ __('events.start_date') }}:
                                    {{ $start_date->format('d/m/Y H:i') }}
                                </span>
                                <span class="text-background-800 dark:text-background-200">{{ __('events.end_date') }}:
                                    {{ $end_date->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div x-load x-data="googlemap('{{ $event->location }}')" x-ref="eventGoogleMapContainer">
                        <x-maps-google id="eventGoogleMap" style="height: 400px"></x-maps-google>
                    </div>
                </section>

                <section class="flex justify-end gap-2">
                    <x-event.reject-form :event="$event->id" />
                    <form method="POST" action="{{ route('events.approve', $event->id) }}">
                        @csrf
                        <x-primary-button type="submit">
                            {{ __('events.approve') }}
                        </x-primary-button>
                    </form>
                </section>

            </div>
        </div>
    </div>
</x-app-layout>
