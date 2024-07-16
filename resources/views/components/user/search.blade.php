@php
    $authRole = auth()->user()->getRole();
    $searchPath = $authRole === 'admin' ? 'users.search' : $authRole . '.users.search';
@endphp
<section class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg mb-3">
    <form action="{{ route($searchPath) }}" method="GET" class="p-6 text-background-900 dark:text-background-100">
        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('users.search_title') }}</h3>
        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

        <div class="flex items-center gap-2">
            <div class="flex-1">
                <input type="search" name="search" value="{{ request()->query('search') }}"
                    class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
            </div>
            <x-primary-button>
                <x-lucide-search class="h-5 w-5 text-white" />
            </x-primary-button>
        </div>
    </form>
</section>
