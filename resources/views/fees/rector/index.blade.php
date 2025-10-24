<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('fees.title') }}
            </h2>

        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">

            @php
                $authRole = auth()->user()->getRole();
            @endphp
            @if ($authRole === 'rector')
                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-background-900 dark:text-background-100">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('fees.purchase_fees') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <div
                                    class="border border-background-700 text-background-800 dark:text-background-200 rounded-lg p-4 cursor-pointer flex flex-col gap-2">
                                    <p>{{ __('fees.buy_new_fees') }}</p>
                                    <div class="flex justify-end ">
                                        <a href="{{ route('rector.fees.purchase') }}">
                                            <x-primary-button>
                                                <x-lucide-arrow-right class="h-6 w-6 text-white" />
                                            </x-primary-button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div
                                    class="border border-background-700 text-background-800 dark:text-background-200 rounded-lg p-4 cursor-pointer flex flex-col gap-2">
                                    {{ __('fees.renew_expired_fees') }}
                                    <div class="flex justify-end ">
                                        <a href="{{ route('rector.fees.renew') }}">
                                            <x-primary-button>
                                                <x-lucide-arrow-right class="h-6 w-6 text-white" />
                                            </x-primary-button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100" x-data="{
                    authRole: '{{ $authRole }}',
                    athletes_no_fees: {{ collect($athletes_no_fees) }},
                    athletes_add_fees: [],
                    fees_number: {{ $fees_number }},
                    paginatedAthletes: [],                    
                    currentAthletePage: 1,
                    totalAthletePages: 1,
                    feesCheckData: {},
                    searchTerm: '',
                    getFilteredAthletes: function() {
                        if (this.searchTerm.length < 3) {
                            return this.athletes_no_fees;
                        }
                        return this.athletes_no_fees.filter((athlete) => {
                            return athlete.fullname.toLowerCase().includes(this.searchTerm.toLowerCase());
                        });
                    },
                    searchavailableAthletes: function(event) {
                        this.searchTerm = event.target.value;
                        this.currentAthletePage = 1; // Reset alla prima pagina
                        this.paginateAthletes();
                    },
                    goToAthletePage: function(page) {
                        if (page < 1 || page > this.totalAthletePages) {
                            return;
                        }
                        this.currentAthletePage = page;
                        this.paginateAthletes();
                    },
                    addAthlete: function(athlete_id) {
                        let athlete = this.athletes_no_fees.find((athlete) => {
                            return athlete.id === athlete_id;
                        });
                        this.athletes_add_fees.push(athlete);
                        this.athletes_no_fees = this.athletes_no_fees.filter((athlete) => {
                            return athlete.id !== athlete_id;
                        });
                        this.paginateAthletes(); // Ricalcola la paginazione
                    },
                    removeAthlete(athlete_id) {
                        let athlete = this.athletes_add_fees.find((athlete) => {
                            return athlete.id === athlete_id;
                        });
                        this.athletes_no_fees.push(athlete);
                        this.athletes_add_fees = this.athletes_add_fees.filter((athlete) => {
                            return athlete.id !== athlete_id;
                        });
                        this.paginateAthletes(); // Ricalcola la paginazione
                    },
                    paginateAthletes: function() {
                        const filteredAthletes = this.getFilteredAthletes();
                        this.totalAthletePages = Math.ceil(filteredAthletes.length / 10);
                        
                        // Assicurati che la pagina corrente sia valida
                        if (this.currentAthletePage > this.totalAthletePages) {
                            this.currentAthletePage = Math.max(1, this.totalAthletePages);
                        }
                        
                        const startIndex = (this.currentAthletePage - 1) * 10;
                        const endIndex = startIndex + 10;
                        this.paginatedAthletes = filteredAthletes.slice(startIndex, endIndex);
                    },
                    openConfirmModal: function() {
                
                        let url = `/${this.authRole}/fees/extimate`;
                        let params = new URLSearchParams({
                            selected_users: JSON.stringify(this.athletes_add_fees.map((athlete) => {
                                return athlete.id;
                            }))
                        });
                
                        fetch(`${url}?${params}`, {
                                method: 'GET',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                            }).then(response => response.json())
                            .then(data => {
                                this.feesCheckData = data;
                
                                if ((this.feesCheckData.available_fees - this.feesCheckData.fees_consumed) >= 0) {
                                    this.$dispatch('open-modal', 'confirm-modal');
                                } else {
                                    this.$dispatch('open-modal', 'error-modal');
                                }
                
                
                            })
                            .catch((error) => {
                                console.error('Error:', error);
                            });
                    },
                    confirmAssociateFees: function() {
                
                        let url = `/${this.authRole}/fees/associate`;
                        const fd = new FormData();
                
                        const selected_users = this.athletes_add_fees.map((athlete) => {
                            return athlete.id;
                        });
                        fd.append('selected_users', JSON.stringify(selected_users));
                
                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: fd
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.is_error) {
                                    this.$dispatch('close-modal', 'confirm-modal');
                                    this.$dispatch('open-modal', 'error-modal');
                                } else {
                                    window.location.reload();
                                }
                            })
                            .catch((error) => {
                                console.error('Error:', error);
                            });
                
                    },
                    init() {
                        this.paginateAthletes();
                    }
                }">
                    <div class="flex gap-2 items-center">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">
                            {{ __('fees.associate_fees') }}

                        </h3>
                        @if ($authRole === 'manager')
                            <div class='has-tooltip'>
                                <span
                                    class='tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full'>
                                    {{ __('fees.fees_purchase_info') }}
                                </span>
                                <x-lucide-info class="h-4 text-background-400" />
                            </div>
                        @endif
                    </div>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="p-4 bg-background-100 dark:bg-background-700 rounded-lg">
                            <h4 class="text-background-800 dark:text-background-200 text-lg">
                                {{ __('fees.available_fees') }}</h4>
                            <p class="text-primary-600 dark:text-primary-500 text-3xl" x-text="fees_number">

                            </p>
                        </div>

                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-white dark:bg-background-900 p-4 rounded">
                            <div class="flex justify-between gap-2 items-center">
                                <div class="flex-1">
                                    <h4 class="text-background-800 dark:text-background-200 text-lg">
                                        {{ __('fees.users_no_fees') }}
                                    </h4>
                                </div>
                                <div>
                                    <x-text-input type="text" x-on:input="searchavailableAthletes(event);"
                                        placeholder="Search..."
                                        class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                                </div>
                            </div>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                            <table
                                class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">
                                <thead>
                                    <tr class="">
                                        <th
                                            class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                            {{ __('users.name') }}</th>
                                        <th
                                            class="text-right bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                            {{ __('users.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(row, index) in paginatedAthletes">
                                        <tr>
                                            <td class="text-background-500 dark:text-background-300 text-sm"
                                                x-text="row.fullname"></td>
                                            <td
                                                class="text-background-500 dark:text-background-300 text-sm text-right p-1">
                                                <button type="button" @click="addAthlete(row.id)">
                                                    <x-lucide-plus
                                                        class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:text-primary-700" />
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>


                            <div class="flex items-center">
                                <div class="flex-1">

                                </div>
                                <div class="flex justify-between items-center">
                                    <button type="button" x-on:click="goToAthletePage(1)" class="mr-2"
                                        x-bind:disabled="currentAthletePage === 1">
                                        <x-lucide-chevron-first
                                            class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                    </button>
                                    <button type="button" x-on:click="goToAthletePage(currentAthletePage - 1)"
                                        class="mr-2" x-bind:disabled="currentAthletePage === 1"
                                        :class="{ 'opacity-50 cursor-not-allowed': currentAthletePage === 1 }">
                                        <x-lucide-chevron-left class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                    </button>
                                    <p class="text-sm text-background-500 dark:text-background-300">Page <span
                                            x-text="currentAthletePage"></span> of
                                        <span x-text="totalAthletePages"></span>
                                    </p>
                                    <button type="button" x-on:click="goToAthletePage(currentAthletePage + 1)"
                                        class="ml-2" x-bind:disabled="currentAthletePage === totalAthletePages"
                                        :class="{
                                            'opacity-50 cursor-not-allowed': currentAthletePage === totalAthletePages
                                        }">
                                        <x-lucide-chevron-right
                                            class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                    </button>
                                    <button type="button" x-on:click="goToAthletePage(totalAthletePages)"
                                        class="ml-2" x-bind:disabled="currentAthletePage === totalAthletePages">
                                        <x-lucide-chevron-last class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                    </button>
                                </div>
                            </div>

                        </div>

                        <div class="bg-white dark:bg-background-900 p-4 rounded">
                            <div class="flex justify-between gap-2 items-center">
                                <div class="flex-1">
                                    <h4 class="text-background-800 dark:text-background-200 text-lg">
                                        {{ __('fees.selected_users') }}
                                    </h4>

                                </div>

                                <div>
                                    <x-text-input type="text" 
                                        {{-- x-on:input="searchavailableAcademies(event);" --}}
                                        placeholder="Search..."
                                        class="invisible border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                                </div>

                            </div>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                            <table
                                class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">
                                <thead>
                                    <tr class="">
                                        <th
                                            class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                            {{ __('clan.name') }}</th>
                                        <th
                                            class="text-right bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                            {{ __('users.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(row, index) in athletes_add_fees">
                                        <tr>
                                            <td class="text-background-500 dark:text-background-300 text-sm"
                                                x-text="row.fullname"></td>
                                            <td
                                                class="text-background-500 dark:text-background-300 text-sm text-right p-1">
                                                <button type="button" @click="removeAthlete(row.id)">
                                                    <x-lucide-minus
                                                        class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:text-primary-700" />
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>

                        </div>
                    </div>

                    <div class="flex justify-end" x-show="athletes_add_fees.length > 0">
                        <x-primary-button x-on:click.prevent="openConfirmModal">
                            {{ __('fees.associate_fees') }}
                        </x-primary-button>
                    </div>

                    <x-modal name="confirm-modal" :show="$errors->userId->isNotEmpty()" focusable>

                        <div class="p-6">
                            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                                {{ __('fees.associate_fees') }}
                            </h2>

                            <p>
                                {{ __('fees.associate_fees_message') }}:
                            </p>

                            <ul>
                                <li x-show="feesCheckData.fees_consumed > 0">
                                    {{ __('fees.fees_consumed') }}: <span x-text="feesCheckData.fees_consumed"></span>
                                </li>
                            </ul>

                            <p>
                                {{ __('fees.associate_fees_message_remaining') }}:
                            </p>

                            <ul>
                                <li>
                                    {{ __('fees.fees') }}: <span
                                        x-text="feesCheckData.available_fees - feesCheckData.fees_consumed"></span>
                                </li>

                            </ul>

                            <div class="flex justify-end">
                                <x-primary-button x-on:click.prevent="confirmAssociateFees">
                                    {{ __('fees.associate_fees') }}
                                </x-primary-button>
                            </div>
                        </div>

                    </x-modal>

                    <x-modal name="error-modal" :show="$errors->userId->isNotEmpty()" focusable>
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                                {{ __('fees.associate_fees') }}
                            </h2>

                            <p>
                                {{ __('fees.associate_fees_error') }}
                            </p>

                            <div class="flex justify-end">
                                <x-primary-button x-on:click.prevent="$dispatch('close-modal', 'error-modal')">
                                    {{ __('fees.close') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </x-modal>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
