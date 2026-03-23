@props(['size' => 'md'])

@php
    $sizeClasses = match($size) {
        'xs' => 'px-1 py-0 text-xs',
        'sm' => 'px-2 py-1 text-xs',
        'md' => 'px-4 py-2 text-xs',
        'lg' => 'px-6 py-3 text-sm',
        default => 'px-4 py-2 text-xs',
    };
@endphp

<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center ' . $sizeClasses . ' bg-primary-500 dark:bg-primary-400 border border-transparent rounded-md font-semibold text-white dark:text-background-800 uppercase tracking-widest hover:bg-background-700 dark:hover:bg-primary-600 focus:bg-background-700 dark:focus:bg-primary-500 active:bg-background-900 dark:active:bg-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-background-800 transition ease-in-out duration-150 disabled:bg-background-100 disabled:hover:bg-background-100 disabled:text-background-500']) }}>
    {{ $slot }}
</button>
