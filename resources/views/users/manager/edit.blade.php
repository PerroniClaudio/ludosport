{{-- 
    I ruoli admin, rettore, istruttore e tecnico sono assegnabili solo dall'admin. 
    dean e manager da admin e rettore. 
    atleta da admin, rettore, dean e manager.
--}}
@php
    $authUser = auth()->user();
    $authRole = $authUser->getRole();
    $editable_roles = auth()->user()->getEditableRoles()->pluck('label');
    // Può modificare l'utente solo se è un atleta della stessa scuola
$canEdit = in_array($authUser->getActiveInstitutionId(), $user->academyAthletes->pluck('id')->toArray());
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('users.edit_user', ['id' => $user->id]) }}
            </h2>
        </div>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 mb-4">
                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('users.status') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <div class="w-1/2 flex flex-col gap-2">
                        <div>
                            @if ($user->hasRole('athlete'))
                                @if ($user->has_paid_fee)
                                    <div
                                        class="mt-1 text-sm text-background-600 dark:text-background-200 flex flex-row items-center gap-2">
                                        <x-lucide-check-circle class="w-6 h-6 text-green-500" />
                                        <span>{{ __('users.active_fee_paid') }}</span>
                                    </div>
                                @else
                                    <div
                                        class="mt-1 text-sm text-background-600 dark:text-background-200 flex flex-row items-center gap-2">
                                        <x-lucide-x-circle class="w-6 h-6 text-red-500" />
                                        <span>{{ __('users.inactive_fee_not_paid') }}</span>
                                    </div>
                                @endif
                            @endif
                        </div>
                        <!-- Non gli interessa più vedere questo dato -->
                        <!-- <div>
                            @if (!$user->is_disabled)
                                <div
                                    class="mt-1 text-sm text-background-600 dark:text-background-200 flex flex-row items-center gap-2">
                                    <x-lucide-check-circle class="w-6 h-6 text-green-500" />
                                    <span>{{ __('users.registered') }}</span>
                                </div>
                            @else
                                <div
                                    class="mt-1 text-sm text-background-600 dark:text-background-200 flex flex-row items-center gap-2">
                                    <x-lucide-x-circle class="w-6 h-6 text-red-500" />
                                    <span>{{ __('users.disabled') }}</span>
                                </div>
                            @endif
                        </div> -->
                        <div>
                            @if ($user->is_verified)
                                <div
                                    class="mt-1 text-sm text-background-600 dark:text-background-200 flex flex-row items-center gap-2">
                                    <x-lucide-check-circle class="w-6 h-6 text-green-500" />
                                    <span>{{ __('users.verified') }}</span>
                                </div>
                            @else
                                <div
                                    class="mt-1 text-sm text-background-600 dark:text-background-200 flex flex-row items-center gap-2">
                                    <x-lucide-x-circle class="w-6 h-6 text-red-500" />
                                    <span>{{ __('users.not_verified') }}</span>
                                    <form method="POST" action="{{ route('verification.send-for-user', $user->id) }}">
                                        @csrf
                                        <x-primary-button>
                                            <span>{{ __('users.send_verification_email') }}</span>
                                        </x-primary-button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>


            @if ($user->is_disabled)
                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 mb-4">
                    <p class="text-background-800 dark:text-background-200 text-xl">
                        {{ __('users.disabled_text') }}
                    </p>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <form method="POST" action="{{ route('manager.users.update', $user->id) }}">
                    @csrf

                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">
                            {{ __('users.personal_details_message') }}</h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <div class="flex flex-col gap-2">
                            <x-form.input name="name" label="Name" type="text" required="{{ true }}"
                                :value="$user->name" placeholder="{{ fake()->firstName() }}" :disabled="!$canEdit" />
                            <x-form.input name="surname" label="Surname" type="text" required="{{ true }}"
                                :value="$user->surname" placeholder="{{ fake()->lastName() }}" :disabled="!$canEdit" />
                            <x-form.input name="email" label="Email" type="email" required="{{ true }}"
                                value="{{ $user->email }}" placeholder="{{ fake()->email() }}" :disabled="!$canEdit" />
                            <x-form.input name="year" label="First subscription year" type="text"
                                required="{{ true }}" value="{{ $user->subscription_year }}"
                                placeholder="{{ date('Y') }}" :disabled="!$canEdit" />

                            <div>
                                <x-input-label for="nationality" value="Nationality" />
                                <select name="nationality" id="nationality" {{ !$canEdit ? 'disabled' : '' }}
                                    class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm">
                                    @foreach ($nations as $key => $nation)
                                        <optgroup label="{{ $key }}">
                                            @foreach ($nation as $n)
                                                <option value="{{ $n['id'] }}"
                                                    {{ $n['id'] == $user->nation_id ? 'selected' : '' }}>
                                                    {{ $n['name'] }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="" value="Instagram" />
                                <div
                                    class="w-full min-h-10 cursor-not-allowed px-3 py-2 border border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm">
                                    {{ $user->instagram ?? '' }}
                                </div>
                            </div>

                        </div>
                    </div>

                    @if ($canEdit)
                        <div class="fixed bottom-8 right-32">
                            <x-primary-button type="submit">
                                <x-lucide-save class="w-6 h-6 text-white" />
                            </x-primary-button>
                        </div>
                    @endif

                </form>
                <div>
                    <x-user.roles :user="$user" :roles="$user->roles" :availableRoles="$roles" />
                </div>
            </div>

            <!-- <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 my-4"
                x-data="{}">
                <div class="flex justify-between">
                    <div class="flex gap-2 items-center">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">
                            {{ __('users.profile_picture') }}
                        </h3>
                        <div class='has-tooltip'>
                            <span
                                class='tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full'>
                                {{ __('users.profile_picture_info') }}
                            </span>
                            <x-lucide-info class="h-4 text-background-400" />
                        </div>
                    </div>
                    <div>
                        <form method="POST" action="{{ route('manager.users.picture.update', $user->id) }}"
                            enctype="multipart/form-data" x-ref="pfpform">
                            @csrf
                            @method('PUT')

                            <div class="flex flex-col gap-4">
                                <div class="flex flex-col gap-2">
                                    @if ($canEdit)
<input type="file" name="profilepicture" id="profilepicture" class="hidden"
                                            x-on:change="$refs.pfpform.submit()" />
                                        <x-primary-button type="button"
                                            onclick="document.getElementById('profilepicture').click()">
                                            {{ __('users.upload_picture') }}
                                        </x-primary-button>
@endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                @if ($user->profile_picture)
<img src="{{ $user->profile_picture }}" alt="{{ $user->name }}" class="w-1/3 rounded-lg">
@endif

            </div> -->

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 py-4" x-data="{
                institutionType: 'academy',
                roleType: 'personnel',
                selectedAcademy: '',
                selectedSchool: '',
                setInstitutionType(type) {
                    this.institutionType = type;
                },
                setRoleType(type) {
                    this.roleType = type;
                }
            
            }">
                @if ($user->hasRole('instructor') || $user->hasRole('technician') || $user->hasRole('athlete'))
                    <x-user.weapon-forms :availableWeaponForms="$allWeaponForms" :user="$user->id" :forms="$user->weaponForms->map(function ($form) {
                        $form->awarded_at = explode(' ', $form->awarded_at)[0];
                        return $form;
                    })" type="athlete" />
                    <x-user.weapon-forms :availableWeaponForms="$allWeaponForms" :user="$user->id" :forms="$user->weaponFormsPersonnel->map(function ($form) {
                        $form->awarded_at = explode(' ', $form->awarded_at)[0];
                        return $form;
                    })" type="personnel" />
                    <x-user.weapon-forms :availableWeaponForms="$allWeaponForms" :user="$user->id" :forms="$user->weaponFormsTechnician->map(function ($form) {
                        $form->awarded_at = explode(' ', $form->awarded_at)[0];
                        return $form;
                    })" type="technician" />
                @endif

                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8"
                    x-data="{}">
                    <div class="flex justify-between">
                        <div class="flex gap-2 items-center">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                {{ __('users.profile_picture') }}
                            </h3>
                            <div class='has-tooltip'>
                                <span
                                    class='tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full'>
                                    {{ __('users.profile_picture_info') }}
                                </span>
                                <x-lucide-info class="h-4 text-background-400" />
                            </div>
                        </div>
                        <div>
                            <form method="POST" action="{{ route('manager.users.picture.update', $user->id) }}"
                                enctype="multipart/form-data" x-ref="pfpform">
                                @csrf
                                @method('PUT')

                                <div class="flex flex-col gap-4">
                                    <div class="flex flex-col gap-2">
                                        @if ($canEdit)
                                            <input type="file" name="profilepicture" id="profilepicture"
                                                class="hidden" x-on:change="$refs.pfpform.submit()" />
                                            <x-primary-button type="button"
                                                onclick="document.getElementById('profilepicture').click()">
                                                {{ __('users.upload_picture') }}
                                            </x-primary-button>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    @if ($user->profile_picture)
                        <img src="{{ $user->profile_picture }}" alt="{{ $user->name }}" class="w-1/3 rounded-lg">
                    @endif

                </div>

                <div
                    class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 text-background-800 dark:text-background-200 ">
                    <h3 class="text-2xl">
                        {{ __('users.academies') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    <h5 class="text-lg">{{ __('users.as_personnel') }}</h5>

                    <div class="flex flex-col gap-2">
                        @php
                            $mainAcademyPersonnel = $user->primaryAcademy();
                            $allPrimaryAcademiesIds = $user->academies()->wherePivot('is_primary', true)->pluck('academies.id')->toArray();
                        @endphp
                        @foreach ($user->academies as $academy)
                            <div
                                class="flex flex-row items-center gap-2 justify-between hover:text-primary-500 hover:bg-background-900 p-2 rounded">
                                <span class="flex items-center gap-2">
                                    <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                    {{ $academy->name }}
                                    @if (($mainAcademyPersonnel->id ?? null) == $academy->id)
                                        ({{ __('users.main_academy') }})
                                    @endif
                                </span>
                                @if (in_array($academy->id, $allPrimaryAcademiesIds))
                                    <span class='has-tooltip'>
                                        <span class='tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full'>
                                            {{ __('academies.set_as_primary_tooltip') }}
                                        </span>
                                        <x-lucide-check class="w-6 h-6 text-green-500" />
                                    </span>
                                @endif
                            </div>
                        @endforeach

                    </div>

                    <h5 class="text-lg">{{ __('users.as_athlete') }}</h5>

                    <div class="flex flex-col gap-2">
                        @php
                            $mainAcademyAthlete = $user->primaryAcademyAthlete();
                        @endphp
                        @foreach ($user->academyAthletes as $academy)
                            <div
                                class="flex flex-row items-center gap-2 hover:text-primary-500 hover:bg-background-900 p-2 rounded">
                                <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                <span>
                                    {{ $academy->name }}
                                    @if (($mainAcademyAthlete->id ?? null) == $academy->id)
                                        ({{ __('users.main_academy') }})
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 text-background-800 dark:text-background-200">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">
                        {{ __('users.schools') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    <div class="flex justify-between">
                        <h5 class="text-lg">{{ __('users.as_personnel') }}</h5>
                        <div class="flex gap-2">
                            {{-- <x-primary-button :disabled="$user->schools()->count() < 1" --}}
                            <x-primary-button :disabled="$user->schools()->count() < 1 || ($authRole === 'admin' ? false : (in_array($authUser->getActiveInstitutionId(), $user->academies()->pluck('academy_id')->toArray()) ? false : true))"
                                x-on:click.prevent="setInstitutionType('school'), setRoleType('personnel'), $dispatch('open-modal', 'set-main-institution-modal')">
                                <span>{{ __('users.set_main_personnel_school') }}</span>
                            </x-primary-button>
                            <x-user.select-institutions type="school-personnel" :user="$user" :schools="$filteredSchoolsPersonnel"
                                :selectedSchools="$user->schools" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        @php
                            $mainSchool = $user->primarySchool();
                            $allPrimarySchoolsIds = $user->schools()->wherePivot('is_primary', true)->pluck('schools.id')->toArray();
                        @endphp
                        @foreach ($user->schools as $school)
                            <a href="{{ route('schools.edit', $school->id) }}"
                                class="flex flex-row items-center gap-2 justify-between hover:text-primary-500 hover:bg-background-900 p-2 rounded">
                                <span class="flex items-center gap-2">
                                    <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                    {{ $school->name }}
                                    @if (($mainSchool->id ?? null) == $school->id)
                                        ({{ __('users.main_school') }})
                                    @endif
                                </span>
                                @if (in_array($school->id, $allPrimarySchoolsIds))
                                    <span class='has-tooltip'>
                                        <span class='tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full'>
                                            {{ __('school.set_as_primary_tooltip') }}
                                        </span>
                                        <x-lucide-check class="w-6 h-6 text-green-500" />
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>

                    <div class="flex justify-between mt-2">
                        <h5 class="text-lg">{{ __('users.as_athlete') }}</h5>
                        <div class="flex gap-2">
                            <x-primary-button :disabled="$user->schoolAthletes()->count() < 1 || ($authRole === 'admin' ? false : ($authUser->getActiveInstitutionId() == $user->primaryAcademyAthlete()->id ? false : true))"
                                x-on:click.prevent="setInstitutionType('school'), setRoleType('athlete'), $dispatch('open-modal', 'set-main-institution-modal')">
                                <span>{{ __('users.set_main_athletes_school') }}</span>
                            </x-primary-button>

                            <x-user.select-institutions type="school-athlete" :user="$user" :schools="$filteredSchoolsAthlete"
                                :selectedSchools="$user->schoolAthletes" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        @php
                            $mainSchoolAthlete = $user->primarySchoolAthlete();
                        @endphp
                        @foreach ($user->schoolAthletes as $schools)
                            <div
                                class="flex flex-row items-center gap-2 hover:text-primary-500 hover:bg-background-900 p-2 rounded">
                                <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                <span>
                                    {{ $schools->name }}
                                    @if (($mainSchoolAthlete->id ?? null) == $schools->id)
                                        ({{ __('users.main_school') }})
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>

                </div>

                {{-- Modal con form dinamico per modifica accademia/scuola principale --}}
                <x-modal name="set-main-institution-modal" :show="$errors->get('name') || $errors->get('go_to_edit')" focusable>
                    <form method="POST"
                        action="{{ route(($authRole === 'admin' ? '' : $authRole . '.') . 'users.set-main-institution') }}"
                        class="p-6 flex flex-col gap-4" x-ref="edituserform" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                                    <div x-show="institutionType == 'academy' && roleType == 'personnel'">
                                        {{ __('users.set_main_personnel_academy') }}
                                    </div>
                                    <div x-show="institutionType == 'academy' && roleType == 'athlete'">
                                        {{ __('users.set_main_athletes_academy') }}
                                    </div>
                                    <div x-show="institutionType == 'school' && roleType == 'personnel'">
                                        {{ __('users.set_main_personnel_school') }}
                                    </div>
                                    <div x-show="institutionType == 'school' && roleType == 'athlete'">
                                        {{ __('users.set_main_athletes_school') }}
                                    </div>
                                </h2>
                                <div>
                                    <x-lucide-x
                                        class="w-6 h-6 text-background-500 dark:text-background-300 cursor-pointer"
                                        x-on:click="$dispatch('close-modal', 'set-main-institution-modal')" />
                                </div>
                            </div>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        </div>

                        <input type="hidden" name="institution_type" :value="institutionType">
                        <input type="hidden" name="role_type" :value="roleType">
                        <input type="hidden" name="user_id" value="{{ $user->id }}">

                        {{-- Il manager non può modificare l'accademia principale --}}
                        <template x-if="institutionType == 'academy' && roleType == 'personnel'">
                            @php
                                $academiesPersonnelOptions = $user->academies->map(function ($academy) {
                                    return ['value' => $academy->id, 'label' => $academy->name];
                                });
                                // $user non ha accesso alla funzione getActiveInstitutionId() perché non è l'utente loggato.
                                // Per ora il manager non può modificare l'accademia principale, quindi se servirà in futuro vedremo come fare.
                                $selectedAcademy = [
                                    'value' => $user->primaryAcademy() ?? null,
                                    'label' => $user->primaryAcademy()->name ?? null,
                                ];
                            @endphp
                            <x-form.select name="academy_id" label="{{ __('academies.academy') }}" :options="$academiesPersonnelOptions"
                                x-model="selectedAcademy" />
                        </template>

                        <template x-if="institutionType == 'academy' && roleType == 'athlete'">
                            @php
                                $academiesAthleteOptions = $user->academyAthletes->map(function ($academy) {
                                    return ['value' => $academy->id, 'label' => $academy->name];
                                });
                                $selectedAcademy = [
                                    'value' => $user->primaryAcademyAthlete()->id ?? null,
                                    'label' => $user->primaryAcademyAthlete()->name ?? null,
                                ];
                            @endphp
                            <x-form.select name="academy_id" label="{{ __('academies.academy') }}"
                                :options="$academiesAthleteOptions" />
                        </template>

                        <template x-if="institutionType == 'school' && roleType == 'personnel'">
                            @php
                                $schoolsPersonnelOptions = [];
                                if ($authRole == 'admin') {
                                    $schoolsPersonnelOptions = $user->schools;
                                } else {
                                    $schoolsPersonnelOptions = $user->schools
                                        ->whereIn('academy_id', $authUser->getActiveInstitutionId());
                                }
                            @endphp
                            {{-- <x-form.select name="school_id" label="{{ __('school.school') }}" :options="$schoolsPersonnelOptions" /> --}}
                            <div>
                                <table
                                    class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1 rounded-lg shadow overflow-hidden">

                                    <thead>
                                        <tr class="text-left">
                                            <th
                                                class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">

                                                {{ __('school.school') }}</th>

                                            <th
                                                class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">

                                                {{ __('users.set_main_personnel_school') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($schoolsPersonnelOptions as $school)
                                            <tr
                                                class="hover:bg-background-200 dark:hover:bg-background-900 cursor-pointer">
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300">
                                                    {{ $school->name }}</td>

                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300">

                                                    <x-form.checkbox :id="$school->id" :name="'school_id_' . $school->id"
                                                        label="Primary" :isChecked="$school->pivot->is_primary" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <template x-if="institutionType == 'school' && roleType == 'athlete'">
                            @php
                                $schoolsAthleteOptions = $user->schoolAthletes->map(function ($school) {
                                    return ['value' => $school->id, 'label' => $school->name];
                                });
                                $selectedSchool = [
                                    'value' => $user->primarySchoolAthlete()->id ?? null,
                                    'label' => $user->primarySchoolAthlete()->name ?? null,
                                ];
                            @endphp
                            <x-form.select name="school_id" label="{{ __('school.school') }}" :options="$schoolsAthleteOptions" />
                        </template>


                        <div class="flex justify-end">
                            <x-primary-button x-on:click.prevent="$refs.edituserform.submit()">
                                <span>{{ __('users.confirm') }}</span>
                            </x-primary-button>
                        </div>

                    </form>
                </x-modal>

            </div>

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <h3 class="text-background-800 dark:text-background-200 text-2xl">
                    {{ __('users.rank') }}</h3>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                <p class="text-background-600 dark:text-background-200 mb-2">
                    {{ __('users.rank_message', [
                        'rank' => __('users.' . strtolower($user->rank->name)),
                    ]) }}
                </p>
                <a href="{{ route('users.rank.request.specific', $user->id) }}">
                    <x-primary-button>
                        <span>{{ __('users.request_rank') }}</span>
                    </x-primary-button>
                </a>
            </div>

            {{-- @if (!$user->is_disabled && $authRole == 'admin')
                <x-user.disable-user-form :user="$user->id" />
            @endif --}}
        </div>
    </div>
</x-app-layout>
