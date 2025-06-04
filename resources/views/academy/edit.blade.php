@php
    $authRole = auth()->user()->getRole();
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('academies.edit') }} #{{ $academy->id }}
            </h2>

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">

            <div class="grid grid-cols-2 gap-4">

                @if ($authRole === 'admin')

                    <form method="POST" action="{{ route('academies.update', $academy->id) }}"
                        class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                        @csrf
                        <div class="flex items-center justify-between">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('academies.info') }}
                            </h3>

                        </div>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>


                        <div class="flex flex-col gap-2  mb-8">
                            <x-form.input name="name" label="Name" type="text" required="{{ true }}"
                                :value="$academy->name" placeholder="{{ fake()->company() }}" />
                            <div>
                                <x-input-label for="nationality" value="Nationality" />
                                <select x-model="selectedNationality" x-on:change="updateNationId()" name="nationality"
                                    id="nationality"
                                    class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm">
                                    @foreach ($nations as $key => $nation)
                                        <optgroup label="{{ $key }}"">
                                            @foreach ($nation as $n)
                                                <option value="{{ $n['id'] }}"
                                                    {{ $n['id'] == $academy->nation_id ? 'selected' : '' }}>
                                                    {{ $n['name'] }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 ">
                            <x-form.input name="email" label="{{ __('academies.academy_email') }}" type="text"
                                value="{{ $academy->email ?? '' }}" placeholder="{{ fake()->email() }}" />
                            <x-form.input name="rector" label="{{ __('academies.academy_rector') }}" type="text"
                                value="{{ $academy->rector() ? $academy->rector()->name . ' ' . ($academy->rector()->surname ?? '') : '' }}"
                                placeholder="{{ fake()->name() }}" disabled
                                description="{{ __('academies.academy_rector_description') }}" />
                        </div>

                        {{-- <h1 class="text-background-800 dark:text-background-200 text-lg">{{ __('academies.address') }}
                        </h1>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <div class="flex flex-col gap-2 ">
                            <x-form.input name="address" label="Address" type="text" value="{{ $academy->address }}"
                                placeholder="{{ fake()->address() }}" />
                            <x-form.input name="city" label="City" type="text" value="{{ $academy->city }}"
                                placeholder="{{ fake()->city() }}" />
                            <x-form.input name="zip" label="Zip" type="text" value="{{ $academy->zip }}"
                                placeholder="{{ fake()->postcode() }}" />
                        </div> --}}

                        <div class="fixed bottom-8 right-32 z-30">
                            <x-primary-button type="submit">
                                <x-lucide-save class="w-6 h-6 text-white" />
                            </x-primary-button>
                        </div>


                    </form>
                @else
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                        <div class="flex flex-col gap-2 ">
                            <x-form.input name="name" label="Name" type="text" required="{{ true }}"
                                disabled="{{ true }}" :value="$academy->name"
                                placeholder="{{ fake()->company() }}" />
                            @php
                                $nationId = $academy->nation_id;
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
                            <x-form.input name="nationality" label="Nationality" type="text"
                                required="{{ true }}" disabled="{{ true }}"
                                value="{{ $nationName }}" placeholder="{{ fake()->company() }}" />

                            <div class="flex flex-col gap-2 ">
                                <x-form.input name="email" label="{{ __('academies.academy_email') }}" type="text"
                                    value="{{ $academy->email ?? '' }}" placeholder="{{ fake()->email() }}"
                                    disabled />
                                <x-form.input name="rector" label="{{ __('academies.academy_rector') }}"
                                    type="text"
                                    value="{{ $academy->rector() ? $academy->rector()->name . ' ' . ($academy->rector()->surname ?? '') : '' }}"
                                    placeholder="{{ fake()->name() }}" disabled
                                    description="{{ __('academies.academy_rector_description') }}" />
                            </div>

                            {{-- <h1 class="text-background-800 dark:text-background-200 text-lg">{{ __('academies.address') }}
                            </h1>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            <x-form.input name="address" label="Address" type="text" required="{{ true }}"
                                disabled="{{ true }}" value="{{ $academy->address }}"
                                placeholder="{{ fake()->address() }}" />
                            <x-form.input name="city" label="City" type="text" required="{{ true }}"
                                disabled="{{ true }}" value="{{ $academy->city }}"
                                placeholder="{{ fake()->city() }}" />
                            <x-form.input name="zip" label="Zip" type="text" required="{{ true }}"
                                disabled="{{ true }}" value="{{ $academy->zip }}"
                                placeholder="{{ fake()->postcode() }}" /> --}}
                        </div>
                    </div>

                @endif

                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8"
                    x-data="{}">
                    <div class="flex flex-col h-full">
                        <div class="flex-none">
                            <div class="flex justify-between">
                                <div class="flex gap-2 items-center">
                                    <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                        {{ __('academies.logo') }}
                                    </h3>
                                    <div class='has-tooltip'>
                                        <span
                                            class='tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full'>
                                            {{ __('academies.picture_info') }}
                                        </span>
                                        <x-lucide-info class="h-4 text-background-400" />
                                    </div>
                                </div>
                                <div>
                                    <form method="POST" action="{{ route('academies.picture.update', $academy->id) }}"
                                        enctype="multipart/form-data" x-ref="pfpform">
                                        @csrf
                                        @method('PUT')

                                        <div class="flex flex-col gap-4">
                                            <div class="flex flex-col gap-2">
                                                <input type="file" name="academylogo" id="academylogo" class="hidden"
                                                    x-on:change="$refs.pfpform.submit()" />
                                                <x-primary-button type="button"
                                                    onclick="document.getElementById('academylogo').click()">
                                                    {{ __('users.upload_picture') }}
                                                </x-primary-button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            @if ($errors->get('academylogo') != null)
                                <div class="text-red-600 dark:text-red-400 flex items-center gap-1 my-2">
                                    <x-lucide-info class="h-4 text-red-600 dark:text-red-400" />
                                    <span>{{ __('academies.error_picture_size') }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col items-center justify-center flex-1 h-auto">

                            @if ($academy->picture)
                                <img src="{{ route('academy-image', $academy->id) }}" alt="{{ $academy->name }}"
                                    class="w-1/2 rounded-lg">
                            @endif

                        </div>
                    </div>
                </div>
            </div>

            <x-academy.search-users :academy="$academy" :roles="$roles" />

            @if ($authRole === 'admin' || $authRole === 'rector')
                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <div class="flex items-center justify-between">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">
                            {{ __('academies.personnel') }}
                        </h3>
                        <div class="flex items-center gap-1">

                            <x-academy.personnel :academy="$academy" :personnel="$personnel" :associatedPersonnel="$associated_personnel" />
                            <x-academy.create-user academy="{{ $academy->id }}" type="personnel"
                                :roles="$editable_roles" />

                        </div>
                    </div>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
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
            @endif

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <div class="flex items-center justify-between">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('academies.athletes') }}
                    </h3>
                    <div class="flex items-center gap-1">
                        @if ($authRole === 'admin' || $authRole === 'rector')
                            <x-academy.athletes :academy="$academy" :athletes="$athletes" :associatedAthletes="$associated_athletes" />
                            <x-academy.create-user academy="{{ $academy->id }}" type="athlete" :roles="$roles" />
                        @endif
                    </div>
                </div>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
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
                        'name' => 'School',
                        'field' => 'school',
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
                        <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                            x-text="row.school"></td>
                        <td
                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                            <a x-bind:href="'{{ $authRole === 'admin' ? '' : '/' . $authRole }}' + '/users/' + row.id">
                                <x-lucide-pencil
                                    class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                        </td>

                    </x-slot>


                </x-table>
            </div>

            @if ($authRole === 'admin' || $authRole === 'rector')
                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <div class="flex items-center justify-between">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">
                            {{ __('academies.schools') }}
                        </h3>
                        <div class="flex items-center gap-1">

                            <x-academy.create-school :academy="$academy->id" />

                        </div>
                    </div>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
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
                    ]" :rows="$academy->schools">
                        <x-slot name="tableActions">
                            <a
                                x-bind:href="'{{ $authRole === 'admin' ? '' : '/' . $authRole }}' + '/schools/' + row.id">
                                <x-lucide-pencil
                                    class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                        </x-slot>
                    </x-table>
                </div>
            @endif

            @if ($authRole === 'admin')
                <x-academy.fees :academy="$academy->id" />
            @endif

            @if ($authRole === 'admin')

                @if (!$academy->is_disabled)
                    <x-academy.disable-form :academy="$academy->id" />
                @endif

            @endif
        </div>
    </div>

</x-app-layout>
