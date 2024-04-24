@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-background-700 dark:text-background-300']) }}>
    {{ $value ?? $slot }}
</label>
