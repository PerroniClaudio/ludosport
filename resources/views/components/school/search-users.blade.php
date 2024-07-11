@props([
    'school' => [],
    'roles' => [],
])

<form class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8" x-data="{
    selectedRoles: [],
    selectedRolesJson: '',
    showExpandedFilter: false,
    selectRole(role) {
        if (this.selectedRoles.includes(role)) {
            this.selectedRoles = this.selectedRoles.filter(item => item !== role);
        } else {
            this.selectedRoles.push(role);
        }

        this.selectedRolesJson = JSON.stringify(this.selectedRoles);
    }

}"
    method="GET" action="{{ route('schools.users-search', $school->id) }}">
    <div class="flex justify-between">
        <h3 class="text-background-800 dark:text-background-200 text-2xl">
            {{ __('users.filter_title') }}
        </h3>
        <div>
            <x-primary-button type="button" x-on:click="showExpandedFilter = !showExpandedFilter">
                <x-lucide-filter class="w-6 h-6 text-white cursor-pointer" />
            </x-primary-button>

        </div>
    </div>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

    <x-form.input name="search" label="Search" type="text" placeholder="Search" />

    <div class="flex flex-col gap-4 mt-4" x-show="showExpandedFilter">

        <div>
            <h3 class="text-background-800 dark:text-background-200 text-md">
                {{ __('users.filter_by_creation_date') }}
            </h3>
            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>


            <div class="flex w-full items-center gap-2">
                <div class="flex-1">
                    <x-form.input name="from" label="From" type="date" placeholder="{{ date('Y') }}" />
                </div>

                <div class="flex-1">
                    <x-form.input name="to" label="To" type="date" placeholder="{{ date('Y') }}" />
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-background-800 dark:text-background-200 text-md">
                {{ __('users.filter_by_subscription_year') }}
            </h3>
            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
            <div class="flex flex-col w-1/2">
                <x-form.input name="year" label="Subscription year" type="number"
                    placeholder="{{ date('Y') }}" />
            </div>
        </div>

        <div>
            <h3 class="text-background-800 dark:text-background-200 text-md">
                {{ __('users.role') }}
            </h3>
            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
            <div class="grid grid-cols-3 gap-4 text-background-800 dark:text-background-200">
                @foreach ($roles as $role)
                    @if ($role->label === 'admin' || $role->label === 'rector')
                        @continue
                    @endif

                    <div x-on:click="selectRole('{{ $role->id }}')"
                        class="border border-background-700 hover:border-primary-500 rounded-lg p-4 cursor-pointer flex items-center gap-2"
                        :class="{ 'border-primary-500': selectedRoles.includes('{{ $role->id }}') }">

                        @switch($role->label)
                            @case('admin')
                                <x-lucide-crown class="w-6 h-6 text-primary-500" />
                            @break

                            @case('athlete')
                                <x-lucide-swords class="w-6 h-6 text-primary-500" />
                            @break

                            @case('rector')
                                <x-lucide-graduation-cap class="w-6 h-6 text-primary-500" />
                            @break

                            @case('dean')
                                <x-lucide-book-marked class="w-6 h-6 text-primary-500" />
                            @break

                            @case('manager')
                                <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                            @break

                            @case('technician')
                                <x-lucide-wrench class="w-6 h-6 text-primary-500" />
                            @break

                            @case('instructor')
                                <x-lucide-megaphone class="w-6 h-6 text-primary-500" />
                            @break

                            @default
                        @endswitch

                        <span>{{ __("users.{$role->label}") }}</span>
                    </div>
                @endforeach
            </div>
            <input type="hidden" name="roles" x-model="selectedRolesJson">
        </div>

        <input type="hidden" name="filters_enabled" x-model="showExpandedFilter">

    </div>

    <div class="flex justify-end mt-4">
        <x-primary-button type="submit">
            {{ __('users.filter_title') }}
        </x-primary-button>
    </div>
</form>
