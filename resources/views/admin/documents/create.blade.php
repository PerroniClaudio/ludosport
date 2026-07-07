<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('Upload a PDF Document') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data"
                        class="space-y-6" x-data="{ fileName: '' }">
                        @csrf

                        <div>
                            <x-input-label for="document" value="{{ __('PDF') }}" />
                            <input id="document" name="document" type="file" accept="application/pdf,.pdf"
                                class="hidden" x-ref="documentInput"
                                x-on:change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                            <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center">
                                <x-secondary-button type="button" x-on:click="$refs.documentInput.click()">
                                    Select PDF
                                </x-secondary-button>
                                <span class="text-sm text-background-500 dark:text-background-300"
                                    x-text="fileName || 'No file selected'"></span>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('document')" />
                        </div>

                        <div>
                            <x-input-label value="{{ __('Watermark information') }}" />
                            <div class="mt-2 space-y-2">
                                @foreach ($watermarkFields as $value => $label)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="watermark_fields[]" value="{{ $value }}"
                                            class="rounded border-background-300 text-primary-600 shadow-sm focus:ring-primary-500"
                                            @checked(in_array($value, old('watermark_fields', array_keys($watermarkFields))))>
                                        <span>{{ __($label) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('watermark_fields')" />
                        </div>

                        <x-form.select name="watermark_side" label="{{ __('Watermark side') }}"
                            :options="[
                                ['value' => 'left', 'label' => __('Left margin')],
                                ['value' => 'right', 'label' => __('Right margin')],
                            ]" :value="old('watermark_side', 'left')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('watermark_side')" />

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('documents.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-white dark:bg-background-800 border border-background-300 dark:border-background-500 rounded-md font-semibold text-xs text-background-700 dark:text-background-300 uppercase tracking-widest shadow-sm hover:bg-background-50 dark:hover:bg-background-700">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>{{ __('Upload') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
