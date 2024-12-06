<x-website-layout>

    <div class="flex">
        <div id="bigone" class="relative bg-background-400 dark:bg-background-900">
            <div class="hidden 2xl:block absolute" id="bollino">
                <img src="{{ route('bollino') }}" alt="">
            </div>
            <div class="flex flex-col items-center justify-center h-full 2xl:max-w-[50vw]">
                <div>
                    <img src="{{ url('warriors') }}" alt="" class="h-[20vh] sm:h-[30vh] md:h-[40vh]">
                </div>
                <a href="{{ route('login') }}" class="mt-8 w-1/2 h-16">
                    <x-primary-button class="w-full h-16 flex items-center justify-center">
                        <p>{{ __('website.homepage_join') }}</p>
                    </x-primary-button>
                </a>
            </div>
        </div>
        <div id="smallone" class="hidden 2xl:block bg-background-200 dark:bg-background-700">

            <div class="inset-0 h-full bg-cover bg-no-repeat lg:max-w-[50vw]"
                style="background-image: url('{{ route('spada-home') }}')">
            </div>
        </div>
    </div>


</x-website-layout>
