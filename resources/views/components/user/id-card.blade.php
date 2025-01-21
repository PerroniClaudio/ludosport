@props([
    'user' => null,
])

<section class="w-full bg-background-900  border-8 border-primary-500 lg:w-1/2">
    <div class="flex flex-col flex-[1_1_0]">
        <div class="flex bg-white">
            <div class="w-[45%] p-2">
                <div class="flex items-center gap-1">
                    <div class="flex-[1_1_0]">
                        <img class="w-12" src="{{ route('nation-flag', $user->nation->id) }}"
                            alt="{{ $user->nation->flag }}">
                    </div>
                    <div class="flex-[2_1_0]">
                        <p class="text-primary-500 uppercase font-bold text-2xl">{{ $user->nation->name }}</p>
                    </div>
                </div>
                <p class="font-bold text-3xl">{{ $user->name }} {{ $user->surname }}</p>
                <p class="font-bold text-3xl">
                    {{ __('users.since') }} <span class="text-primary-500">{{ $user->subscription_year }}</span>
                </p>
            </div>
            <div class="w-[10%] bg-primary-500">

            </div>
            <div class="flex flex-row justify-end w-[45%] p-2">
                @if ($user->primaryAcademyAthlete())
                    <img class="w-24 aspect-square" src="/academy-image/{{ $user->primaryAcademyAthlete()->id }}"
                        alt="{{ $user->primaryAcademyAthlete()->name }}">
                @elseif ($user->primaryAcademy())
                    <img class="w-24 aspect-square" src="/academy-image/{{ $user->primaryAcademy()->id }}"
                        alt="{{ $user->primaryAcademy()->name }}">
                @endif
            </div>
        </div>

        <div class="flex relative h-80 bg-white">
            <div class="w-[45%]"></div>
            <div class="w-[10%] bg-primary-500"></div>
            <div class="w-[45%]"></div>

            <div class="flex flex-col items-center justify-center absolute inset-0">
                <div class="border-8 border-primary-500 rounded-full">
                    <div class="border-8 border-black rounded-full p-2 bg-cover bg-center bg-no-repeat w-72 h-72"
                        style="background-image: url('{{ route('profile-picture', $user->id) }}')">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex relative h-64  bg-white">
            <div class="w-[45%]"></div>
            <div class="w-[10%] bg-primary-500"></div>
            <div class="w-[45%]"></div>

            <div class="flex flex-col gap-8 items-center justify-between absolute inset-0 bottom-0">

                <div class="flex flex-col items-center justify-center bg-white">
                    <img src="{{ route('logoex') }}" alt="ludosport international">
                </div>

                <p class="text-center w-full text-lg mt-4 bg-white">
                    <span class="text-primary-500">ID</span>
                    {{ $user->unique_code }}
                </p>



            </div>

        </div>
    </div>
    <div class="flex flex-col flex-[1_1_0]">
        <div class="flex">
            <div class="w-[45%] p-2">
                <p class="text-primary-500 font-bold text-xl text-center">
                    {{ __('users.rank_and_forms') }}
                </p>

                <div class="flex flex-row items-center justify-center my-2">
                    <img src="{{ route('rank-image', $user->rank->id) }}" alt="rank"
                        class="rounded-full h-8 w-8" />
                </div>

                @php
                    use App\Models\WeaponForm;
                    $weapon_forms = WeaponForm::all();

                    $weapon_forms_cycle2 = $weapon_forms->skip(3);
                    $weapon_forms_cycle2 = $weapon_forms_cycle2->sortBy('name');
                @endphp


                <div class="grid grid-cols-3 gap-2 mb-2">

                    @foreach ($weapon_forms->take(3) as $weapon_form)
                        <div class="flex items-center justify-center col-span-3  ">

                            <img src="{{ route('weapon-form-image-user', [
                                'weapon' => $weapon_form->id,
                                'user' => $user->id,
                            ]) }}"
                                alt="{{ $weapon_form->name }}" class="w-8 h-8" style="">

                        </div>
                    @endforeach

                </div>

                <div class="grid grid-cols-3 rounded gap-2 mb-2">

                    @foreach ($weapon_forms_cycle2 as $weapon_form)
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
            <div class="w-[10%] bg-primary-500"></div>
            <div class="w-[45%] p-2 flex flex-col  gap-4">
                <div>
                    <p class="text-primary-500 font-bold text-xl text-center">
                        {{ __('users.battle_name') }}
                    </p>
                    <p class="font-bold text-xl text-center text-white">
                        {{ $user->battle_name }}
                    </p>
                </div>

                <div>
                    <p class="text-primary-500 font-bold text-xl text-center">
                        {{ __('users.status') }}
                    </p>
                    @if ($user->has_paid_fee)
                        <div class="flex items-center justify-center gap-1">
                            <x-lucide-circle class="h-6 w-6 text-green-500" /> <span
                                class="text-white font-bold text-xl">{{ __('users.active') }}</span>
                        </div>
                    @endif
                    @if (!$user->has_paid_fee)
                        <div class="flex items-center justify-center gap-1">
                            <x-lucide-circle class="h-6 w-6 text-red-500" /> <span
                                class="text-white font-bold text-xl">{{ __('users.inactive') }}</span>
                        </div>
                    @endif
                </div>

                <div>
                    <p class="text-primary-500 font-bold text-xl text-center">
                        {{ __('users.school') }}
                    </p>

                    <p class="font-bold text-xl text-center">
                        @if ($user->primarySchool())
                            <span class="text-white">{{ $user->primarySchool()->name }}</span>
                        @elseif ($user->primarySchoolAthlete())
                            <span class="text-white">{{ $user->primarySchoolAthlete()->name }}</span>
                        @else
                            <span class="text-white">{{ __('users.no_school') }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
