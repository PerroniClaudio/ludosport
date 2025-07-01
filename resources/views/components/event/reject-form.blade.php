@props([
    'event' => 0,
])

<div>

    <x-danger-button x-data="" type="button"
        x-on:click.prevent="$dispatch('open-modal', 'confirm-rejection')">
        {{ __('events.reject') }}
    </x-danger-button>


    <x-modal name="confirm-rejection" :show="$errors->disable->isNotEmpty()" focusable>

        <form method="post" action="{{ route('events.reject', $event) }}"class="p-6">
            @csrf


            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('events.reject_event_title') }}
            </h2>

            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>


            <x-form.textarea name="reason" label="{{ __('events.reject_reason') }}" required />

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('events.reject') }}
                </x-danger-button>
            </div>
        </form>

    </x-modal>
</div>
