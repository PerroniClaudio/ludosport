<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-background-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 my-4"
                x-data="{}">
                <div class="flex justify-between">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('users.profile_picture') }}
                    </h3>
                    <div>
                        <form method="POST" action="{{ route('users.update-pfp', $user->id) }}"
                            enctype="multipart/form-data" x-ref="pfpform">
                            @csrf
                            @method('PUT')

                            <div class="flex flex-col gap-4">
                                <div class="flex flex-col gap-2">
                                    <input type="file" name="profilepicture" id="profilepicture" class="hidden"
                                        x-on:change="$refs.pfpform.submit()" />
                                    <x-primary-button type="button"
                                        onclick="document.getElementById('profilepicture').click()">
                                        {{ __('users.upload_picture') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                @if ($user->profile_picture)
                    <img src="{{ route('user.profile-picture-show', $user->id) }}" alt="{{ $user->name }}"
                        class="w-1/3 rounded-lg">
                @endif

            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-background-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-background-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
