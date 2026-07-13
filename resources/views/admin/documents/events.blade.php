<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                Document event logs
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="GET" class="p-6 grid gap-4 md:grid-cols-3">
                    @php($filterClass = 'rounded border-background-300 bg-white text-background-900 placeholder:text-background-500 dark:border-background-500 dark:bg-background-900 dark:text-background-100 dark:placeholder:text-background-400')

                    <select name="user_id" class="{{ $filterClass }}">
                        <option value="">All users</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>
                                {{ trim($user->name.' '.$user->surname) }} - {{ $user->email }}
                            </option>
                        @endforeach
                    </select>

                    <select name="document_id" class="{{ $filterClass }}">
                        <option value="">All documents</option>
                        @foreach ($documents as $document)
                            <option value="{{ $document->id }}" @selected(($filters['document_id'] ?? '') == $document->id)>
                                {{ $document->original_name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="event_type" class="{{ $filterClass }}">
                        <option value="">All events</option>
                        @foreach ($eventTypes as $type => $label)
                            <option value="{{ $type }}" @selected(($filters['event_type'] ?? '') === $type)>{{ $label }}</option>
                        @endforeach
                    </select>

                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                        class="{{ $filterClass }}">
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                        class="{{ $filterClass }}">

                    <select name="operation_result" class="{{ $filterClass }}">
                        <option value="">All results</option>
                        @foreach ($results as $result => $label)
                            <option value="{{ $result }}" @selected(($filters['operation_result'] ?? '') === $result)>{{ $label }}</option>
                        @endforeach
                    </select>

                    <div class="md:col-span-3 flex gap-3">
                        <x-primary-button>Search</x-primary-button>
                        <a href="{{ route('documents.events') }}"
                            class="inline-flex items-center px-4 py-2 bg-white dark:bg-background-800 border border-background-300 dark:border-background-500 rounded-md font-semibold text-xs text-background-700 dark:text-background-300 uppercase tracking-widest shadow-sm hover:bg-background-50 dark:hover:bg-background-700">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <x-table striped="false" :columns="[
                        ['name' => 'Date', 'field' => 'created_at_formatted', 'sortType' => 'date'],
                        ['name' => 'User', 'field' => 'user_name'],
                        ['name' => 'Email', 'field' => 'user_email'],
                        ['name' => 'Document', 'field' => 'document_name'],
                        ['name' => 'Terms', 'field' => 'terms_version'],
                        ['name' => 'Event', 'field' => 'event_type'],
                        ['name' => 'Result', 'field' => 'operation_result'],
                        ['name' => 'IP', 'field' => 'ip_address'],
                    ]" :rows="$events" />

                    <div class="mt-4 flex justify-end">
                        <a href="{{ route('documents.events.export', request()->query()) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-background-800 transition ease-in-out duration-150">
                            <x-lucide-download class="w-4 h-4" />
                            Export
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
