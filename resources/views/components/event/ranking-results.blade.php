@props([
    'results' => [],
])

<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-background-900 dark:text-background-100">
        <x-table striped="false" :columns="[
            [
                'name' => 'User',
                'field' => 'user_fullname',
                'columnClasses' => '', // classes to style table th
                'rowClasses' => '', // classes to style table td
            ],
            [
                'name' => 'War Points',
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
                'name' => 'Total War Points',
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
</div>