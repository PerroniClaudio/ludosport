<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('Edit User') }}
            </h2>
        </div>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('users.update', $user->id) }}" class="flex flex-col gap-4">
                @csrf
                
                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('users.personal_details_message') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <div class="w-1/2 flex flex-col gap-2">
                        <x-form.input  name="name" label="Name" type="text" required="{{ true }}" value="{{$user->name }}" placeholder="{{ fake()->firstName() }}"/>
                        <x-form.input  name="surname" label="Surname" type="text" required="{{ true }}" value="{{ $user->surname }}" placeholder="{{ fake()->lastName() }}"/>
                        <x-form.input  name="email" label="Email" type="email" required="{{ true }}" value="{{ $user->email }}" placeholder="{{ fake()->email() }}"/>
                        <x-form.input  name="year" label="Year" type="text" required="{{ true }}" value="{{ $user->subscription_year }}" placeholder="{{ date('Y') }}"/>
                    </div>
                </div>

                <x-user.provenance-selector 
                    nationality="{{ $user->nation_id }}" 
                    selectedAcademyId="{{ $user->academy_id }}" 
                    selectedAcademy="{{ $user->academy->name }}" 
                    selectedSchoolId="{{ $user->school_id }}"
                    selectedSchool="{{ $user->school ? $user->school->name : '' }}"
                    :academies="$academies" 
                    :nations="$nations" 
                    :schools="$schools"
                />

                <div class="fixed bottom-8 right-32">
                    <x-primary-button type="submit">
                        <x-lucide-save class="w-6 h-6 text-white" />
                    </x-primary-button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>