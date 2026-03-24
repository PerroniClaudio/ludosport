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
                                    {{ $academy->mainRector?->name }} {{ $academy->mainRector?->surname }}
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

                    <div x-data="{
                        googleMapsAccepted: false,
                        googleMapsReady: false,
                        
                        init() {
                            this.checkGoogleMapsAccepted();
                            window.addEventListener('storage', (e) => {
                                if (e.key === 'policyChoices') {
                                    this.checkGoogleMapsAccepted();
                                }
                            });
                            window.addEventListener('policyChoicesUpdated', (e) => {
                                this.checkGoogleMapsAccepted();
                            });
                        },
                        
                        checkGoogleMapsAccepted() {
                            const policyChoices = JSON.parse(localStorage.getItem('policyChoices') || '{}');
                            const isAccepted = policyChoices.cookie_policy?.categories?.google_api === true;
                            this.googleMapsAccepted = isAccepted;
                            this.googleMapsReady = isAccepted;
                        }
                    }" x-init="init()">
                        <!-- Map visible only if Google APIs accepted -->
                        <template x-if="googleMapsReady">
                            <div x-load x-data="googlemap('{{ $school->coordinates }}')" x-ref="eventGoogleMapContainer">
                                <x-maps-google id="eventGoogleMap" style="height: 400px"></x-maps-google>
                            </div>
                        </template>
                        
                        <!-- Message if Google APIs not accepted -->
                        <template x-if="!googleMapsAccepted">
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800/50 p-6 rounded-lg flex flex-col gap-4 items-start h-fit">
                                <div>
                                    <h3 class="text-base font-semibold text-yellow-800 dark:text-yellow-100 mb-2">
                                        {{ __('website.cookies_google_apis_required') ?? 'Google APIs Required' }}
                                    </h3>
                                    <p class="text-yellow-700 dark:text-yellow-200 text-sm">
                                        {{ __('website.cookies_google_apis_message') ?? 'To view the school location on the map, you need to enable Google APIs in your cookie preferences.' }}
                                    </p>
                                </div>
                                <button @click="typeof window.openCookiePreferences === 'function' && window.openCookiePreferences()"
                                    class="px-4 py-2 bg-primary-600 text-white text-sm font-semibold rounded hover:bg-primary-700 transition">
                                    {{ __('website.cookies_manage_preferences') ?? 'Manage Preferences' }}
                                </button>
                            </div>
                        </template>
                    </div>
                </section>
            </section>
        </section>
    </div>
</x-website-layout>
