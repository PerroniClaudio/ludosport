<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 sm:px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12">
            <div
                class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-2 sm:p-8 text-background-800 dark:text-background-200">
                @if ($event->thumbnail)
                    <img src="/event-picture/{{ $event->id }}" alt="{{ $event->name }}" class="h-[400px] max-w-[600px] object-cover rounded-lg">
                @endif
                <h1 class="font-semibold text-3xl  leading-tight">
                    {{ $event->name }}</h1>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                <section class="">
                    {!! $event->description !!}
                </section>

                <section class="grid grid-cols-1 lg:grid-cols-2 gap-4 my-8">
                    <div class="flex flex-col gap-2 bg-background-100 dark:bg-background-700 rounded-lg p-8 text-sm sm:text-base">

                        <h2 class="font-semibold text-2xl  leading-tight">
                            {{ __('events.info') }}</h2>
                        <div class="border-b border-background-100 dark:border-background-500 my-2"></div>

                        <div class="flex items-center gap-2">
                            <x-lucide-map-pin class="w-6 h-6 sm:w-10 sm:h-10 shrink-0 text-primary-500 dark:text-primary-600" />
                            <span>{{ $event->address }},
                                {{ $event->postal_code }}
                                {{ $event->city }}, {{ $event->nation->name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            @php

                                $start_date = new DateTime($event->start_date);
                                $end_date = new DateTime($event->end_date);

                            @endphp

                            <x-lucide-calendar-days class="w-6 h-6 sm:w-10 sm:h-10 shrink-0 text-primary-500 dark:text-primary-600" />
                            <div class="flex flex-col gap-1">
                                <span class="">{{ __('events.start_date') }}:
                                    {{ $start_date->format('d/m/Y H:i') }}
                                </span>
                                <span class="">{{ __('events.end_date') }}:
                                    {{ $end_date->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        </div>

                        @if ($event->academy)
                            <div class="flex items-center gap-2">
                                <x-lucide-swords class="w-6 h-6 sm:w-10 sm:h-10 shrink-0 text-primary-500 dark:text-primary-600" />
                                <div class="flex flex-col gap-1">
                                    <div>
                                        {{ __('academies.academy') }}: <a
                                            class="text-primary-500 dark:text-primary-600"
                                            href="{{ route('academy-profile', $event->academy->slug) }}">
                                            {{ $event->academy->name }}
                                        </a>
                                    </div>
                                    @if ($academy_email)
                                        <div>
                                            {{ __('academies.academy_email') }}: <a
                                                class="text-primary-500 dark:text-primary-600"
                                                href="mailto:{{ $academy_email ?? '' }}">
                                                {{ $academy_email ?? '' }}
                                            </a>
                                        </div>
                                    @elseif (isset($event->academy->rector()->email))
                                        <div>
                                            {{ __('academies.rector_email_website') }}: <a
                                                class="text-primary-500 dark:text-primary-600"
                                                href="mailto:{{ $event->academy->rector()->email ?? '' }}">
                                                {{ $event->academy->rector()->email ?? '' }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @auth



                            @if ($canpurchase)
                                <a href="{{ route('event-purchase', $event->id) }}">
                                    <x-primary-button>
                                        @if ($is_waiting_payment)
                                            {{ __('website.events_waiting_payment') }}
                                        @elseif($only_waiting_list)
                                            {{ __('website.events_list_waiting_list') }}
                                        @else
                                            {{ __('website.events_list_participate') }}
                                        @endif
                                    </x-primary-button>
                                </a>
                            @elseif (!Auth()->user()->has_paid_fee)
                                <p>{{ __('website.events_pay_fee_before') }}</p>
                            @elseif ($is_participating)
                                <p>{{ __('website.events_participating') }}</p>
                            @elseif ($is_in_waiting_list)
                                {{-- Qui si può aggiungere il bottone per mandarlo al completamento del pagamento se ha il flag is_waiting_payment --}}
                                <p>{{ __('website.events_in_waiting_list') }}</p>
                            @elseif ($block_subscriptions || !$event->internal_shop)
                                <p>{{ __('website.events_subscriptions_blocked') }}</p>
                            @elseif ($waiting_list_closed)
                                <p>{{ __('website.event_waiting_list_closed_text') }}</p>
                            @endif

                        @endauth

                    </div>
                    <div x-load x-data="googlemap('{{ $event->location }}')" x-ref="eventGoogleMapContainer">
                        <x-maps-google id="eventGoogleMap" style="height: 400px"></x-maps-google>
                    </div>
                </section>
            </div>
        </section>
    </div>
</x-website-layout>
