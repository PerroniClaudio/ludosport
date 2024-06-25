@props([
    'school' => 0,
])

<div x-data="{}">

    <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'new-clan-modal')">
        <span>{{ __('school.create_clan') }}</span>
    </x-primary-button>

    <x-modal name="new-clan-modal" :show="$errors->get('name') || $errors->get('go_to_edit')" focusable>

        <form method="post" action="{{ route('schools.clan.create', $school) }}" class="p-6 flex flex-col gap-4"
            x-ref="form">
            @csrf

            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                    {{ __('school.quick_create_clan') }}
                </h2>
                <div>
                    <x-lucide-x class="w-6 h-6 text-background-500 dark:text-background-300 cursor-pointer"
                        x-on:click="$dispatch('close-modal', 'new-clan-modal')" />
                </div>
            </div>

            <input type="hidden" name="school_id" value="{{ $school }}">

            <x-form.input name="name" label="{{ __('school.clan_name') }}" type="text" required
                placeholder="{{ __('school.clan_name') }}" />

            <x-form.checkbox name="go_to_edit_clan" id="go_to_edit_clan" label="{{ __('school.go_to_edit_clan') }}" />

            <div class="flex justify-end">
                <x-primary-button x-on:click.prevent="$refs.form.submit()">
                    <span>{{ __('school.create_clan') }}</span>
                </x-primary-button>
            </div>

        </form>

    </x-modal>

</div>
