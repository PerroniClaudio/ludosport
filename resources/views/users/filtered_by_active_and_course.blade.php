@php
    $authUser = auth()->user();
    $authRole = $authUser->getRole();
    $pathPrefix = $authRole === 'admin' ? '' : '/' . $authRole;
    $urlName = 'users.filtered-by-active-and-course'
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                @php
                    $active = request()->query('active');
                    $clans = request()->query('clans');
                @endphp

                @if ($active == 'active')
                    @if ($clans == 'with')
                        {{ __('users.title_active_with_clans') }}
                    @else
                        {{ __('users.title_active_without_clans') }}
                    @endif
                @else
                    @if ($clans == 'with')
                        {{ __('users.title_inactive_with_clans') }}
                    @else
                        {{ __('users.title_inactive_without_clans') }}
                    @endif
                @endif
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12">
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-background-900 dark:text-background-100">
                            <div
                                class="mb-5 overflow-x-auto bg-white dark:bg-background-900 rounded-lg shadow overflow-y-auto relative p-6">

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
                                                    <x-sortable-header field="name" :urlName="$urlName"
                                                        class="px-6 w-[200px] min-w-[200px] max-w[200px]"
                                                        :noHeaderTag="true">
                                                        {{ __('users.name') }}
                                                    </x-sortable-header>
                                                    <x-sortable-header field="surname" :urlName="$urlName"
                                                        class="px-6 w-[200px] min-w-[200px] max-w[200px]"
                                                        :noHeaderTag="true">
                                                        {{ __('users.surname') }}
                                                    </x-sortable-header>
                                                </th>

                                                {{-- Actions column always present --}}
                                                <th
                                                    class="xl:hidden w-[70px] min-w-[70px] max-w[70px] sticky left-0 z-30 bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                    {{ __('users.actions') }}
                                                </th>

                                                {{-- Name column always present --}}
                                                <x-sortable-header field="name" :urlName="$urlName" class="xl:hidden">
                                                    {{ __('users.name') }}
                                                </x-sortable-header>

                                                {{-- Surname column for athletes --}}
                                                <x-sortable-header field="surname" :urlName="$urlName"
                                                    class="xl:hidden">
                                                    {{ __('users.surname') }}
                                                </x-sortable-header>
                                                {{-- Email column for athletes --}}
                                                <x-sortable-header field="email" :urlName="$urlName">
                                                    {{ __('users.email') }}
                                                </x-sortable-header>
                                                {{-- Year column for athletes --}}
                                                <x-sortable-header field="subscription_year" :urlName="$urlName">
                                                    {{ __('users.subscription_year') }}
                                                </x-sortable-header>
                                                {{-- Nation column for athletes --}}
                                                <x-sortable-header field="nation" :urlName="$urlName">
                                                    {{ __('users.nation') }}
                                                </x-sortable-header>
                                                {{-- Academy column for athletes --}}
                                                <x-sortable-header field="academy" :urlName="$urlName">
                                                    {{ __('users.academy') }}
                                                </x-sortable-header>
                                                {{-- School column for athletes --}}
                                                <x-sortable-header field="school" :urlName="$urlName">
                                                    {{ __('users.school') }}
                                                </x-sortable-header>
                                                {{-- Fee column for athletes --}}
                                                <x-sortable-header field="has_paid_fee" :urlName="$urlName">
                                                    {{ __('users.fee_paid') }}
                                                </x-sortable-header>

                                                {{-- ID column always present --}}
                                                <x-sortable-header field="id" :urlName="$urlName">
                                                    ID
                                                </x-sortable-header>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($users as $user)
                                                <tr class="bg-white dark:bg-background-900">


                                                    {{-- Actions + Name + Surname on Desktop column always present --}}
                                                    <td
                                                        class="hidden xl:flex text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700
                                                            sticky left-0 z-30 bg-white dark:bg-background-900">
                                                        <a href="{{ $pathPrefix }}/users/{{ $user->id }}"
                                                            class="w-[70px] min-w-[70px] max-w[70px]">
                                                            <x-lucide-pencil
                                                                class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                                        </a>
                                                        <div class="px-6 w-[200px] min-w-[200px] max-w[200px]">
                                                            {{ $user->name }}
                                                        </div>
                                                        <div class="px-6 w-[200px] min-w-[200px] max-w[200px]">
                                                            {{ $user->surname }}
                                                        </div>
                                                    </td>

                                                    {{-- Actions column always present --}}
                                                    <td
                                                        class="xl:hidden text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap
                                                            w-[70px] min-w-[70px] max-w[70px] sticky left-0 z-30 bg-white dark:bg-background-900">
                                                        <a href="{{ $pathPrefix }}/users/{{ $user->id }}">
                                                            <x-lucide-pencil
                                                                class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                                        </a>
                                                    </td>
                                                    {{-- Name column always present --}}
                                                    <td
                                                        class="xl:hidden text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                                        {{ $user->name }}
                                                    </td>

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
