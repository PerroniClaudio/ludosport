@props([
    'nationality' => auth()->user()->academies->first()->nation_id ?? '',
    'selectedAcademyId' => auth()->user()->academies->first()->id ??'',
    'selectedAcademy' => auth()->user()->academies->first()->name ??'',
    'nations' => [auth()->user()->academies->first()->nation],
    'academies' => [auth()->user()->academies->first()],
])
@php
    $authRole = auth()->user()->getRole();
    $addToRoute = $authRole === 'admin' ? '' : '/' . $authRole;
    $academy = auth()->user()->academies->first();
    $nation = $academy->nation;
@endphp
<div x-data="{
    selectedNationality: '{{ $nationality }}',
    selectedNation: '{{ $nation->name }}',
    selectedAcademyId: '{{ $selectedAcademyId }}',
    selectedAcademy: '{{ $selectedAcademy ? $selectedAcademy : 'Select an academy' }}',
    academies: {{ collect($academies) }},

}">

    <div class="w-full flex flex-col gap-2">
        <div id="nationality-container">
            <x-input-label for="nationality" value="{{ __('users.nationality') }}" />
            <div class="flex w-full gap-2">
                <input type="hidden" name="nationality" x-model="selectedNationality">
                <x-text-input disabled name="nationality_name" class="flex-1" type="text" x-model="selectedNation" />
            </div>
        </div>

        <div id="academy-container">
            <x-input-label for="academy" value="{{ __('users.academy') }}" />
            <div class="flex w-full gap-2">
                <input type="hidden" name="academy_id" x-model="selectedAcademyId">
                <x-text-input disabled name="academy" class="flex-1" type="text" x-model="selectedAcademy" />
            </div>
            <x-input-error :messages="$errors->get('academy_id')" class="mt-2" />
        </div>

        <div>
            <p>
                <x-input-error :messages="$errors->get('academy_id')" />
                <x-input-error :messages="$errors->get('nationality')" />
            </p>
        </div>
    </div>

</div>
