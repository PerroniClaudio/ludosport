<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('New User') }}
            </h2>

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" >
                    @csrf

                    <div class="grid grid-cols-3 gap-4">

                        <div class="flex flex-col gap-2">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('users.personal_details_message') }}</h3>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            <x-form.input  name="name" label="Name" type="text" required="{{ true }}" value="{{ old('name') }}" placeholder="{{ fake()->firstName() }}"/>
                            <x-form.input  name="surname" label="Surname" type="text" required="{{ true }}" value="{{ old('surname') }}" placeholder="{{ fake()->lastName() }}"/>
                            <x-form.input  name="email" label="Email" type="email" required="{{ true }}" value="{{ old('email') }}" placeholder="{{ fake()->email() }}"/>
                            <x-form.input  name="year" label="Year" type="text" required="{{ true }}" value="{{ old('year') }}" placeholder="{{ date('Y') }}"/>
                            <small class="text-background-800 dark:text-background-200"><i>{{ __('users.password_creation_message') }}</i></small>
                        </div>

                        <div class="flex flex-col gap-2">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('users.provenance') }}</h3>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            <x-form.nationality-select  />
                            <x-form.academy-select :academies="$academies" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('users.authorization') }}</h3>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            <x-form.select name="role" label="Role" required="{{ true }}" :options="$roles" value="{{ old('role') }}" />
                        </div>

                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <x-secondary-button type="button">
                            {{ __('users.cancel') }}
                        </x-secondary-button>
                        <x-primary-button type="sumbit">
                            {{ __('users.create') }}
                        </x-primary-button>
                    </div>


                </form>

                @if($errors->any())
                    <div class="mt-4 text-red-600">
                        <p>
                            {{ $errors->first() }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-app-layout>