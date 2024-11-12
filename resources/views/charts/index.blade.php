<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('charts.title') }}
            </h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">

                    <x-table striped="false" :columns="[
                        [
                            'name' => 'Name',
                            'field' => 'user_name',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                        [
                            'name' => 'Academy',
                            'field' => 'user_academy',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                        [
                            'name' => 'School',
                            'field' => 'user_school',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                        [
                            'name' => 'Nation',
                            'field' => 'nation',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                        [
                            'name' => 'Arena points',
                            'field' => 'total_war_points',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                        [
                            'name' => 'Style points',
                            'field' => 'total_style_points',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                    ]" :rows="$chart_data" />

                    <div class="mt-4 flex justify-between">
                        <p class="text-lg">
                            {{ __('events.chart_generated_at', [
                                'date' => \Carbon\Carbon::parse($chart['created_at'])->format('d/m/Y'),
                            ]) }}
                        </p>

                        <a href="{{ route('rankings.create') }}">
                            <x-primary-button>
                                <span>{{ __('events.chart_generate_new') }}</span>
                            </x-primary-button>
                        </a>

                    </div>


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
