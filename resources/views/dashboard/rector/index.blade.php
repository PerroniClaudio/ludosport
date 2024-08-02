<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
            {{ __('dashboard.title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex flex-col gap-4">

                <x-dashboard.user-academy-numbers academyId="{{ Auth()->user()->academies()->first()->id }}" />

                <!-- Paga in bulk le fee degli utenti non attivi -->

                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-background-900 dark:text-background-100">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">
                            {{ __('dashboard.rector_bulk_fee') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <p>{{ __('dashboard.rector_bulk_fee_text') }}</p>
                        <div class="flex justify-end ">
                            <a href="{{ route('fees.index') }}">
                                <x-primary-button>
                                    <x-lucide-arrow-right class="h-6 w-6 text-white" />
                                </x-primary-button>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Grafico a torta per vedere la divisione di utenti tra le school + Confronto tra iscritti anno precedente e iscritti anno corrente  -->

                <x-dashboard.user-school-graph academyId="{{ Auth()->user()->academies()->first()->id }}" />

                <!-- Richieste di promozione a Preside delle school -->
                <!-- Richiesta di promozione a cavaliere -->

            </div>

        </div>
    </div>
</x-app-layout>
