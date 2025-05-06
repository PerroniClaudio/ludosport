@props([
    'academy' => null,
])

<div x-data="{
    academy_id: {{ $academy }},
    availableFees: 0,
    usersNoFees: 0,
    getAvailableFees() {
        fetch(`/academies/available-fees?academy_id=${this.academy_id}`)
            .then(response => response.json())
            .then(data => {
                this.availableFees = data.count;
            })
            .catch(error => console.error('Error:', error));
    },
    getUsersNoFees() {
        fetch(`/academies/${this.academy_id}/athletes-no-fee`)
            .then(response => response.json())
            .then(data => {
                this.usersNoFees = data.count;
            })
            .catch(error => console.error('Error:', error));
    },
    init() {
        this.getAvailableFees();
        this.getUsersNoFees();
    }
}">

    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
        <div class="flex items-center justify-between">
            <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('fees.title') }}
            </h3>
            <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'new-fees-modal')">
                <span>
                    {{ __('academies.academy_create_fees') }}
                </span>
            </x-primary-button>
        </div>
        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white dark:bg-background-900 rounded-lg p-2 flex flex-col">
                <h2 class="text-xl text-primary-500">{{ __('fees.available_fees') }}</h2>
                <p class="text-lg" x-text="availableFees"></p>
            </div>
            <div class="bg-white dark:bg-background-900 rounded-lg p-2">
                <h2 class="text-xl text-primary-500">{{ __('fees.users_no_fees') }}</h2>
                <p class="text-lg" x-text="usersNoFees"></p>
            </div>
        </div>
    </div>

    <x-modal name="new-fees-modal" :show="$errors->get('name') || $errors->get('new_fees_error')" focusable>
        <form method="post" action="{{ route('academies.generate-fees') }}" class="p-6 flex flex-col gap-4"
            x-ref="form">
            @csrf


            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                    {{ __('academies.academy_create_fees') }}
                </h2>
                <div>
                    <x-lucide-x class="w-6 h-6 text-background-500 dark:text-background-300 cursor-pointer"
                        x-on:click="$dispatch('close-modal', 'new-school-modal')" />
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-100 text-red-800 p-4 rounded-lg">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <input type="hidden" name="academy_id" value="{{ $academy }}">

            <x-form.input name="number" label="{{ __('academies.fee_number') }}" type="number" required
                placeholder="{{ __('academies.fee_number') }}" />

            <div class="flex justify-end">
                <x-primary-button x-on:click.prevent="$refs.form.submit()">
                    <span>{{ __('academies.academy_create_fees') }}</span>
                </x-primary-button>
            </div>

        </form>
    </x-modal>
</div>
