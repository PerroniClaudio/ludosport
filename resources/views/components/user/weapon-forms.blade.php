@props([
    'forms' => [],
])

<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
    <h3 class="text-background-800 dark:text-background-200 text-2xl">
        {{ __('users.weapons_forms') }}</h3>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
    <x-table striped="false" :columns="[
        [
            'name' => 'Name',
            'field' => 'name',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td
        ],
        [
            'name' => 'Awarded on',
            'field' => 'awarded_at',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td
        ],
    ]" :rows="$forms">

    </x-table>
</div>
