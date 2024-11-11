@props([
    'user' => null,
])

<section class="w-full bg-background-900 border-8 border-primary-500 flex flex-col p-4 gap-4">
    <div class="grid grid-cols-4 items-center w-full  gap-4">
        <div class="flex items-center gap-1">
            <div class="flex-[2_1_0]">
                <p class="lg:text-6xl text-primary-500 uppercase font-bold">{{ $user->nation->name }}</p>
            </div>
            <div class="flex-[1_1_0]">
                <img src="{{ route('nation-flag', $user->nation->id) }}" alt="{{ $user->nation->flag }}">
            </div>
        </div>
        <div class="col-span-2 flex flex-col">
            <p class="text-white font-bold lg:text-3xl uppercase">{{ $user->name }} {{ $user->surname }}</p>
            <p class="text-primary-500 font-bold lg:text-3xl">
                {{ __('users.since_to', [
                    'since' => $user->subscription_year,
                    'to' => now()->format('Y'),
                ]) }}
            </p>
        </div>
        <div class="flex justify-end">
            <p class="text-xs lg:text-lg">
                <span class="text-primary-500">
                    {{ __('users.uninque_id_card') }}
                </span>
                <span class="text-white">
                    {{ $user->unique_code }}
                </span>
            </p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <p class="text-white text-3xl">{{ $user->battle_name }}</p>

        @if ($user->has_paid_fee)
            <x-lucide-circle class="h-6 w-6 text-green-500" />
        @endif
    </div>
    <div>
        <p class="text-white font-bold">LudoSport</p>
        <p class="font-bold">
            <span class="text-primary-500 ">{{ __('academies.academy') }}:</span>
            @if ($user->primaryAcademyAthlete())
                <span class="text-white">{{ $user->primaryAcademyAthlete()->name }}</span>
            @endif
        </p>
    </div>
</section>
