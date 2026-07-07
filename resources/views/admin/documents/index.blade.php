<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('navigation.documents') }}
            </h2>
            @if ($isAdmin)
                <x-create-new-button href="{{ route('documents.create') }}" />
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100" x-data="{ selectedDocument: null }">
                    <x-table :columns="[
                        ['name' => 'File name', 'field' => 'original_name'],
                        ['name' => 'Uploaded at', 'field' => 'created_at_formatted', 'sortType' => 'date'],
                        ['name' => 'Author', 'field' => 'author'],
                    ]" :rows="$documents">
                        <x-slot name="tableActions">
                            <div class="flex items-center gap-2">
                                <div class="has-tooltip">
                                    <span
                                        class="tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm -mt-6 -translate-y-full">
                                        Download
                                    </span>
                                    @if ($isAdmin)
                                        <a href="#" x-bind:href="'/documents/' + row.id + '/download'" title="Download"
                                            class="inline-flex items-center justify-center p-2 text-primary-500 dark:text-primary-400 hover:text-background-700 dark:hover:text-primary-300 transition">
                                            <x-lucide-download class="w-5 h-5" />
                                        </a>
                                    @else
                                        <button type="button" title="Download"
                                            x-on:click="
                                                selectedDocument = row;
                                                fetch('/documents/' + row.id + '/terms-viewed', {
                                                    method: 'POST',
                                                    headers: {
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                        'Accept': 'application/json',
                                                    },
                                                });
                                                $dispatch('open-modal', 'document-terms-modal');
                                            "
                                            class="inline-flex items-center justify-center p-2 text-primary-500 dark:text-primary-400 hover:text-background-700 dark:hover:text-primary-300 transition">
                                            <x-lucide-download class="w-5 h-5" />
                                        </button>
                                    @endif
                                </div>
                                @if ($isAdmin)
                                    <form method="POST" x-bind:action="'/documents/' + row.id">
                                        @csrf
                                        @method('DELETE')
                                        <div class="has-tooltip">
                                            <span
                                                class="tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm -mt-6 -translate-y-full">
                                                Delete
                                            </span>
                                            <button type="submit" title="Delete"
                                                class="inline-flex items-center justify-center p-2 text-red-600 hover:text-red-500 transition">
                                                <x-lucide-trash-2 class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </x-slot>
                    </x-table>

                    @unless ($isAdmin)
                        <x-modal name="document-terms-modal" focusable>
                            <div class="p-6">
                                <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                                    Terms of Access
                                </h2>

                                <p class="mt-3 text-sm text-background-600 dark:text-background-300">
                                    Before continuing you must accept the Terms of Access for this document.
                                </p>

                                <a href="" class="mt-4 inline-flex text-sm text-primary-600 dark:text-primary-400 underline">
                                    Download Terms of Access
                                </a>

                                <form method="POST" target="_blank" class="mt-6"
                                    x-on:submit="$dispatch('close-modal', 'document-terms-modal')"
                                    x-bind:action="selectedDocument ? '/documents/' + selectedDocument.id + '/accept-terms' : '#'">
                                    @csrf

                                    <label class="flex items-center gap-2 text-sm text-background-700 dark:text-background-300">
                                        <input type="checkbox" name="terms_accepted" value="1" required
                                            class="rounded border-background-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                        <span>I accept the Terms of Access</span>
                                    </label>

                                    <div class="mt-6 flex justify-end gap-3">
                                        <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'document-terms-modal')">
                                            Cancel
                                        </x-secondary-button>
                                        <x-primary-button>
                                            Continue
                                        </x-primary-button>
                                    </div>
                                </form>
                            </div>
                        </x-modal>
                    @endunless
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
