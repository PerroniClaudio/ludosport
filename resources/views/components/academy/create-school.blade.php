@props([
    'academy' => 0,
])

@php
    $authRole = auth()->user()->getRole();
    $addToRoute = $authRole === 'admin' ? '' : $authRole . '.';
@endphp

<div x-data="{}">

    <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'new-school-modal')">
        <span>{{ __('academies.create_school') }}</span>
    </x-primary-button>

    <x-modal name="new-school-modal" :show="$errors->get('name') || $errors->get('go_to_edit')" focusable>

        <form method="post" action="{{ route($addToRoute . 'academies.schools.create', $academy) }}" class="p-6 flex flex-col gap-4"
            x-ref="form">
            @csrf

            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                    {{ __('academies.quick_create_school') }}
                </h2>
                <div>
                    <x-lucide-x class="w-6 h-6 text-background-500 dark:text-background-300 cursor-pointer"
                        x-on:click="$dispatch('close-modal', 'new-school-modal')" />
                </div>
            </div>

            <input type="hidden" name="academy_id" value="{{ $academy }}">

            <x-form.input name="name" label="{{ __('academies.school_name') }}" type="text" required
                placeholder="{{ __('academies.school_name') }}" />

            <x-form.checkbox name="go_to_edit_school" id="go_to_edit_school"
                label="{{ __('academies.go_to_edit_school') }}" />

            <div class="flex justify-end">
                <x-primary-button x-on:click.prevent="$refs.form.submit()">
                    <span>{{ __('academies.create_school') }}</span>
                </x-primary-button>
            </div>

        </form>

    </x-modal>

</div>
