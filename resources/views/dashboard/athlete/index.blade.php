<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
            {{ __('dashboard.title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">

            @if (auth()->user()->has_paid_fee)
                @if (auth()->user()->isFeeExpiring())
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                        <div class="p-6 text-background-900 dark:text-background-100">
                            <div class="text-red-500 flex items-center gap-1">
                                <x-lucide-circle-alert class="h-6 w-6" />
                                {{ __('users.fee_about_expire_text') }}
                            </div>
                        </div>
                    </div>
                @endif
            @endif


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

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">
                        {{ __('dashboard.athlete_upcoming_events') }}
                    </h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    @unless (count(collect(auth()->user()->eventResults())) == 0)
                        @foreach (auth()->user()->eventResults()->get() as $eventSubscription)
                            @if (\Carbon\Carbon::parse($eventSubscription->event->start_date)->isFuture())
                                <div x-data="{
                                    start_date: '{{ $eventSubscription->event->start_date }}',
                                    end_date: '{{ $eventSubscription->event->end_date }}',
                                }"
                                    class="bg-white text-background-800 dark:bg-background-900 rounded dark:text-background-300 p-4 flex flex-col justify-between gap-2">
                                    <p class="text-lg font-semibold group-hover:text-primary-500">
                                        {{ $eventSubscription->event->name }}
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
                                        <span class="text-sm font-semibold group-hover:text-primary-500">
                                            {{ $eventSubscription->event->address }} ,
                                            {{ $eventSubscription->event->postal_code }} ,
                                            {{ $eventSubscription->event->city }}
                                        </span>
                                    </div>

                                    <a href="{{ route('event-detail', $eventSubscription->event->slug) }}">
                                        <x-primary-button>
                                            {{ __('website.events_list_button') }}
                                        </x-primary-button>
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    @endunless
                </div>
            </div>


        </div>
    </div>
</x-app-layout>
