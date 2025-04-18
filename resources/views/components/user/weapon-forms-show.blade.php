@props([
    'forms' => [],
    'user' => [],
])


<section
    class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 text-background-800 dark:text-background-200">
    <h3 class="text-2xl">{{ __('navigation.weapon_forms') }}</h3>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

    <div class="bg-background-800 py-4 rounded-lg">

        <div class="grid grid-cols-3 gap-2 mb-2">
            <div class="flex flex-col items-center justify-center">
                <img src="{{ route('weapon-image', [
                    'weapon' => 'saberstaff',
                ]) }}"
                    alt="long saber" class="w-12 h-12 invert">
            </div>
            <div class="flex flex-col items-center justify-center">
                <img src="{{ route('weapon-image', [
                    'weapon' => 'longsaber',
                ]) }}"
                    alt="long saber" class="w-12 h-12 invert">
            </div>
            <div class="flex flex-col items-center justify-center">
                <img src="{{ route('weapon-image', [
                    'weapon' => 'dualsaber',
                ]) }}"
                    alt="long saber" class="w-12 h-12 invert">
            </div>
    
        </div>
    
        @php
            use App\Models\WeaponForm;
            $weapon_forms = WeaponForm::all();
    
            $weapon_forms_cycle2 = $weapon_forms->skip(3);
            $weapon_forms_cycle2 = $weapon_forms_cycle2->sortBy('name');
    
            $long_saber_weapon_forms = [];
            $saberstaff_weapon_forms = [];
            $dual_saber_weapon_forms = [];
    
            foreach ($weapon_forms_cycle2 as $weapon_form) {
                if (strpos($weapon_form->name, 'Long Saber') !== false) {
                    $long_saber_weapon_forms[] = $weapon_form;
                }
    
                if (strpos($weapon_form->name, 'Saberstaff') !== false) {
                    $saberstaff_weapon_forms[] = $weapon_form;
                }
    
                if (strpos($weapon_form->name, 'Dual Saber') !== false) {
                    $dual_saber_weapon_forms[] = $weapon_form;
                }
            }
    
        @endphp
    
    
        <div class="grid grid-cols-3 gap-2 mb-2">
    
            @foreach ($weapon_forms->take(3) as $weapon_form)
                <div></div>
                 <div class="flex items-center justify-center">
    
                    <img src="{{ route('weapon-form-image-user', [
                        'weapon' => $weapon_form->id,
                        'user' => $user->id,
                    ]) }}"
                        alt="{{ $weapon_form->name }}" class="w-8 h-8" style="">
    
                </div>
                <div></div>
            @endforeach
    
        </div>
    
        <div class="grid grid-cols-3 rounded gap-2 mb-2">
           
            <div class="flex flex-col gap-2">
                @foreach ($saberstaff_weapon_forms as $weapon_form)
                    <div class="flex items-center justify-center ">
                        <img src="{{ route('weapon-form-image-user', [
                            'weapon' => $weapon_form->id,
                            'user' => $user->id,
                        ]) }}"
                            alt="{{ $weapon_form->name }}" class="w-8 h-8" style="">
                    </div>
                @endforeach
            </div>
             <div class="flex flex-col gap-2">
                @foreach ($long_saber_weapon_forms as $weapon_form)
                    <div class="flex items-center justify-center ">
                        <img src="{{ route('weapon-form-image-user', [
                            'weapon' => $weapon_form->id,
                            'user' => $user->id,
                        ]) }}"
                            alt="{{ $weapon_form->name }}" class="w-8 h-8" style="">
                    </div>
                @endforeach
            </div>
            <div class="flex flex-col gap-2">
                @foreach ($dual_saber_weapon_forms as $weapon_form)
                    <div class="flex items-center justify-center ">
                        <img src="{{ route('weapon-form-image-user', [
                            'weapon' => $weapon_form->id,
                            'user' => $user->id,
                        ]) }}"
                            alt="{{ $weapon_form->name }}" class="w-8 h-8" style="">
                    </div>
                @endforeach
            </div>

        </div>

    </div>

</section>
