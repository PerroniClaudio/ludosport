@props([
    'name',
    'label',
    'type' => 'text',
    'required' => false,
    'value' => '',
    'options' => [],
    'optgroups' => [],
    'disabledOption' => [],
    'selected' => [],
    'xModel' => null,
    'shouldHaveEmptyOption' => false,
])

<div>
    <x-input-label for="{{ $name }}" value="{{ $label }}" />
    <select name="{{ $name }}" id="{{ $name }}"
        class="w-full mt-1 border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm"
        {{ $xModel ? 'x-model=' . $xModel : '' }} {{ $required ? 'required' : '' }}>

        @if ($shouldHaveEmptyOption)
            <option value="" selected>{{ __('Select an option') }}</option>
        @endif

        @if ($selected != null)
            <option value="{{ $selected['value'] }}" selected>{{ $selected['label'] }}</option>
        @endif

        @foreach ($options as $option)
            <option value="{{ $option['value'] }}" {{ $option['value'] == $value ? 'selected' : '' }}>
                {{ $option['label'] }}</option>
        @endforeach

        @foreach ($optgroups as $optgroup)
            <optgroup label="{{ $optgroup['label'] }}">
                @foreach ($optgroup['options'] as $option)
                    <option value="{{ $option['value'] }}" {{ $option['value'] == $value ? 'selected' : '' }}>
                        {{ $option['label'] }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>
