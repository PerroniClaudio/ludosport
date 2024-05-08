@props(['name','label', 'type' => 'text', 'required' => false, 'disabled' => false, 'value' => '', 'placeholder' => ''])

<div>
    <x-input-label value="{{ $label }}" />
    <input name="{{ $name }}" type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $required ? 'required' : '' }} value="{{ $value }}" placeholder="{{ $placeholder }}" class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
    <x-input-error :messages="$errors->get($name)"class="mt-2" />
</div>
