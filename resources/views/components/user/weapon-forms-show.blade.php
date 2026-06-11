@props([
    'forms' => [],
    'user' => [],
])


<section
    class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-2 sm:p-8 text-background-800 dark:text-background-200">
    <h3 class="text-2xl">{{ __('navigation.weapon_forms') }}</h3>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
    <div class="bg-background-800 py-4 rounded-lg">
        <x-user.weapon-forms-grid :user="$user" />
    </div>

</section>
