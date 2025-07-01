@props(['nation' => null])

<div x-data="{}">

    <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'new-academy-modal')">
        <span>{{ __('nations.create_academy') }}</span>
    </x-primary-button>


    <x-modal name="new-academy-modal" :show="$errors->get('name') || $errors->get('go_to_edit')" focusable>

        <form method="post" action="{{ route('nations.academies.create', $nation->id) }}" class="p-6 flex flex-col gap-4"
            x-ref="form">
            @csrf

            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                    {{ __('nations.quick_create_academy') }}
                </h2>
                <div>
                    <x-lucide-x class="w-6 h-6 text-background-500 dark:text-background-300 cursor-pointer"
                        x-on:click="$dispatch('close-modal', 'new-academy-modal')" />
                </div>
            </div>


            <input type="hidden" name="nation_id" value="{{ $nation->id }}">

            <x-form.input name="name" label="{{ __('nations.academy_name') }}" type="text" required
                placeholder="{{ __('nations.academy_name') }}" />

            <x-form.checkbox name="go_to_edit" id="go_to_edit" label="{{ __('nations.go_to_edit_academy') }}" />

            <div class="flex justify-end">
                <x-primary-button x-on:click.prevent="$refs.form.submit()">
                    <span>{{ __('nations.create_academy') }}</span>
                </x-primary-button>
            </div>

        </form>

    </x-modal>


</div>
