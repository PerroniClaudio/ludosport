@props([
    'user' => null,
])

<section class="w-full dark:bg-background-600 border-8 border-primary-500 text-background-800 dark:text-white lg:w-1/2">
    <div class="flex">
        <div class="w-[45%] p-2">
            <div class="flex items-center gap-1">
                <div class="flex-[1_1_0]">
                    <img class="w-12" src="{{ route('nation-flag', $user->nation->id) }}"
                        alt="{{ $user->nation->flag }}">
                </div>
                <div class="flex-[2_1_0]">
                    <p class="text-primary-500 uppercase font-bold text-xl">{{ $user->nation->name }}</p>
                </div>
            </div>
            <p class="font-bold text-xl">{{ $user->name }} {{ $user->surname }}</p>
            <p class="font-bold text-xl">
                {{ __('users.since') }} <span class="text-primary-500">{{ $user->subscription_year }}</span>
            </p>
        </div>
        <div class="w-[10%] bg-primary-500">

        </div>
        <div class="flex flex-row justify-end w-[45%] p-2">
            @if ($user->primaryAcademyAthlete())
                <img class="w-24 aspect-square" src="/academy-image/{{ $user->primaryAcademyAthlete()->id }}"
                    alt="{{ $user->primaryAcademyAthlete()->name }}">
            @endif
        </div>
    </div>

    <div class="flex relative h-64">
        <div class="w-[45%]"></div>
        <div class="w-[10%] bg-primary-500"></div>
        <div class="w-[45%]"></div>

        <div class="flex flex-col items-center justify-center absolute inset-0">
            <div class="border-8 border-primary-500 rounded-full">
                <div class="border-8 border-black rounded-full p-2 bg-cover bg-center bg-no-repeat w-52 h-52"
                    style="background-image: url('{{ route('profile-picture', $user->id) }}')">
                </div>
            </div>
        </div>
    </div>

    <div class="flex relative h-64">
        <div class="w-[45%]"></div>
        <div class="w-[10%] bg-primary-500"></div>
        <div class="w-[45%]"></div>

        <div class="flex flex-col gap-8 items-center justify-center absolute inset-0">

            <div class="flex flex-col items-center justify-center dark:bg-background-600 bg-white">
                <img src="{{ route('logoex') }}" alt="ludosport international">
            </div>

            <p class="text-center w-full text-lg mt-4 dark:bg-background-600 bg-white">
                <span class="text-primary-500">ID</span>
                {{ $user->unique_code }}
            </p>

        </div>

    </div>

    <div class="flex">
        <div class="w-[45%] p-2">
            @php
                use App\Models\WeaponForm;
                $weapon_forms = WeaponForm::all();

                $weapon_forms_cycle2 = $weapon_forms->skip(3);
                $weapon_forms_cycle2 = $weapon_forms_cycle2->sortBy('name');

                $user_forms = $user->weaponForms->pluck('id')->toArray();
            @endphp

            <p class="text-primary-500 font-bold text-xl text-center">
                {{ __('users.rank_and_forms') }}
            </p>

            <div class="grid grid-cols-3 gap-2 mb-2">

                @foreach ($weapon_forms->take(3) as $weapon_form)
                    <div
                        class="flex items-center justify-center col-span-3  {{ in_array($weapon_form->id, $user_forms) ? '' : 'opacity-30' }}">

                        <img src="{{ route('weapon-form-image', $weapon_form->id) }}" alt="{{ $weapon_form->name }}"
                            class="w-8 h-8 invert" style="">

                    </div>
                @endforeach

            </div>

            <div class="grid grid-cols-3 rounded">


                @foreach ($weapon_forms_cycle2 as $weapon_form)
                    <div
                        class="my-2 mx-2  {{ in_array($weapon_form->id, $user_forms) ? '' : 'opacity-30' }} flex flex-col items-center justify-center">
                        <div
                            class="{{ strpos($weapon_form->name, '6') !== false || strpos($weapon_form->name, '7') !== false ? 'hidden' : '' }}">
                            <img src="{{ route('weapon-form-image', $weapon_form->id) }}"
                                alt="{{ $weapon_form->name }}" class="w-8 h-8 invert">

                        </div>
                    </div>
                @endforeach

            </div>

        </div>
        <div class="w-[10%] bg-primary-500"></div>
        <div class="w-[45%] p-2 flex flex-col gap-4">
            <p class="text-primary-500 font-bold text-xl text-center">
                {{ __('users.battle_name') }}
            </p>
            <p class="text-white font-bold text-xl text-center">
                {{ $user->battle_name }}
            </p>

            <p class="text-primary-500 font-bold text-xl text-center">
                {{ __('users.status') }}
            </p>

            @if ($user->has_paid_fee)
                <div class="flex items-center justify-center gap-1">
                    <x-lucide-circle class="h-6 w-6 text-green-500" /> <span>{{ __('users.active') }}</span>
                </div>
            @endif

            @if (!$user->has_paid_fee)
                <div class="flex items-center justify-center gap-1">
                    <x-lucide-circle class="h-6 w-6 text-red-500" /> <span>{{ __('users.inactive') }}</span>
                </div>
            @endif

            <p class="text-primary-500 font-bold text-xl text-center">
                {{ __('users.school') }}
            </p>

            <p class="font-bold text-xl text-center">
                @if ($user->primarySchool())
                    <span class="text-white">{{ $user->primarySchool()->name }}</span>
                @endif
            </p>
        </div>
    </div>

</section>
