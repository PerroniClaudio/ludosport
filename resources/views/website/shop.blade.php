<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12 flex flex-col gap-8">
            <h1
                class="text-6xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                {{ __('website.shop_title') }}
            </h1>

            <div class="grid lg:grid-cols-2 gap-8 text-background-800 dark:text-background-200 text-center">
                <section class="flex flex-col gap-4 bg-background-200 dark:bg-background-800 p-8 rounded-md col-span-2">
                    <h3
                        class="text-4xl font-bold tracking-tighter sm:text-3xl xl:text-4xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300 text-center">
                        {{ __('website.shop_membership_activation') }}
                    </h3>

                    <p class="text-background-800 dark:text-background-200 text-center">
                        {{ __('website.shop_membership_activation_text') }}</p>

                    <div class="grid grid-cols-2 gap-4">
                        <div
                            class="border border-background-700 p-4 rounded-md flex flex-col items-center justify-center">
                            {{ __('website.shop_membership_activation_1') }}
                        </div>
                        <div
                            class="border border-background-700 p-4 rounded-md flex flex-col items-center justify-center">
                            {{ __('website.shop_membership_activation_2') }}
                        </div>
                    </div>


                    <div class="grid grid-cols-3 gap-4">
                        <div
                            class="border border-background-700 p-4 rounded-md flex flex-col items-center justify-center">
                            {{ __('website.shop_membership_activation_3') }}
                        </div>
                        <div
                            class="border border-background-700 p-4 rounded-md flex flex-col items-center justify-center">
                            {{ __('website.shop_membership_activation_4') }}
                        </div>
                        <div
                            class="border border-background-700 p-4 rounded-md flex flex-col items-center justify-center">
                            {{ __('website.shop_membership_activation_5') }}
                        </div>
                    </div>

                    <div class="flex items-center justify-center">
                        <a href="{{ route('shop-activate-membership') }}">
                            <x-primary-button>
                                {{ __('website.shop_membership_cta') }}
                            </x-primary-button>
                        </a>
                    </div>

                </section>

                <section class="flex flex-col gap-4 bg-background-200 dark:bg-background-800 p-8 rounded-md">
                    <h3
                        class="text-4xl font-bold tracking-tighter sm:text-3xl xl:text-4xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                        {{ __('website.shop_events') }}
                    </h3>

                    <div class="flex-1">
                        <p class="text-background-800 dark:text-background-200 text-justify">
                            {{ __('website.shop_events_text') }}</p>
                        <ul class="text-background-800 dark:text-background-200 text-justify">
                            <li>Scuola Internazionale Superiore (SIS), the Summer Camp where LudoSport Masters train new
                                Instructors</li>
                            <li>LudoSport International Champions’Arena</li>
                            <li>Instructor Courses at LudoSport International HQs in Milan, Italy</li>
                            <li>Special International Events (Vacations, Team Tournaments, etc.)</li>
                        </ul>
                    </div>

                    <div>
                        <a href="{{ route('register') }}">
                            <x-primary-button>
                                {{ __('navigation.register') }}
                            </x-primary-button>
                        </a>
                    </div>
                </section>

                <section class="flex flex-col gap-4 bg-background-200 dark:bg-background-800 p-8 rounded-md">
                    <h3
                        class="text-4xl font-bold tracking-tighter sm:text-3xl xl:text-4xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                        {{ __('website.shop_digital_resources') }}
                    </h3>

                    <div class="flex-1">
                        <p class="text-background-800 dark:text-background-200 text-justify">
                            {{ __('website.shop_digital_resources_text') }}</p>
                        <p class="text-background-800 dark:text-background-200 text-justify">
                            {{ __('website.shop_digital_resources_text_2') }}</p>
                    </div>

                    <div>
                        <x-primary-button>
                            {{ __('website.shop_digital_resources_cta') }}
                        </x-primary-button>
                    </div>
                </section>

            </div>

        </section>
    </div>
</x-website-layout>
