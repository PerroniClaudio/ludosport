<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12 flex flex-col gap-8">
            <section class="bg-white dark:bg-background-800 flex gap-8 flex-col sm:flex-row p-8 rounded">
                <div class="rounded-full h-24 w-24 shrink-0">
                    <img src="{{ route('academy-image', $school->academy->id) }}" alt="avatar"
                        class="rounded-full h-24 w-24" />
                </div>
                <div class="flex-1 flex flex-col gap-2">
                    <div class="flex flex-col gap-2">
                        <div class="text-3xl sm:text-4xl text-primary-500">{{ $school->name }}</div>
                        <div class="flex items-center gap-2">
                            <x-lucide-flag class="h-5 w-5 text-background-500 dark:text-background-400 shrink-0" />
                            <span class="text-sm text-background-500 dark:text-background-400">
                                {{ $school->nation->name }}
                            </span>
                            <img src="{{ route('nation-flag', $school->nation->id) }}" alt="{{ $school->nation->name }}"
                                class="h-2 w-4">
                        </div>
                        <div class="flex items-center gap-2">
                            <x-lucide-circle-user-round
                                class="h-5 w-5 text-background-500 dark:text-background-400 shrink-0" />
                            <span class="text-sm text-background-500 dark:text-background-400">
                                {{ __('users.dean') }}: {{ $dean }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-lucide-swords class="h-5 w-5 text-background-500 dark:text-background-400 shrink-0" />
                            <a href="{{ route('academy-profile', $academy->slug) }}">
                                <span
                                    class="text-sm text-background-500 dark:text-background-400 hover:text-primary-500">
                                    {{ $academy->name }}
                                </span>
                            </a>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-lucide-circle-user-round
                                class="h-5 w-5 text-background-500 dark:text-background-400 shrink-0" />
                            <span class="text-sm text-background-500 dark:text-background-400">
                                {{ __('users.rector') }}:


                                <span
                                    class="text-sm text-background-500 dark:text-background-400 hover:text-primary-500">
                                    {{ $academy->mainRector->name }} {{ $academy->mainRector->surname }}
                                </span>



                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-lucide-mail class="h-5 w-5 text-background-500 dark:text-background-400 shrink-0" />
                            <span class="text-sm text-background-500 dark:text-background-400">
                                {{ __('school.school_email') }}:
                                <a href="mailto:{{ $school->email }}"
                                    class="text-primary-500">{{ $school->email }}</a>
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <section
                    class="bg-white dark:bg-background-800 text-background-800 dark:text-background-200 flex flex-col p-8 rounded order-2 lg:order-1">
                    <h4 class="text-2xl">{{ __('website.school_detail_users') }}</h4>

                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    <x-table striped="false" :columns="[
                        [
                            'name' => 'Name',
                            'field' => 'name',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Surname',
                            'field' => 'surname',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Battle Name',
                            'field' => 'battle_name',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                    ]" :rows="$school->athletes">

                    </x-table>


                </section>
                <section
                    class="bg-white dark:bg-background-800 text-background-800 dark:text-background-200 flex flex-col gap-4 p-8 rounded order-1 lg:order-2">
                    <h4 class="text-2xl">{{ __('website.school_detail_location') }}</h4>

                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    <div class="flex items-center gap-2">
                        <x-lucide-map-pin class="w-10 h-10 text-primary-500 dark:text-primary-600" />
                        <span class="dark:text-background-200">{{ $school->address }},
                            {{ $school->postal_code }}
                            {{ $school->city }}, {{ $school->nation->name }}</span>
                    </div>

                    <div x-load x-data="googlemap('{{ $school->coordinates }}')" x-ref="eventGoogleMapContainer">
                        <x-maps-google id="eventGoogleMap" style="height: 400px"></x-maps-google>
                    </div>
                </section>
            </section>
        </section>
    </div>
</x-website-layout>
