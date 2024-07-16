@props([
    'school' => 0,
    'type' => '',
    'roles' => [],
])

@php

    $should_show_modal_for_errors =
        $errors->get('name') || $errors->get('surname') || $errors->get('email') || $errors->get('go_to_edit');

@endphp

<div x-data="{
    selected: [],
    selectRole(role) {
        if (this.selected.includes(role)) {
            this.selected = this.selected.filter(item => item !== role);
        } else {
            this.selected.push(role);
        }
    }
}">

    <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'new-user-{{ $type }}-modal')">
        @if ($type === 'personnel')
            <span>{{ __('school.create_personnel') }}</span>
        @else
            <span>{{ __('school.create_athlete') }}</span>
        @endif
    </x-primary-button>

    <x-modal name="new-user-{{ $type }}-modal" :show="$should_show_modal_for_errors" focusable>

        @php
            $authRole = auth()->user()->getRole();
            $formRoute = $authRole === 'admin' ? 'schools.users.create' : $authRole . '.schools.users.create';
        @endphp
        <form action="{{ route($formRoute, $school) }}" method="post" class="p-6 flex flex-col gap-4">

            @csrf

            <input type="hidden" name="school_id" value="{{ $school }}">

            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                    @if ($type === 'personnel')
                        {{ __('school.quick_create_personnel') }}
                    @else
                        {{ __('school.quick_create_athlete') }}
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
                label="{{ __('school.go_to_edit') }}" />

            <div class="flex justify-end">
                <x-primary-button>
                    <span>
                        @if ($type === 'personnel')
                            {{ __('school.create_personnel') }}
                        @else
                            {{ __('school.create_athlete') }}
                        @endif
                    </span>
                </x-primary-button>
            </div>


        </form>

    </x-modal>

</div>
