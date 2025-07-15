@props(['field', 'selectedRole', 'noHeaderTag' => false])

{{-- If noHeaderTag is true, the component will not render the <th> tag --}}

@php
    $currentSort = request('sortedby');
    $currentDirection = request('direction', 'asc');
    $newDirection = $currentSort === $field && $currentDirection === 'asc' ? 'desc' : 'asc';

    $authUser = auth()->user();
    $authUserRole = $authUser ? $authUser->getRole() : null;

    $sortUrl = route(
        $authUserRole === 'admin' ? 'users.index' : $authUserRole . '.users.index',
        array_merge(request()->query(), [
            'sortedby' => $field,
            'role' => $selectedRole,
            'direction' => $newDirection,
        ]),
    );

    $isCurrentSort = $currentSort === $field;
@endphp

@if (!$noHeaderTag)
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
@else
    <div {{ $attributes->merge(['class' => 'px-6 w-[200px] min-w-[200px] max-w[200px]']) }}>
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
    </div>
@endif
