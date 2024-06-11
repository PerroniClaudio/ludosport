@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'parent-nav inline-flex items-center px-1 pt-1 border-b-2 border-primary-400 text-sm font-semibold leading-5 text-background-900 dark:text-background-100 focus:outline-none focus:border-primary-700 transition duration-150 ease-in-out cursor-pointer relative'
            : 'parent-nav inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-semibold leading-5 text-background-500 dark:text-background-400 hover:text-background-700 dark:hover:text-background-300 hover:border-background-300 dark:hover:border-background-700 focus:outline-none focus:text-background-700 focus:border-background-300 transition duration-150 ease-in-out cursor-pointer relative';
@endphp

<div x-data="{ open: false }" @click.away="open = false" @close.stop="open = false" @click="open = ! open"
    {{ $attributes->merge(['class' => $classes]) }}>
    <div>
        {{ $name }}

        <div class="ml-1 inline-block relative top-1">
            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </div>
    </div>

    <div class="children rounded-md" x-show="open" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95">
        {{ $children }}
    </div>
</div>
