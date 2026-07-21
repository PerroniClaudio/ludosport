<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200">Terms of Access</h2>
            <a href="{{ route('documents.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-background-300 dark:bg-background-800 border border-background-300 dark:border-background-500 rounded-md font-semibold text-xs text-background-700 dark:text-background-300 uppercase tracking-widest shadow-sm hover:bg-background-50 dark:hover:bg-background-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-background-800 transition ease-in-out duration-150">
                <x-lucide-arrow-left class="w-4 h-4" />
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="POST" action="{{ route('documents.terms.content.store') }}"
                class="terms-editor bg-white dark:bg-background-800 shadow-sm sm:rounded-lg p-6 space-y-6">
                @csrf
                <x-rich-text-editor name="content" label="HTML content" :value="old('content', $content)" required />
                <div class="flex justify-end">
                    <x-primary-button>Save HTML</x-primary-button>
                </div>
            </form>

            <form method="POST" action="{{ route('documents.terms.store') }}" enctype="multipart/form-data"
                class="bg-white dark:bg-background-800 shadow-sm sm:rounded-lg p-6 space-y-6" x-data="{ fileName: '' }">
                @csrf
                <div>
                    <x-input-label for="terms" value="Terms of Access PDF" />
                    <input id="terms" name="terms" type="file" accept="application/pdf,.pdf" class="hidden" x-ref="termsInput"
                        x-on:change="fileName = $event.target.files[0]?.name || ''">
                    <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <x-secondary-button type="button" x-on:click="$refs.termsInput.click()">Select PDF</x-secondary-button>
                            <span class="text-sm text-background-500" x-text="fileName || 'No file selected'"></span>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <x-danger-button type="button" x-show="fileName" x-cloak
                                x-on:click="$refs.termsInput.value = ''; fileName = ''">
                                Cancel
                            </x-danger-button>
                            <x-primary-button>Upload new version</x-primary-button>
                        </div>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('terms')" />
                </div>

                <div>
                    <h3 class="font-semibold text-background-900 dark:text-background-100">Uploaded versions</h3>
                    <div class="mt-4 divide-y divide-background-200 dark:divide-background-700">
                        @forelse ($terms as $term)
                            <div class="py-3 flex justify-between gap-4 text-sm">
                                <span>V{{ $term->version }} — {{ $term->original_name }}</span>
                                <span class="text-background-500">{{ $term->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @empty
                            <p class="text-background-500">No versions uploaded.</p>
                        @endforelse
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        .terms-editor .ProseMirror {
            min-height: 32rem;
        }
    </style>
</x-app-layout>
