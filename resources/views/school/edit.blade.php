@php
    $authUser = auth()->user();
    $authRole = $authUser->getRole();
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-lg lg:text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('school.edit_school', ['id' => $school->id]) }}
            </h2>
        </div>
    </x-slot>
    <div class="py-6 lg:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col gap-4">

            @if (in_array($authRole, ['admin', 'rector', 'dean', 'manager']))
                <div x-data="{
                    address: '{{ $school->address }}',
                    city: '{{ $school->city }}',
                    zip: '{{ $school->zip }}',
                    verdict: '',
                    state: 0,
                    to_correct: '',
                    verifyAddress: function() {
                        const params = new URLSearchParams({
                            address: this.address,
                            city: this.city,
                            zip: this.zip,
                            nation: document.querySelector('#nationality').value,
                            school_id: '{{ $school->id }}'
                        });
                
                        const url = `/verify-address?${params}`;
                
                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                this.state = data.state;
                                this.verdict = data.message;
                                this.to_correct = data.unconfirmed;
                            });
                
                    }
                }" class="flex flex-col gap-4">



                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-4 lg:p-8">
                        <h3 class="text-background-800 dark:text-background-200 text-xl lg:text-2xl">
                            {{ __('school.info') }}</h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                        @if ($authRole === 'rector')
                            <form method="POST" action="{{ route('rector.schools.update', $school->id) }}">
                                @csrf
                                <div class="flex flex-col lg:flex-row gap-4">
                                    <div class="flex flex-col gap-2 w-full lg:w-1/2">
                                        <x-form.input name="name" label="Name" type="text"
                                            required="{{ true }}" :value="$school->name"
                                            placeholder="{{ fake()->company() }}" />
                                        <x-school.rector.academy nationality="{{ $school->nation_id }}"
                                            :selectedAcademyId="$school->academy_id" :selectedAcademy="$school->academy->name" :nations="$nations" :academies="$academies" />
                                    </div>
                                    <div class="flex flex-col gap-2 w-full lg:w-1/2">
                                        <div class="flex flex-col gap-2">
                                            <x-form.input name="dean" label="{{ __('school.school_dean') }}"
                                                type="text"
                                                value="{{ $school->dean ? $school->dean->name . ' ' . ($school->dean->surname ?? '') : '' }}"
                                                placeholder="{{ fake()->name() }}" disabled
                                                description="{{ __('school.school_dean_description') }}" />
                                            <x-form.input name="email" label="{{ __('school.school_email') }}"
                                                type="text" value="{{ $school->email ?? '' }}"
                                                placeholder="{{ fake()->email() }}" />
                                        </div>
                                    </div>
                                </div>

                                <div class="fixed bottom-4 right-4 lg:bottom-8 lg:right-32 z-10">
                                    <x-primary-button type="submit"
                                        class="w-12 h-12 lg:w-auto lg:h-auto p-3 lg:px-4 lg:py-2">
                                        <x-lucide-save class="w-6 h-6 text-white" />
                                    </x-primary-button>
                                </div>
                            </form>
                        @elseif(in_array($authRole, ['dean', 'manager']))
                            @php
                                $updateRoute = $authRole . '.schools.update';
                            @endphp
                            <form method="POST" action="{{ route($updateRoute, $school->id) }}">
                                @csrf
                                <div class="flex flex-col gap-4">
                                    <div class="w-full">
                                        <x-form.input name="name" label="Name" type="text"
                                            required="{{ true }}" disabled="{{ false }}"
                                            :value="$school->name" placeholder="{{ fake()->company() }}" />
                                    </div>

                                    @php
                                        $nationId = $school->nation_id;
                                        $nationName = '';
                                        // $nations contiene i continenti e quelli contengono le nazioni
                                        foreach ($nations as $key => $nation) {
                                            if ($nationName != '') {
                                                break;
                                            }
                                            foreach ($nation as $n) {
                                                if ($nationName != '') {
                                                    break;
                                                }
                                                if ($n['id'] == $nationId) {
                                                    $nationName = $n['name'];
                                                }
                                            }
                                        }
                                    @endphp

                                    <input type="hidden" name="academy_id" id="academy_id"
                                        value="{{ $school->academy_id }}">
                                    <input type="hidden" name="nationality" id="nationality"
                                        value="{{ $school->nation_id }}">

                                    <div class="flex flex-col lg:flex-row gap-4">
                                        <div class="flex flex-col gap-2 w-full lg:w-1/2">
                                            <x-form.input name="nationality" label="Nationality" type="text"
                                                required="{{ true }}" disabled="{{ true }}"
                                                :value="$nationName" placeholder="{{ fake()->company() }}" />
                                            <x-form.input name="academy_id" label="Academy" type="text"
                                                required="{{ true }}" disabled="{{ true }}"
                                                :value="$school->academy->name" placeholder="{{ fake()->company() }}" />
                                        </div>
                                        <div class="flex flex-col gap-2 w-full lg:w-1/2">
                                            <x-form.input name="dean" label="{{ __('school.school_dean') }}"
                                                type="text"
                                                value="{{ $school->dean ? $school->dean->name . ' ' . ($school->dean->surname ?? '') : '' }}"
                                                placeholder="{{ fake()->name() }}" disabled
                                                description="{{ __('school.school_dean_description') }}" />
                                            <x-form.input name="email" label="{{ __('school.school_email') }}"
                                                type="text" value="{{ $school->email ?? '' }}"
                                                disabled="{{ false }}" placeholder="{{ fake()->email() }}" />
                                        </div>
                                    </div>

                                    <div class="fixed bottom-4 right-4 lg:bottom-8 lg:right-32 z-10">
                                        <x-primary-button type="submit"
                                            class="w-12 h-12 lg:w-auto lg:h-auto p-3 lg:px-4 lg:py-2">
                                            <x-lucide-save class="w-6 h-6 text-white" />
                                        </x-primary-button>
                                    </div>
                                </div>
                            </form>
                        @else
                            <form method="POST" action="{{ route('schools.update', $school->id) }}">
                                @csrf
                                <div class="flex flex-col lg:w-1/2 gap-4">
                                    <x-form.input name="name" label="Name" type="text"
                                        required="{{ true }}" :value="$school->name"
                                        placeholder="{{ fake()->company() }}" />

                                    <x-school.academy nationality="{{ $school->nation_id }}" :selectedAcademyId="$school->academy_id"
                                        :selectedAcademy="$school->academy->name" :academies="$academies" :nations="$nations" />

                                    <x-school.dean :school="$school" />
                                    <x-form.input name="email" label="{{ __('school.school_email') }}" type="text"
                                        value="{{ $school->email ?? '' }}" placeholder="{{ fake()->email() }}" />


                                </div>

                                <div class="fixed bottom-4 right-4 lg:bottom-8 lg:right-32 z-10">
                                    <x-primary-button type="submit"
                                        class="w-12 h-12 lg:w-auto lg:h-auto p-3 lg:px-4 lg:py-2">
                                        <x-lucide-save class="w-6 h-6 text-white" />
                                    </x-primary-button>
                                </div>
                            </form>
                        @endif


                    </div>

                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-4 lg:p-8">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <h3 class="text-background-800 dark:text-background-200 text-xl lg:text-2xl">
                                {{ __('school.location') }}
                            </h3>
                            <div class='has-tooltip'>
                                <span
                                    class='tooltip rounded shadow-lg p-1 bg-primary-500 text-white text-sm max-w-[800px] -mt-6 -translate-y-full'>
                                    {{ __('school.location_explanation') }}</span>
                                <x-lucide-info class="w-5 h-5 text-primary-500 dark:text-primary-500 cursor-pointer" />
                            </div>
                        </div>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>



                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2 w-full">

                                <x-form.input-model x-model="address" name="address" label="Address" type="text"
                                    required="{{ true }}" value="{{ $school->address }}"
                                    placeholder="{{ fake()->address() }}" />

                                <x-form.input-model x-model="city" name="city" label="City" type="text"
                                    required="{{ true }}" value="{{ $school->city }}"
                                    placeholder="{{ fake()->city() }}" />

                                <x-form.input-model x-model="zip" name="zip" label="Zip" type="text"
                                    required="{{ true }}" value="{{ $school->zip }}"
                                    placeholder="{{ fake()->postcode() }}" />

                                <div class="flex gap-2 items-start">
                                    <div class="flex-shrink-0 mt-1">
                                        <x-lucide-check class="w-5 h-5 text-green-500 dark:text-green-400" x-cloak
                                            x-show="state==1" />
                                        <x-lucide-triangle-alert class="w-5 h-5 text-yellow-500 dark:text-yellow-400"
                                            x-cloak x-show="state==2" />
                                        <x-lucide-circle-x class="w-5 h-5 text-red-500 dark:text-red-400" x-cloak
                                            x-show="state==3" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p x-text="verdict"
                                            class="text-background-800 dark:text-background-200 break-words"></p>
                                        <p x-text="to_correct"
                                            class="text-background-800 dark:text-background-200 break-words">
                                        </p>
                                    </div>
                                </div>

                                <div class="flex gap-1">
                                    <x-primary-button type="button" class="w-full flex-shrink-0"
                                        x-on:click="verifyAddress">
                                        <div class="flex flex-col items-center justify-center w-full">
                                            <span
                                                class="text-sm lg:text-base">{{ __('school.verify_location') }}</span>
                                        </div>
                                    </x-primary-button>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>

            @endif


            @if ($authRole === 'admin' || $authRole === 'rector')
                <x-school.search-users :school="$school" :roles="$roles" />
            @endif

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-4 lg:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h3 class="text-background-800 dark:text-background-200 text-xl lg:text-2xl">
                        {{ __('academies.personnel') }}
                    </h3>
                    <div class="flex items-center gap-1 flex-wrap">
                        <x-school.personnel :school="$school" :personnel="$personnel" :associatedPersonnel="$associated_personnel" />
                        @if ($authRole === 'admin' || $authRole === 'rector')
                            <x-school.create-user :school="$school->id" type="personnel" :roles="$editable_roles" />
                        @endif
                    </div>
                </div>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                <div class="overflow-x-auto">
                    <x-table striped="false" :columns="[
                        [
                            'name' => 'Id',
                            'field' => 'id',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                        [
                            'name' => 'Name',
                            'field' => 'name',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                        [
                            'name' => 'Surname',
                            'field' => 'surname',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                        [
                            'name' => 'Role',
                            'field' => 'role',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                    ]" :rows="$associated_personnel">
                        <x-slot name="tableActions">
                            <a x-bind:href="'{{ $authRole === 'admin' ? '' : '/' . $authRole }}' + '/users/' + row.id">
                                <x-lucide-pencil
                                    class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                        </x-slot>
                    </x-table>
                </div>
            </div>

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-4 lg:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h3 class="text-background-800 dark:text-background-200 text-xl lg:text-2xl">
                        {{ __('academies.athletes') }}
                    </h3>
                    <div class="flex items-center gap-1 flex-wrap">
                        <x-school.athletes :school="$school" :athletes="$athletes" :associatedAthletes="$associated_athletes" />
                        <x-school.create-user :school="$school->id" type="athlete" />
                    </div>
                </div>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                <div class="overflow-x-auto">
                    <x-table striped="false" :columns="[
                        [
                            'name' => 'Id',
                            'field' => 'id',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                        [
                            'name' => 'Name',
                            'field' => 'name',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                        [
                            'name' => 'Surname',
                            'field' => 'surname',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                        [
                            'name' => 'Enabled',
                            'field' => 'is_disabled',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                        [
                            'name' => 'Actions',
                            'field' => 'actions',
                            'columnClasses' => 'text-right',
                            'rowClasses' => '',
                        ],
                    ]" :rows="$associated_athletes">
                        <x-slot name="tableRows">
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.id"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.name"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.surname"></td>
                            <td
                                class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                <div x-show="row.is_disabled">
                                    <x-lucide-x-circle class="w-5 h-5 text-red-500 dark:text-red-400" />
                                </div>
                                <div x-show="!row.is_disabled">
                                    <x-lucide-check-circle class="w-5 h-5 text-green-500 dark:text-green-400" />
                                </div>
                            </td>
                            <td
                                class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                <a
                                    x-bind:href="'{{ $authRole === 'admin' ? '' : '/' . $authRole }}' + '/users/' + row.id">
                                    <x-lucide-pencil
                                        class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                </a>
                            </td>
                        </x-slot>
                    </x-table>
                </div>
            </div>

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-4 lg:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h3 class="text-background-800 dark:text-background-200 text-xl lg:text-2xl">
                        {{ __('school.clans') }}
                    </h3>
                    <div class="flex items-center gap-1 flex-wrap">
                        @if ($authRole === 'admin')
                            <x-school.clans :school="$school" :athletes="$clans" />
                        @endif
                        <x-school.create-clan :school="$school->id" />
                    </div>
                </div>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                <div class="overflow-x-auto">
                    <x-table striped="false" :columns="[
                        [
                            'name' => 'Id',
                            'field' => 'id',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                        [
                            'name' => 'Name',
                            'field' => 'name',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                    ]" :rows="$school->clan">
                        <x-slot name="tableActions">
                            <a
                                x-bind:href="'{{ $authRole === 'admin' ? '' : '/' . $authRole }}' + '/courses/' + row.id">
                                <x-lucide-pencil
                                    class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                        </x-slot>
                    </x-table>
                </div>
            </div>

            @if (in_array($authRole, ['admin', 'rector']))
                @if (!$school->is_disabled)
                    <x-school.disable-form :school="$school->id" />
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
