@props(['clan' => 0])

<div class="flex flex-col gap-4">
    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('clan.disable_clan') }}</h3>
        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
        <div class="w-1/2 flex flex-col gap-2">
            <p class="mt-1 text-sm text-background-600 dark:text-background-200">
                {{ __('clan.disable_clan_message') }}
            </p>

            <div class="w-1/2">
                <x-danger-button x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-disable')">{{ __('clan.disable_clan') }}
                </x-danger-button>
            </div>

        </div>
    </div>

    <x-modal name="confirm-user-disable" :show="$errors->disable->isNotEmpty()" focusable>
        @php
            $authRole = auth()->user()->getRole();
            $editUserRoute = $authRole === 'admin' ? 'clans.disable' : $authRole . '.clans.disable';
        @endphp
        <form method="post" action="{{ route($editUserRoute, $clan) }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('clan.disable_clan') }}
            </h2>

            <p class="mt-1 text-sm text-background-600 dark:text-background-400">
                {{ __('clan.disable_clan_confirmation') }}
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('clan.disable_clan') }}
                </x-danger-button>
            </div>
        </form>

    </x-modal>
</div>
