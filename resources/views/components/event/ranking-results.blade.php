@props([
    'results' => [],
])

<div
    class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-background-900 dark:text-background-100">

    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.ranking_results') }}
    </h3>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

    <x-table striped="false" :columns="[
        [
            'name' => 'User',
            'field' => 'user_fullname',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td
        ],
        [
            'name' => 'Arena Points',
            'field' => 'war_points',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td]
        ],
        [
            'name' => 'Bonus',
            'field' => 'bonus_war_points',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td]
        ],
        [
            'name' => 'Style Points',
            'field' => 'style_points',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td]
        ],
        [
            'name' => 'Bonus',
            'field' => 'bonus_style_points',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td]
        ],
        [
            'name' => 'Total Arena Points',
            'field' => 'total_war_points',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td]
        ],
        [
            'name' => 'Total Style Points',
            'field' => 'total_style_points',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td]
        ],
    ]" :rows="$results">

    </x-table>
</div>
