<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 lg:px-48 pb-16  container mx-auto max-w-7xl">
        <section class="hidden lg:block col-span-12 py-12 md:py-24 lg:py-32 xl:py-48">

            <div class="grid gap-6 items-center">
                <div class="flex flex-col justify-center space-y-4 text-center">
                    <div class="space-y-2">
                        <h5 class="max-w-[600px] md:text-xl text-background-800 dark:text-background-200 mx-auto">
                            {{ __('website.hero_small') }}
                        </h5>
                        <h1
                            class="text-6xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                            {{ __('website.hero_title') }}
                        </h1>
                    </div>
                    <a href="{{ route('register') }}">
                        <button
                            class="bg-primary-500 hover:bg-primary-600 text-white font-bold py-4 px-16 rounded mx-auto text-3xl">
                            {{ __('website.hero_cta') }}
                        </button>
                    </a>
                </div>
            </div>

        </section>
        <section class="flex lg:hidden col-span-12 h-screen  flex-col items-center justify-center">

            <div class="text-center flex flex-col gap-8">
                <div class="space-y-2">
                    <h5 class="max-w-[600px] md:text-xl text-background-800 dark:text-background-200 mx-auto">
                        {{ __('website.hero_small') }}
                    </h5>
                    <h1
                        class="text-7xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                        {{ __('website.hero_title') }}
                    </h1>
                </div>
                <a href="{{ route('register') }}">
                    <x-primary-button>
                        {{ __('website.hero_cta') }}
                    </x-primary-button>
                </a>

                <a href="#section1"
                    onclick="event.preventDefault(); document.getElementById('section1').scrollIntoView({ behavior: 'smooth' });">
                    <x-lucide-chevron-down class="w-16 h-16 text-primary-500 mx-auto animate-bounce" />
                </a>

            </div>

        </section>

        <div class="col-span-12 grid grid-cols-12 py-4 lg:py-16" id="section1">

            <section class="col-span-12 lg:col-span-6 flex flex-col justify-center gap-2">
                <span
                    class="text-primary-500 font-semibold text-center text-sm lg:text-lg lg:text-left">{{ __('website.first_section_small') }}</span>
                <h1
                    class="text-2xl text-center lg:text-left md:text-3xl font-bold text-background-800 dark:text-background-200">
                    {{ __('website.first_section_title') }}</h1>
                <p class="text-background-800 dark:text-background-200 text-justify">
                    {{ __('website.first_section_description') }}</p>
            </section>
            <section class="hidden lg:flex col-span-6 flex-col items-center justify-center">
                <div class="relative w-full h-full">
                    <div class="absolute top-8 right-8 w-52 h-52 bg-primary-500 rounded"></div>
                    <img src="https://picsum.photos/200" alt="foto 1"
                        class="w-52 rounded aspect-square absolute top-12 right-12">
                </div>
            </section>

        </div>

        <div class="col-span-12 grid grid-cols-12 py-4 lg:py-16">

            <section class="hidden lg:flex col-span-6 flex-col items-center justify-center">
                <div class="relative w-full h-full">
                    <div class="absolute top-8 left-8 w-52 h-52 bg-primary-500 rounded"></div>
                    <img src="https://picsum.photos/200" alt="foto 1"
                        class="w-52 rounded aspect-square absolute top-12 left-12">
                </div>
            </section>
            <section class="col-span-12 lg:col-span-6 flex flex-col justify-center gap-2">
                <span
                    class="text-primary-500 font-semibold text-center text-sm lg:text-lg lg:text-right">{{ __('website.second_section_small') }}</span>
                <h1
                    class="text-2xl text-center lg:text-right md:text-3xl font-bold text-background-800 dark:text-background-200">
                    {{ __('website.second_section_title') }}</h1>
                <p class="text-background-800 dark:text-background-200 text-justify">
                    {{ __('website.second_section_description') }}</p>
            </section>


        </div>

        <div class="col-span-12">
            <h1
                class="text-center text-4xl font-bold tracking-tighter pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                {{ __('website.third_section_title') }}
            </h1>

            <div class="grid lg:grid-cols-3 gap-4 mt-8">
                <div
                    class="text-background-800 dark:text-background-200 p-4 bg-primary-400 dark:bg-background-800 rounded-lg flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <x-lucide-handshake class="w-6 h-6 dark:text-primary-500" />
                        <h4 class="font-semibold text-2xl">{{ __('website.tird_section_first_title') }}</h4>
                    </div>
                    <p>{{ __('website.third_section_description_fp') }}</p>
                </div>
                <div
                    class="text-background-800 dark:text-background-200 p-4 bg-primary-400 dark:bg-background-800 rounded-lg flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <x-lucide-scale class="w-6 h-6 dark:text-primary-500" />
                        <h4 class="font-semibold text-2xl">{{ __('website.tird_section_second_title') }}</h4>
                    </div>
                    <p>{{ __('website.third_section_description_sp') }}</p>
                </div>
                <div
                    class="text-background-800 dark:text-background-200 p-4 bg-primary-400 dark:bg-background-800 rounded-lg flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <x-lucide-users class="w-6 h-6 dark:text-primary-500" />
                        <h4 class="font-semibold text-2xl">{{ __('website.tird_section_third_title') }}</h4>
                    </div>
                    <p>{{ __('website.third_section_description_tp') }}</p>
                </div>
            </div>

        </div>
    </div>
</x-website-layout>
