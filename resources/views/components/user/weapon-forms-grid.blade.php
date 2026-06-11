@props([
    'user',
])

@php
    use App\Models\WeaponForm;

    $weaponForms = WeaponForm::query()->orderBy('id')->get();
    $beforeSpecificForms = $weaponForms->where('position_before_specific', true);
    $baseForms = $weaponForms->filter(fn ($weaponForm) => in_array($weaponForm->name, WeaponForm::DEFAULT_BASE_FORM_NAMES, true));
    $afterSpecificForms = $weaponForms->where('position_after_specific', true);

    $saberstaffForms = $weaponForms->filter(function ($weaponForm) {
        return in_array($weaponForm->name, WeaponForm::DEFAULT_SABERSTAFF_FORM_NAMES, true)
            || $weaponForm->position_saberstaff;
    });

    $longSaberForms = $weaponForms->filter(function ($weaponForm) {
        return in_array($weaponForm->name, WeaponForm::DEFAULT_LONG_SABER_FORM_NAMES, true)
            || $weaponForm->position_long_saber;
    });

    $dualSaberForms = $weaponForms->filter(function ($weaponForm) {
        return in_array($weaponForm->name, WeaponForm::DEFAULT_DUAL_SABER_FORM_NAMES, true)
            || $weaponForm->position_dual_saber;
    });
@endphp

<div class="grid grid-cols-3 gap-2 mb-2">
    <div class="flex flex-col items-center justify-center">
        <img src="{{ route('weapon-image', [
            'weapon' => 'saberstaff',
        ]) }}"
            alt="saberstaff" class="w-12 h-12 invert">
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
            alt="dual saber" class="w-12 h-12 invert">
    </div>
</div>

@if ($baseForms->isNotEmpty())
    <div class="grid grid-cols-1 gap-2 mb-2">
        @foreach ($baseForms as $weaponForm)
            <div class="flex items-center justify-center">
                <img src="{{ route('weapon-form-image-user', [
                    'weapon' => $weaponForm->id,
                    'user' => $user->id,
                ]) }}"
                    alt="{{ $weaponForm->name }}" class="w-8 h-8">
            </div>
        @endforeach
    </div>
@endif

@if ($beforeSpecificForms->isNotEmpty())
    <div class="grid grid-cols-1 gap-2 mb-2">
        @foreach ($beforeSpecificForms as $weaponForm)
            <div class="flex items-center justify-center">
                <img src="{{ route('weapon-form-image-user', [
                    'weapon' => $weaponForm->id,
                    'user' => $user->id,
                ]) }}"
                    alt="{{ $weaponForm->name }}" class="w-8 h-8">
            </div>
        @endforeach
    </div>
@endif

<div class="grid grid-cols-3 rounded gap-2 mb-2">
    <div class="flex flex-col gap-2">
        @foreach ($saberstaffForms as $weaponForm)
            <div class="flex items-center justify-center">
                <img src="{{ route('weapon-form-image-user', [
                    'weapon' => $weaponForm->id,
                    'user' => $user->id,
                ]) }}"
                    alt="{{ $weaponForm->name }}" class="w-8 h-8">
            </div>
        @endforeach
    </div>
    <div class="flex flex-col gap-2">
        @foreach ($longSaberForms as $weaponForm)
            <div class="flex items-center justify-center">
                <img src="{{ route('weapon-form-image-user', [
                    'weapon' => $weaponForm->id,
                    'user' => $user->id,
                ]) }}"
                    alt="{{ $weaponForm->name }}" class="w-8 h-8">
            </div>
        @endforeach
    </div>
    <div class="flex flex-col gap-2">
        @foreach ($dualSaberForms as $weaponForm)
            <div class="flex items-center justify-center">
                <img src="{{ route('weapon-form-image-user', [
                    'weapon' => $weaponForm->id,
                    'user' => $user->id,
                ]) }}"
                    alt="{{ $weaponForm->name }}" class="w-8 h-8">
            </div>
        @endforeach
    </div>
</div>

@if ($afterSpecificForms->isNotEmpty())
    <div class="grid grid-cols-1 gap-2 mb-2">
        @foreach ($afterSpecificForms as $weaponForm)
            <div class="flex items-center justify-center">
                <img src="{{ route('weapon-form-image-user', [
                    'weapon' => $weaponForm->id,
                    'user' => $user->id,
                ]) }}"
                    alt="{{ $weaponForm->name }}" class="w-8 h-8">
            </div>
        @endforeach
    </div>
@endif
