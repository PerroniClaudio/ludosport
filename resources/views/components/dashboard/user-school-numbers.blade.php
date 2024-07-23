@props(['schoolId' => 1])
@php
    $authRole = auth()->user()->getRole();
    $addToRoute = $authRole === 'admin' ? '' : '/' . $authRole;
@endphp

<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8" x-data="{
    schoolId: {{ $schoolId }},
    active_users: 0,
    active_users_no_course: 0,
    users_course_not_active: 0,
    new_users_this_year: 0,
    fetchData() {
        fetch(`{{$addToRoute}}/schools/${this.schoolId}/athletes-data`)
            .then(response => response.json())
            .then(data => {
                this.active_users = data.active_users;
                this.active_users_no_course = data.active_users_no_course;
                this.users_course_not_active = data.users_course_not_active;
                this.new_users_this_year = data.new_users_this_year;
            });
    },
    init() {
        this.fetchData();
    }

}">
    <h3 class="text-background-800 dark:text-background-200 text-2xl">
        {{ __('dashboard.rector_user_stats') }}
    </h3>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

    <div class="grid grid-cols-4 gap-4">
        <!-- Utenti attivi nella scuola -->

        <div class="p-4 bg-background-100 dark:bg-background-700 rounded-lg">
            <h4 class="text-background-800 dark:text-background-200  xl:text-lg">
                {{ __('dashboard.rector_active_users') }}</h4>
            <p class="text-primary-600 dark:text-primary-500 text-3xl" x-text="active_users"></p>
        </div>

        <!-- Utenti attivi ma non associati a un corso -->

        <div class="p-4 bg-background-100 dark:bg-background-700 rounded-lg">
            <h4 class="text-background-800 dark:text-background-200  xl:text-lg">
                {{ __('dashboard.rector_active_users_no_course') }}</h4>
            <p class="text-primary-600 dark:text-primary-500 text-3xl" x-text="active_users_no_course"></p>
        </div>

        <!-- Utenti associati a un corso ma non attivi -->

        <div class="p-4 bg-background-100 dark:bg-background-700 rounded-lg">
            <h4 class="text-background-800 dark:text-background-200  xl:text-lg">
                {{ __('dashboard.rector_inactive_users') }}</h4>
            <p class="text-primary-600 dark:text-primary-500 text-3xl" x-text="users_course_not_active"></p>
        </div>

        <!-- Utenti iscritti quest'anno per la prima volta -->

        <div class="p-4 bg-background-100 dark:bg-background-700 rounded-lg">
            <h4 class="text-background-800 dark:text-background-200  xl:text-lg">
                {{ __('dashboard.rector_new_users') }}</h4>
            <p class="text-primary-600 dark:text-primary-500 text-3xl" x-text="new_users_this_year"></p>
        </div>
    </div>
</div>
