@php
    $authRole = auth()->user()->getRole();
    $actionRoute = $authRole === 'admin' ? 'exports.store' : $authRole . '.exports.store';
@endphp
<form action="{{ route($actionRoute) }}" method="POST" x-data="{
    isSubmitEnabled: false,
    start_date: '',
    end_date: '',
    valuateDates: function() {
        let date_start = new Date(this.start_date);
        let date_end = new Date(this.end_date);

        if (date_end > date_start) {
            this.isSubmitEnabled = true;
            return;
        }

        this.isSubmitEnabled = false;
    },

    start_date_change: function(value) {
        if (value === '') {
            this.isSubmitEnabled = false;
            return;
        }

        this.valuateDates();
    },
    end_date_change: function(value) {
        if (value === '') {
            this.isSubmitEnabled = false;
            return;
        }

        this.valuateDates();
    }

}">
    @csrf

    <p>{{ __('exports.users_filter_message') }}</p>
    <input name="type" type="hidden" value="users">
    <div class="flex items-center gap-2 my-2">
        <div class="flex-1">

            <x-input-label value="Start Date" />
            <input name="start_date" type="date" x-model="start_date" @change="start_date_change()"
                class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />

        </div>
        <div class="flex-1">
            <x-input-label value="End Date" />
            <input name="end_date" type="date" x-model="end_date" @change="end_date_change()"
                class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />

        </div>
    </div>

    <div class="flex justify-end mt-4">
        <button type="submit" :disabled="!isSubmitEnabled"
            class="inline-flex items-center px-4 py-2 bg-primary-800 dark:bg-primary-400 border border-transparent rounded-md font-semibold text-xs text-white dark:text-background-800 uppercase tracking-widest hover:bg-background-700 dark:hover:bg-primary-600 focus:bg-background-700 dark:focus:bg-primary-500 active:bg-background-900 dark:active:bg-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-background-800 transition ease-in-out duration-150 disabled:cursor-not-allowed disabled:pointer-events-none disabled:opacity-60 ">
            {{ __('exports.submit') }}
        </button>
    </div>
</form>
