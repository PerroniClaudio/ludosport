@props([
    'nationality' => '',
    'selectedAcademyId' => '',
    'selectedAcademy' => '',
    'nations' => [],
    'academies' => [],
    'isCreate' => false,
])


<div x-data="{
    selectedNationality: '{{ $nationality }}',
    selectedAcademyId: '{{ $selectedAcademyId }}',
    currentAcademyId: '{{ $selectedAcademyId }}',
    selectedAcademy: '{{ $selectedAcademy ? addslashes($selectedAcademy) : 'Select an academy' }}',

    academies: {{ collect($academies) }},
    isAcademyDialogOpen: false,
    searchAcademyByValue(e) {
        const search = e.target.value.toLowerCase();
        if (search === '') {
            this.getAcademies();
        } else {
            this.academies = this.academies.filter(academy => {
                return academy.name.toLowerCase().includes(search);
            });
        }
    },
    updateNationId() {
        this.selectedAcademyId = '';
        this.selectedAcademy = '';
        this.getAcademies();
    },
    getAcademies() {
        const url = `/nation/${this.selectedNationality}/academies`;
        fetch(url, {
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(data => data.json())
            .then(res => this.academies = res)
            .catch(e => console.log(e))
    },
}">

    <div class="w-full flex flex-col gap-2">
        <div>
            <x-input-label for="nationality" value="Nationality" />
            <select {{$isCreate ? '' : 'disabled'}} x-model="selectedNationality" x-on:change="updateNationId()" name="nationality" id="nationality"
                class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm">
                {{-- Si è deciso di non modificare l'accademia di appartenenza per evitare problemi con le associazioni degli atleti eventualmente presenti --}}
                <option value="" selected disabled>{{ __('Select a country') }}</option>
                @foreach ($nations as $key => $nation)
                    <optgroup label="{{ $key }}">
                        @foreach ($nation as $n)
                            <option value="{{ $n['id'] }}" {{ $n['id'] == $nationality ? 'selected' : ($isCreate ? '' : 'disabled') }}>
                                {{ $n['name'] }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>


        <div id="academy-container">
            <div class="flex gap-2">
                <x-input-label for="academy" value="{{ __('users.academy') }}" />
                <div class="has-tooltip">
                    <x-lucide-info class="h-4 text-background-300" />
                    <div class="tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full">
                        {{ __('school.transfer_courses_tooltip') }}
                    </div>
                </div>
            </div>
            <div class="flex w-full gap-2">
                <input type="hidden" name="academy_id" x-model="selectedAcademyId">
                <x-text-input :disabled="true" placeholder="Select an academy" name="academy" class="flex-1" type="text" x-model="selectedAcademy" />
                
                @if (auth()->user()->getRole() === 'admin')
                    <div class="text-primary-500 hover:bg-background-500 dark:hover:bg-background-900 p-2 rounded-full cursor-pointer"
                        x-on:click="isAcademyDialogOpen = true">
                        <x-lucide-search class="w-6 h-6 text-primary-500 dark:text-primary-400" />
                    </div>
                @endif
            </div>
            <x-input-error :messages="$errors->get('academy_id')" class="mt-2" />

            @if(auth()->user()->getRole() === 'admin')
                <div class="modal" role="dialog" tabindex="-1" x-show="isAcademyDialogOpen"
                    x-on:click.away="isAcademyDialogOpen = false" x-cloak x-transition>
                    <div class="fixed inset-0 z-10 overflow-y-auto bg-black bg-opacity-50">
                        <div class="flex items-center justify-center min-h-screen">
                            <div class="bg-background-100 dark:bg-background-800 rounded-lg shadow-lg p-6 w-full max-w-3xl">
                                <div class="flex justify-between items-center">
                                    <h2 class="text-xl font-semibold text-background-500 dark:text-background-300">
                                        {{ __('users.select_academy') }}</h2>
                                    <div class="cursor-pointer" x-on:click="isAcademyDialogOpen = false">
                                        <x-lucide-x class="w-6 h-6 text-background-500 dark:text-background-300" />
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div
                                        class="mb-5 overflow-x-auto bg-white dark:bg-background-900 rounded-lg shadow overflow-y-auto relative min-h-[600px] flex flex-col justify-between max-h-[80vh]">

                                        <div class="flex justify-between items-center p-6">
                                            <div class="flex items-center justify-end w-full">
                                                <x-text-input type="text" x-on:input="searchAcademyByValue($event)"
                                                    placeholder="Search..."
                                                    class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                                            </div>
                                        </div>
                                        <table
                                            class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">

                                            <thead>
                                                <tr class="text-left">
                                                    <th
                                                        class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                        ID</th>
                                                    <th
                                                        class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                        Name</th>
                                                    <th
                                                        class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                        Action</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <template x-if="academies.length === 0">
                                                    <tr>
                                                        <td colspan="100%"
                                                            class="text-center text-background-500 dark:text-background-300 py-10 px-4 text-sm">
                                                            No records found
                                                        </td>
                                                    </tr>
                                                </template>
                                                <template x-for="(academy, index) in academies" :key="'academy-' + index">
                                                    <tr class="hover:bg-background-200 dark:hover:bg-background-900 cursor-pointer"
                                                        x-on:click="selectedAcademyId = academy.id; selectedAcademy = academy.name; isAcademyDialogOpen = false;">
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300"
                                                            x-text="academy.id" />
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300"
                                                            x-text="academy.name" />
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300">
                                                            <x-primary-button type="button"
                                                                x-on:click="selectedAcademy = academy.name; selectedAcademyId = academy.id; isAcademyDialogOpen = false;">{{ __('users.select') }}</x-primary-button>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div>
            <p>
                <x-input-error :messages="$errors->get('academy_id')" />
                <x-input-error :messages="$errors->get('nationality')" />
            </p>
        </div>
    </div>

    <template x-if="{{!$isCreate}} && (selectedAcademyId != currentAcademyId)">
        <div class="mt-2">
            <div class="flex gap-2">
                <x-input-label for="transfer_athletes" value="{{ __('school.transfer_athletes') }}" />
                <div class="has-tooltip">
                    <x-lucide-info class="h-4 text-background-300" />
                    <div class="tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full">
                        {{ __('school.transfer_athletes_tooltip') }}
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-2 text-sm">
                <label class="flex items-center w-fit cursor-pointer">
                    <input type="radio" name="transfer_athletes" value="yes" required>
                    <span class="ml-2">{{ __('school.yes') }}</span>
                </label>
                <label class="flex items-center w-fit cursor-pointer">
                    <input type="radio" name="transfer_athletes" value="no" required>
                    <span class="ml-2">{{ __('school.no') }}</span>
                </label>
            </div>
        </div>
    </template>

</div>
