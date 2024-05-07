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
                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class=" flex flex-col gap-4 w-full">
                    @csrf

                    <x-form.input  name="name" label="Name" type="text" required="{{ true }}" value="{{ old('name') }}" />
                    <x-form.input  name="surname" label="Surname" type="text" required="{{ true }}" value="{{ old('surname') }}" />

                </form>
            </div>
        </div>
    </div>

</x-app-layout>