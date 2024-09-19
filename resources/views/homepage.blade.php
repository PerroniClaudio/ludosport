<x-website-layout>

    <div class="flex">
        <div id="bigone" class="relative">
            <div class="hidden lg:block absolute" style="top: 38%; right: -2%">
                <img src="{{ route('bollino') }}" alt="">
            </div>
            <div class="flex flex-col items-center justify-center h-full lg:max-w-[50vw]">
                <img src="{{ url('warriors') }}" alt="">
                <a href="{{ route('login') }}" class="mt-8 w-1/2 h-16">
                    <x-primary-button class="w-full h-16 flex items-center justify-center">
                        <p>{{ __('website.homepage_join') }}</p>
                    </x-primary-button>
                </a>
            </div>
        </div>
        <div id="smallone" class="hidden lg:block">

            <div class="inset-0 h-full bg-cover bg-no-repeat lg:max-w-[50vw]"
                style="background-image: url('{{ route('spada-home') }}')">
            </div>
        </div>
    </div>


</x-website-layout>
