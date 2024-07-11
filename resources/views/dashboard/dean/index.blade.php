<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
            {{ __('dashboard.title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex flex-col gap-4">

                @if (Auth()->user()->schools()->first())
                    <x-dashboard.user-school-numbers schoolId="{{ Auth()->user()->schools()->first()->id }}" />

                    <x-dashboard.user-clan-graph schoolId="{{ Auth()->user()->schools()->first()->id }}" />
                @else
                    <div>
                        Nessuna scuola associata
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
