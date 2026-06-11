@props([
    'selectedPosition' => null,
    'showLegend' => true,
])

@php
    $positionOptions = [
        'position_before_specific' => 'Before specific forms',
        'position_long_saber' => 'Long Saber column',
        'position_dual_saber' => 'Dual Saber column',
        'position_saberstaff' => 'Saberstaff column',
        'position_after_specific' => 'After specific forms',
    ];
@endphp

<div class="rounded-lg border border-background-100 dark:border-background-700 p-4"
    x-data="{ selectedPosition: '{{ $selectedPosition ?? '' }}' }">
    @if ($showLegend)
        <div class="mb-4">
            <h4 class="text-lg text-background-800 dark:text-background-200">Display position</h4>
            <p class="text-sm text-background-600 dark:text-background-300">
                Use only for non-default weapon forms. Leave all options off for hardcoded default forms.
            </p>
        </div>
    @endif

    <div class="grid gap-3">
        @foreach ($positionOptions as $field => $label)
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="{{ $field }}" value="1"
                    x-bind:checked="selectedPosition === '{{ $field }}'"
                    x-on:change="selectedPosition = $event.target.checked ? '{{ $field }}' : ''"
                    class="rounded border-background-300 text-primary-500 focus:ring-primary-500">
                <span class="text-sm text-background-800 dark:text-background-200">{{ $label }}</span>
            </label>
        @endforeach
    </div>
</div>
