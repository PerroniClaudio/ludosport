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
                    @if ($isAdmin)
                        <div class="mb-6 grid gap-4 md:grid-cols-2">
                            <form method="POST" action="{{ route('documents.terms.store') }}" enctype="multipart/form-data"
                                class="rounded border border-background-200 dark:border-background-700 p-4"
                                x-data="{ uploading: false }">
                                @csrf
                                <h3 class="font-semibold text-background-900 dark:text-background-100">Upload terms of service</h3>
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <x-input-label for="terms" value="Terms of Service PDF" />
                                        <input id="terms" name="terms" type="file" accept="application/pdf,.pdf"
                                            class="hidden" x-ref="termsInput"
                                            x-on:change="if ($event.target.files.length) { uploading = true; $root.submit(); }">
                                        <x-input-error class="mt-2" :messages="$errors->get('terms')" />
                                    </div>
                                    <x-primary-button type="button" x-on:click="$refs.termsInput.click()" x-bind:disabled="uploading">
                                        <span x-text="uploading ? 'Uploading...' : 'Upload'"></span>
                                    </x-primary-button>
                                </div>
                                @if ($latestTerms)
                                    <p class="mt-3 text-sm text-background-600 dark:text-background-300">
                                        Latest: V{{ $latestTerms->version }} - {{ $latestTerms->original_name }}
                                    </p>
                                @endif
                            </form>

                            <div class="rounded border border-background-200 dark:border-background-700 p-4">
                                <h3 class="font-semibold text-background-900 dark:text-background-100">Terms versions</h3>
                                <div class="mt-3 space-y-2 text-sm">
                                    @forelse ($terms as $term)
                                        <div class="flex justify-between gap-3">
                                            <span>V{{ $term->version }} - {{ $term->original_name }}</span>
                                            <span class="text-background-500">{{ $term->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @empty
                                        <p class="text-background-500">No terms uploaded.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 flex justify-end">
                            <a href="{{ route('documents.events') }}"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-background-800 transition ease-in-out duration-150">
                                <x-lucide-list class="w-4 h-4" />
                                Event logs
                            </a>
                        </div>
                    @endif

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

                                <a href="{{ $latestTerms ? route('documents.terms.download') : '#' }}" class="mt-4 inline-flex text-sm text-primary-600 dark:text-primary-400 underline">
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
