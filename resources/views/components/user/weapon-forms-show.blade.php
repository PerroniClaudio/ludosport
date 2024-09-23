@props([
    'forms' => [],
])

@php
    use App\Models\WeaponForm;
    $weapon_forms = WeaponForm::all();

    $weapon_forms_cycle2 = $weapon_forms->skip(3);
    $weapon_forms_cycle2 = $weapon_forms_cycle2->sortBy('name');

    $user_forms = $forms->pluck('id')->toArray();

@endphp

<section
    class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 text-background-800 dark:text-background-200">
    <h3 class="text-2xl">{{ __('navigation.weapon_forms') }}</h3>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

    <div class="grid grid-cols-3 gap-2 mb-2">

        @foreach ($weapon_forms->take(3) as $weapon_form)
            <div
                class="flex items-center justify-center col-span-3 {{ in_array($weapon_form->id, $user_forms) ? '' : 'opacity-30' }}">
                <div class="bg-primary-500 p-2 rounded">
                    <img src="{{ route('weapon-form-image', $weapon_form->id) }}" alt="{{ $weapon_form->name }}"
                        class="w-12 h-12">
                </div>
            </div>
        @endforeach

    </div>

    <div class="grid grid-cols-3 rounded">

        <div class="flex items-center justify-center text-primary-500 bg-background-900 rounded-l">
            <p>Dual Saber</p>
        </div>
        <div class="flex items-center justify-center text-primary-500 bg-background-900">
            <p>Long Saber</p>
        </div>
        <div class="flex items-center justify-center text-primary-500 bg-background-900 rounded-r">
            <p>Saberstaff</p>
        </div>

        @foreach ($weapon_forms_cycle2 as $weapon_form)
            <div class="my-2 mx-2  {{ in_array($weapon_form->id, $user_forms) ? '' : 'opacity-30' }}">
                <div
                    class="{{ strpos($weapon_form->name, '6') !== false || strpos($weapon_form->name, '7') !== false ? 'bg-background-700' : 'bg-primary-500' }}  p-2 rounded w-full flex items-center justify-center ">
                    <img src="{{ route('weapon-form-image', $weapon_form->id) }}" alt="{{ $weapon_form->name }}"
                        class="w-16 h-16">

                </div>
            </div>
        @endforeach

    </div>

</section>
