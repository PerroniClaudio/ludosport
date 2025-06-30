@props(['field', 'selectedRole'])

@php
    $currentSort = request('sortedby');
    $currentDirection = request('direction', 'asc');
    $newDirection = $currentSort === $field && $currentDirection === 'asc' ? 'desc' : 'asc';

    $sortUrl = route(
        'users.index',
        array_merge(request()->query(), [
            'sortedby' => $field,
            'role' => $selectedRole,
            'direction' => $newDirection,
        ]),
    );

    $isCurrentSort = $currentSort === $field;
@endphp

<th
    {{ $attributes->merge(['class' => 'bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate']) }}>
    <a href="{{ $sortUrl }}"
        class="text-primary-500 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 flex items-center gap-1">
        {{ $slot }}
        @if ($isCurrentSort)
            @if ($currentDirection === 'asc')
                <x-lucide-chevron-up class="w-3 h-3 inline" />
            @else
                <x-lucide-chevron-down class="w-3 h-3 inline" />
            @endif
        @endif
    </a>
</th>
