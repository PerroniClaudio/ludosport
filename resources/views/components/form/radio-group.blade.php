@props(['name', 'options', 'label' => null, 'selected' => null])

<div x-data="{ selected: '{{ $selected }}' }" class="flex flex-col gap-2">
    @if($label)
        <span class="block font-medium text-sm text-background-700 dark:text-background-300 mb-1">{{ $label }}</span>
    @endif
    <div class="flex gap-4">
        @foreach($options as $value => $text)
            <label class="flex items-center gap-1 cursor-pointer">
                <input type="radio" name="{{ $name }}" :value="'{{ $value }}'" x-model="selected"
                    @click="selected === '{{ $value }}' ? selected = '' : selected = '{{ $value }}'"
                    :checked="selected === '{{ $value }}'"
                    class="form-radio text-primary-500" />
                <span class="text-sm text-background-700 dark:text-background-300">{{ $text }}</span>
            </label>
        @endforeach
    </div>
    <input type="hidden" name="{{ $name }}" :value="selected">
</div>
