<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12">
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
                            <span class="text-background-800 dark:text-background-200 font-4xl">{{ $event->address }}, {{ $event->postal_code }}
                                {{ $event->city }}, {{ $event->nation->name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            @php

                                $start_date = new DateTime($event->start_date);
                                $end_date = new DateTime($event->end_date);

                            @endphp

                            <x-lucide-calendar-days class="w-10 h-10 text-primary-500 dark:text-primary-600" />
                            <div class="flex flex-col gap-1">
                                <span class="text-background-800 dark:text-background-200">{{ __('events.start_date') }}:
                                    {{ $start_date->format('d/m/Y H:i') }}
                                </span>
                                <span class="text-background-800 dark:text-background-200">{{ __('events.end_date') }}:
                                    {{ $end_date->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        </div>

                        @if ($canpurchase)
                            <a href="{{ route('event-purchase', $event->id) }}">
                                <x-primary-button>
                                    @if($is_waiting_payment)
                                        {{ __('website.events_waiting_payment') }}
                                    @elseif($only_waiting_list)
                                        {{ __('website.events_list_waiting_list') }}
                                    @else
                                        {{ __('website.events_list_participate') }}
                                    @endif    
                                </x-primary-button>
                            </a>
                        @elseif (!Auth()->user()->has_paid_fee)
                            <p>{{__('website.events_pay_fee_before')}}</p>
                        @elseif ($is_participating)
                            <p>{{__('website.events_participating')}}</p>
                        @elseif ($is_in_waiting_list)
                            {{-- Qui si può aggiungere il bottone per mandarlo al completamento del pagamento se ha il flag is_waiting_payment --}}
                            <p>{{__('website.events_in_waiting_list')}}</p>
                        @elseif ($block_subscriptions || !$event->internal_shop)
                            <p>{{__('website.events_subscriptions_blocked')}}</p>
                        @endif

                    </div>
                    <div x-data="googlemap('{{ $event->location }}')" x-ref="eventGoogleMapContainer">
                        <x-maps-google id="eventGoogleMap" style="height: 400px"></x-maps-google>
                    </div>
                </section>
            </div>
        </section>
    </div>
</x-website-layout>
