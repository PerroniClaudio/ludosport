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
                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-3 gap-4"">
                    @csrf

                    <div>
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('users.personal_details_message') }}</h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-4"></div>
                        <x-form.input  name="name" label="Name" type="text" required="{{ true }}" value="{{ old('name') }}" />
                        <x-form.input  name="surname" label="Surname" type="text" required="{{ true }}" value="{{ old('surname') }}" />
                        <x-form.input  name="email" label="Email" type="email" required="{{ true }}" value="{{ old('email') }}" />
                        <small class="text-background-800 dark:text-background-200"><i>{{ __('users.password_creation_message') }}</i></small>
                    </div>

                    <div>
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('users.provenance') }}</h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-4"></div>
                        <x-form.nationality-select />
                        
                    </div>

                </form>
            </div>
        </div>
    </div>

</x-app-layout>