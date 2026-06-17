@props([
    'selectedvalue' => '',
    'required' => false,
])

@php
    $continents = \App\Models\Nation::orderBy('continent')
        ->orderBy('name')
        ->get()
        ->groupBy(fn ($nation) => $nation->continent ?: __('Other'))
        ->map(function ($nations, $continent) {
            return [
                'label' => $continent,
                'options' => $nations->map(fn ($nation) => [
                    'value' => $nation->name,
                    'label' => $nation->name,
                ])->values()->all(),
            ];
        })
        ->all();

    if (isset($continents['Europe'])) {
        $europe = $continents['Europe'];
        unset($continents['Europe']);
        $continents = ['Europe' => $europe] + $continents;
    }
@endphp

<div>
    <x-form.select name="country" label="{{ '* ' . __('fees.invoice_country') }}" :optgroups="$continents"
        value="{{ $selectedvalue }}" xModel="country" :required="$required" shouldHaveEmptyOption="true" />
    <x-input-error :messages="$errors->get('country')" class="mt-2" />
</div>
