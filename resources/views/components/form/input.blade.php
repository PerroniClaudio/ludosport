@props([
    'name' => '',
    'label' => '',
    'type' => 'text',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'value' => '',
    'placeholder' => '',
    'hidden' => false,
    'min' => null,
    'max' => null,
    'description' => null,
    'step' => null,
    'text_before' => '',
])

<div>
    @if (!$hidden)
        @if ($description)
            <div class="flex gap-1">
                <x-input-label value="{{ $label }}" />
                <div x-data="{ tooltip: false }" x-on:mouseover="tooltip = true" x-on:mouseleave="tooltip = false">
                    <div x-show="tooltip"
                        x-cloak
                        class="absolute bg-background-100 
                        p-2 rounded-md text-sm text-background-800 
                        inline-block break-words w-max max-w-80 -translate-x-1/2 -translate-y-full">
                        {{ $description }}
                    </div>
                    <x-lucide-info class="h-4 text-background-300" />
                </div>
            </div>
        @else
            <x-input-label value="{{ $label }}" />
        @endif
    @endif

    {{-- Per evitare di ripetere l'input ci sono due if con la stessa condizione per contenerlo se servisse --}}
    @if ($text_before)
            <div class="flex gap-2 items-center">
                <p>{{ $text_before }}</p> 
    @endif

                <input name="{{ $name }}" type="{{ $type }}" {{ $disabled ? 'disabled' : '' }}
                    {{ $readonly ? 'readonly' : '' }} {{ $required ? 'required' : '' }} value="{{ $value }}"
                    placeholder="{{ $placeholder }}" @if ($min != null) min="{{ $min }}" @endif
                    @if ($max != null) max="{{ $max }}" @endif
                    @if ($step != null) step="{{ $step }}" @endif
                    class="{{ $hidden ? 'hidden' : '' }} {{ $disabled ? 'cursor-not-allowed' : '' }} w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
    
    @if ($text_before)
            </div>
    @endif

    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>
