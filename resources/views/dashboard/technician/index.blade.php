<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
            {{ __('dashboard.technician_title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


                <div class="min-h-[635px]" >
                    <!-- Grafico a torta per vedere la divisione di utenti tra gli eventi creati dal tecnico o in cui il tecnico fa parte del personale. -->
                        <x-dashboard.technician.events-participants-year-graph />
                </div>


                <!-- Richieste di promozione a Preside delle school -->
                <!-- Richiesta di promozione a cavaliere -->

            </div>


        </div>
    </div>
</x-app-layout>
