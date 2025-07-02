<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('users.title') }}
            </h2>
            <div>
                <x-create-new-button :href="route('users.create')" />
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <x-user.search />

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-3">
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-background-900 dark:text-background-100">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                {{ __('users.roles') }}
                            </h3>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            <div class="flex flex-col gap-2">
                                @foreach ($roles as $role)
                                    <a href="{{ $authUserRole === 'admin' ? route('users.index', ['role' => $role->label]) : route($authUserRole . '.users.index', ['role' => $role->label]) }}"
                                        class="hover:text-background-800 dark:hover:text-background-300 focus:outline-none focus:text-primary-600 dark:focus:text-primary-600 text-left {{ $selectedRole === $role->label ? 'text-primary-600 dark:text-primary-400' : 'text-background-600 dark:text-background-400' }}">
                                        {{ __('users.' . $role->label . '_role') }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 md:col-span-9">
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-background-900 dark:text-background-100">
                            <div
                                class="mb-5 overflow-x-auto bg-white dark:bg-background-900 rounded-lg shadow overflow-y-auto relative p-6">

                                <div class="flex justify-between items-center">
                                    <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                        {{ __('users.' . $selectedRole . '_role') }}
                                    </h3>
                                    @if ($selectedRole == 'athlete')
                                        <div>
                                            <a href="{{ route('users.filter') }}">
                                                <x-primary-button>
                                                    {{ __('users.filter_by') }}
                                                </x-primary-button>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                                <div class="overflow-x-auto my-4">
                                    <table
                                        class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative">
                                        <thead>
                                            <tr class="text-left">

                                                {{-- Actions + Name + Surname on Desktop column always present --}}
                                                <th
                                                    class="hidden xl:flex sticky left-0 z-30 bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                    <div class="w-[70px] min-w-[70px] max-w[70px]">
                                                        {{ __('users.actions') }}
                                                    </div>
                                                    <x-sortable-header field="name" :selectedRole="$selectedRole"
                                                        class="px-6 w-[200px] min-w-[200px] max-w[200px]"
                                                        :noHeaderTag="true">
                                                        {{ __('users.name') }}
                                                    </x-sortable-header>
                                                    @if ($selectedRole == 'athlete')
                                                        <!-- <div class="px-6 w-[200px] min-w-[200px] max-w[200px]">
                                                            {{ __('users.surname') }}
                                                        </div> -->
                                                        <x-sortable-header field="surname" :selectedRole="$selectedRole"
                                                            class="px-6 w-[200px] min-w-[200px] max-w[200px]"
                                                            :noHeaderTag="true">
                                                            {{ __('users.surname') }}
                                                        </x-sortable-header>
                                                    @endif
                                                </th>

                                                {{-- Actions column always present --}}
                                                <th
                                                    class="xl:hidden w-[70px] min-w-[70px] max-w[70px] sticky left-0 z-30 bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                    {{ __('users.actions') }}
                                                </th>

                                                {{-- Name column always present --}}
                                                <x-sortable-header field="name" :selectedRole="$selectedRole" class="xl:hidden">
                                                    {{ __('users.name') }}
                                                </x-sortable-header>

                                                @if ($selectedRole == 'athlete')
                                                    {{-- Surname column for athletes --}}
                                                    <x-sortable-header field="surname" :selectedRole="$selectedRole"
                                                        class="xl:hidden">
                                                        {{ __('users.surname') }}
                                                    </x-sortable-header>
                                                    {{-- Email column for athletes --}}
                                                    <x-sortable-header field="email" :selectedRole="$selectedRole">
                                                        {{ __('users.email') }}
                                                    </x-sortable-header>
                                                    {{-- Year column for athletes --}}
                                                    <x-sortable-header field="subscription_year" :selectedRole="$selectedRole">
                                                        {{ __('users.subscription_year') }}
                                                    </x-sortable-header>
                                                    {{-- Nation column for athletes --}}
                                                    <x-sortable-header field="nation" :selectedRole="$selectedRole">
                                                        {{ __('users.nation') }}
                                                    </x-sortable-header>
                                                    {{-- Academy column for athletes --}}
                                                    <x-sortable-header field="academy" :selectedRole="$selectedRole">
                                                        {{ __('users.academy') }}
                                                    </x-sortable-header>
                                                    {{-- School column for athletes --}}
                                                    <x-sortable-header field="school" :selectedRole="$selectedRole">
                                                        {{ __('users.school') }}
                                                    </x-sortable-header>
                                                    {{-- Fee column for athletes --}}
                                                    <x-sortable-header field="has_paid_fee" :selectedRole="$selectedRole">
                                                        {{ __('users.fee_paid') }}
                                                    </x-sortable-header>
                                                @elseif ($selectedRole == 'instructor')
                                                    {{-- Email column for instructors --}}
                                                    <x-sortable-header field="email" :selectedRole="$selectedRole">
                                                        {{ __('users.email') }}
                                                    </x-sortable-header>
                                                    {{-- Weapon Forms column for instructors --}}
                                                    <x-sortable-header field="weapon_forms" :selectedRole="$selectedRole">
                                                        {{ __('users.weapons_forms') }}
                                                    </x-sortable-header>
                                                @elseif ($selectedRole == 'technician')
                                                    {{-- Email column for technicians --}}
                                                    <x-sortable-header field="email" :selectedRole="$selectedRole">
                                                        {{ __('users.email') }}
                                                    </x-sortable-header>
                                                    {{-- Weapon Forms column for technicians --}}
                                                    <x-sortable-header field="weapon_forms" :selectedRole="$selectedRole">
                                                        {{ __('users.weapons_forms') }}
                                                    </x-sortable-header>
                                                @elseif ($selectedRole == 'rector')
                                                    {{-- Email column for rectors --}}
                                                    <x-sortable-header field="email" :selectedRole="$selectedRole">
                                                        {{ __('users.email') }}
                                                    </x-sortable-header>
                                                    {{-- Academy column for rectors --}}
                                                    <x-sortable-header field="academy" :selectedRole="$selectedRole">
                                                        {{ __('users.academy') }}
                                                    </x-sortable-header>
                                                @elseif ($selectedRole == 'dean')
                                                    {{-- Email column for deans --}}
                                                    <x-sortable-header field="email" :selectedRole="$selectedRole">
                                                        {{ __('users.email') }}
                                                    </x-sortable-header>
                                                    {{-- School column for deans --}}
                                                    <x-sortable-header field="school" :selectedRole="$selectedRole">
                                                        {{ __('users.school') }}
                                                    </x-sortable-header>
                                                @else
                                                    {{-- Email column for other roles --}}
                                                    <x-sortable-header field="email" :selectedRole="$selectedRole">
                                                        {{ __('users.email') }}
                                                    </x-sortable-header>
                                                @endif

                                                {{-- ID column always present --}}
                                                <x-sortable-header field="id" :selectedRole="$selectedRole">
                                                    ID
                                                </x-sortable-header>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($users as $user)
                                                <tr class="bg-white dark:bg-background-900">

                                                    <!-- {{-- Actions + Name + Surname on Desktop column always present --}}
                                                    <th
                                                        class="hidden xl:flex sticky left-0 z-30 bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate"
                                                    >
                                                        <div class="w-[70px] min-w-[70px] max-w[70px]">
                                                            {{ __('users.actions') }}
                                                        </div>
                                                        <div class="px-6 w-[200px] min-w-[200px] max-w[200px]">
                                                            {{ __('users.name') }}
                                                        </div>
                                                        @if ($selectedRole == 'athlete')
<div class="px-6 w-[200px] min-w-[200px] max-w[200px]">
                                                                {{ __('users.surname') }}
                                                            </div>
@endif
                                                    </th> -->

                                                    {{-- Actions + Name + Surname on Desktop column always present --}}
                                                    <td
                                                        class="hidden xl:flex text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700
                                                            sticky left-0 z-30 bg-white dark:bg-background-900">
                                                        <a href="/users/{{ $user->id }}"
                                                            class="w-[70px] min-w-[70px] max-w[70px]">
                                                            <x-lucide-pencil
                                                                class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                                        </a>
                                                        <div class="px-6 w-[200px] min-w-[200px] max-w[200px]">
                                                            {{ $user->name }}
                                                            @if ($selectedRole != 'athlete')
                                                                {{ $user->surname }}
                                                            @endif
                                                        </div>
                                                        @if ($selectedRole == 'athlete')
                                                            <div class="px-6 w-[200px] min-w-[200px] max-w[200px]">
                                                                {{ $user->surname }}
                                                            </div>
                                                        @endif
                                                    </td>

                                                    {{-- Actions column always present --}}
                                                    <td
                                                        class="xl:hidden text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap
                                                            w-[70px] min-w-[70px] max-w[70px] sticky left-0 z-30 bg-white dark:bg-background-900">
                                                        <a href="/users/{{ $user->id }}">
                                                            <x-lucide-pencil
                                                                class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                                        </a>
                                                    </td>
                                                    {{-- Name column always present --}}
                                                    <td
                                                        class="xl:hidden text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                        {{ $user->name }}
                                                        @if ($selectedRole != 'athlete')
                                                            {{ $user->surname }}
                                                        @endif
                                                    </td>

                                                    @if ($selectedRole == 'athlete')
                                                        {{-- Surname column for athletes --}}
                                                        <td
                                                            class="xl:hidden text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                            {{ $user->surname }}
                                                        </td>
                                                        {{-- Email column for athletes --}}
                                                        <td
                                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                            {{ $user->email }}
                                                        </td>
                                                        {{-- Year column for athletes --}}
                                                        <td
                                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                            {{ $user->subscription_year }}
                                                        </td>
                                                        {{-- Nation column for athletes --}}
                                                        <td
                                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                            {{ $user->nation }}
                                                        </td>
                                                        {{-- Academy column for athletes --}}
                                                        <td
                                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                            {{ $user->primaryAcademyAthlete() ? $user->primaryAcademyAthlete()->name : __('users.no_academy') }}
                                                        </td>
                                                        {{-- School column for athletes --}}
                                                        <td
                                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                            {{ $user->primarySchoolAthlete() ? $user->primarySchoolAthlete()->name : __('users.no_school') }}
                                                        </td>
                                                        {{-- Fee column for athletes --}}
                                                        <td
                                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                            @if ($user->has_paid_fee)
                                                                <x-lucide-badge-check
                                                                    class="w-5 h-5 text-primary-800 dark:text-primary-500" />
                                                            @else
                                                                <x-lucide-badge-info
                                                                    class="w-5 h-5 text-red-800 dark:text-red-500" />
                                                            @endif
                                                        </td>
                                                    @elseif ($selectedRole == 'instructor')
                                                        {{-- Email column for instructors --}}
                                                        <td
                                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                            {{ $user->email }}
                                                        </td>
                                                        {{-- Weapon Forms column for instructors --}}
                                                        <td
                                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">

                                                            @if ($user->weapon_forms_instructor_formatted)
                                                                {{ implode(', ', $user->weapon_forms_instructor_formatted) }}
                                                            @endif
                                                        </td>
                                                    @elseif ($selectedRole == 'technician')
                                                        {{-- Email column for technicians --}}
                                                        <td
                                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                            {{ $user->email }}
                                                        </td>
                                                        {{-- Weapon Forms column for technicians --}}
                                                        <td
                                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                            @if ($user->weapon_forms_technician_formatted)
                                                                {{ implode(', ', $user->weapon_forms_technician_formatted) }}
                                                            @endif
                                                        </td>
                                                    @elseif ($selectedRole == 'rector')
                                                        {{-- Email column for rectors --}}
                                                        <td
                                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                            {{ $user->email }}
                                                        </td>
                                                        {{-- Academy column for rectors --}}
                                                        <td
                                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                            {{ $user->primary_academy ?? '' }}
                                                        </td>
                                                    @elseif ($selectedRole == 'dean')
                                                        {{-- Email column for deans --}}
                                                        <td
                                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                            {{ $user->email }}
                                                        </td>
                                                        {{-- School column for deans --}}
                                                        <td
                                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                            {{ $user->primary_school ?? '' }}
                                                        </td>
                                                    @else
                                                        {{-- Email column for other roles --}}
                                                        <td
                                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                            {{ $user->email }}
                                                        </td>
                                                    @endif

                                                    {{-- ID column always present --}}
                                                    <td
                                                        class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                        {{ $user->id }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{ $users->links() }}

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>
