<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('ranks.requests_new_title') }}
            </h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">

                    <div class="flex flex-col w-1/2 gap-2" x-data="{
                        needle: '',
                        users: [],
                        user: null,
                        searchUsers: async function() {
                            const response = await fetch('/users-select?search=' + this.needle);
                            const data = await response.json();
                            this.users = data;
                        },
                        test: function() {
                            console.log(this.user);
                        }
                    }">
                        @if ($users->count() > 1)
                            <div class="flex items-end">
                                <div class="flex-1">
                                    <x-form.input-model name="needle" label="Search by name" type="search"
                                        required="{{ true }}" value="{{ old('name') }}"
                                        placeholder="{!! fake()->firstName() !!}" />
                                </div>
                                <x-primary-button class="ml-2" type="button" @click="searchUsers">
                                    <x-lucide-search class="w-6 h-6 text-white" />
                                </x-primary-button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('users.rank.request.create') }}">

                            @csrf

                            @if ($users->count() > 1)
                                <div x-show="users.length > 0">
                                    <x-input-label for="user" value="User to promote" />
                                    <select name="user_to_promote_id" id="user"
                                        class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm"
                                        x-model="user">
                                        <option value="" selected>{{ __('Select an option') }}</option>
                                        <template x-for="user in users" :key="user.id">
                                            <option x-text="user.name + ' ' + user.surname" :value="user.id">
                                            </option>
                                        </template>
                                    </select>
                                    <x-input-error :messages="$errors->get('user_to_promote_id')" class="mt-2" />

                                </div>
                            @elseif ($users->count() == 1)
                                <p>{{ __('users.request_rank_promoting', [
                                    'user' => $users->first()->name . ' ' . $users->first()->surname,
                                ]) }}
                                </p>
                                <input type="hidden" name="user_to_promote_id" value="{{ $users->first()->id }}" />
                            @endif

                            <x-form.select name="rank_id" label="Rank" required="{{ true }}"
                                shouldHaveEmptyOption="true" :options="$ranks" />

                            <x-form.textarea name="reason" label="Reason" required="{{ true }}"
                                value="{{ old('reason') }}"
                                placeholder="{{ __('ranks.requests_rank_promotion_reason_default_text') }}" />

                            <x-primary-button type="submit" @click="test">
                                {{ __('ranks.requests_new_submit') }}
                            </x-primary-button>

                        </form>


                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
