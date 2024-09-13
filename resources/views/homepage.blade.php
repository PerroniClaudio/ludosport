<x-website-layout>

    <div class="flex">
        <div id="bigone" class="relative">
            <div class="absolute" style="top: 38%; right: -2%">
                <img src="{{ route('bollino') }}" alt="">
            </div>
            <div class="inset-0 h-full bg-cover bg-no-repeat lg:max-w-[50vw]"
                style="background-image: url('{{ route('spada-home') }}')">
            </div>
        </div>
        <div id="smallone" class="hidden lg:block">
            <div class="flex flex-col items-center justify-center h-full">
                <img src="{{ url('warriors') }}" alt="">
            </div>
        </div>
    </div>


</x-website-layout>
