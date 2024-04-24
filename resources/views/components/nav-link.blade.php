@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-primary-400 dark:border-primary-600 text-sm font-medium leading-5 text-background-900 dark:text-background-100 focus:outline-none focus:border-primary-700 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-background-500 dark:text-background-400 hover:text-background-700 dark:hover:text-background-300 hover:border-background-300 dark:hover:border-background-700 focus:outline-none focus:text-background-700 dark:focus:text-background-300 focus:border-background-300 dark:focus:border-background-700 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
