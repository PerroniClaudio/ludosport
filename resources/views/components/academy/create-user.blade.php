@props([
    'academy' => 0,
    'type' => '',
    'roles' => [],
])

@php

    $authRole = auth()->user()->getRole();
    $addToRoute = $authRole === 'admin' ? '' : $authRole . '.';

    $should_show_modal_for_errors =
        $errors->get('name') ||
        $errors->get('surname') ||
        $errors->get('email') ||
        $errors->get('birthday') ||
        $errors->get('roles') ||
        $errors->get('minor_documents') ||
        $errors->get('type') ||
        $errors->get('go_to_edit');

@endphp

<div x-data="{
    selected: [],
    birthday: @js(old('birthday')),
    selectRole(role) {
        if (this.selected.includes(role)) {
            this.selected = this.selected.filter(item => item !== role);
        } else {
            this.selected.push(role);
        }
    },
    isMinorBirthday() {
        if (!this.birthday) {
            return false;
        }
        const birthDate = new Date(this.birthday);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age < 18;
    }
}">

    <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'new-user-{{ $type }}-modal')">
        @if ($type === 'personnel')
            <span>{{ __('academies.create_personnel') }}</span>
        @else
            <span>{{ __('academies.create_athlete') }}</span>
        @endif
    </x-primary-button>

    <x-modal name="new-user-{{ $type }}-modal" :show="$should_show_modal_for_errors" focusable>

        <form action="{{ route($addToRoute . 'academies.users.create', $academy) }}" method="post" enctype="multipart/form-data" class="p-6 flex flex-col gap-4">

            @csrf

            <input type="hidden" name="academy_id" value="{{ $academy }}">

            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                    @if ($type === 'personnel')
                        {{ __('academies.quick_create_personnel') }}
                    @else
                        {{ __('academies.quick_create_athlete') }}
                    @endif
                </h2>
                <div>
                    <x-lucide-x class="w-6 h-6 text-background-500 dark:text-background-300 cursor-pointer"
                        x-on:click="$dispatch('close-modal', 'new-user-{{ $type }}-modal')" />
                </div>
            </div>

            <input type="hidden" name="type" value="{{ $type }}">

            <x-form.input name="name" label="{{ __('users.name') }}" type="text" required
                placeholder="{{ __('users.name') }}" />

            <x-form.input name="surname" label="{{ __('users.surname') }}" type="text" required
                placeholder="{{ __('users.surname') }}" />

            <x-form.input name="email" label="{{ __('users.email') }}" type="email" required
                placeholder="{{ __('users.email') }}" />

            <x-form.input name="birthday" label="{{ __('Birthday') }}" type="date" required x-model="birthday" />

            <div x-show="isMinorBirthday()" x-cloak class="flex flex-col gap-2">
                {{-- <x-form.select name="gender" label="{{ __('Gender') }}" :options="[
                    ['value' => 'male', 'label' => 'Male'],
                    ['value' => 'female', 'label' => 'Female'],
                    ['value' => 'other', 'label' => 'Other'],
                    ['value' => 'notsay', 'label' => 'Prefer not to say'],
                ]" :shouldHaveEmptyOption="true" /> --}}

                <label for="minor_documents" class="text-sm font-medium text-background-700 dark:text-background-300">
                    {{ __('auth.minor_documents') }}
                </label>
                <input id="minor_documents" name="minor_documents" type="file" accept="application/pdf"
                    class="block w-full rounded-lg border border-background-200 bg-white px-4 py-3 text-sm text-background-700 shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100 dark:border-background-700 dark:bg-background-900 dark:text-background-200 dark:file:bg-primary-950/50 dark:file:text-primary-300" />
                <x-input-error :messages="$errors->get('minor_documents')" class="mt-2" />
            </div>


            <div>



                @if ($type === 'personnel')
                    <x-input-label value="Role" />
                    <div class="grid grid-cols-3 gap-4 text-background-800 dark:text-background-200">
                        @foreach ($roles as $role)
                            @if ($role->label === 'athlete' || $role->label === 'admin')
                                @continue
                            @endif

                            <div x-on:click="selectRole('{{ $role->label }}')"
                                class="border border-background-700 hover:border-primary-500 rounded-lg p-4 cursor-pointer flex items-center gap-2"
                                :class="{ 'border-primary-500': selected.includes('{{ $role->label }}') }">

                                @switch($role->label)
                                    @case('admin')
                                        <x-lucide-crown class="w-6 h-6 text-primary-500" />
                                    @break

                                    @case('athlete')
                                        <x-lucide-swords class="w-6 h-6 text-primary-500" />
                                    @break

                                    @case('rector')
                                        <x-lucide-graduation-cap class="w-6 h-6 text-primary-500" />
                                    @break

                                    @case('dean')
                                        <x-lucide-book-marked class="w-6 h-6 text-primary-500" />
                                    @break

                                    @case('manager')
                                        <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                    @break

                                    @case('technician')
                                        <x-lucide-wrench class="w-6 h-6 text-primary-500" />
                                    @break

                                    @case('instructor')
                                        <x-lucide-megaphone class="w-6 h-6 text-primary-500" />
                                    @break

                                    @default
                                @endswitch

                                <span>{{ __("users.{$role->label}") }}</span>
                            </div>
                        @endforeach

                    </div>

                    <input type="hidden" name="roles" x-model="selected">
                @endif

            </div>

            <x-form.checkbox name="go_to_edit" id="go_to_edit_{{ $type }}"
                label="{{ __('academies.go_to_edit') }}" />

            <div class="flex justify-end">
                <x-primary-button>
                    <span>
                        @if ($type === 'personnel')
                            {{ __('academies.create_personnel') }}
                        @else
                            {{ __('academies.create_athlete') }}
                        @endif
                    </span>
                </x-primary-button>
            </div>


        </form>

    </x-modal>

</div>
