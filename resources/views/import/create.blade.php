<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('imports.new') }}
            </h2>

        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <form method="POST" action="{{ route('imports.store') }}" enctype="multipart/form-data"
                        x-data="{
                            selectedType: null,
                            hasSubmittedFile: false,
                            downloadTemplate: function() {
                                if (this.selectedType != null) {
                                    window.location.href = '/imports/template?type=' + this.selectedType;
                                }
                            }
                        }">
                        @csrf
                        <div class="flex flex-col gap-2 w-1/2">
                            <x-form.select name="type" label="Type" required="{{ true }}"
                                :options="$types" x-model="selectedType" shouldHaveEmptyOption="true" />

                            <a x-show="selectedType != null" class="w-full" x-on:click="downloadTemplate()">
                                <x-primary-button type="button" class="w-full">
                                    {{ __('imports.download_template') }}
                                </x-primary-button>
                            </a>


                            <input type="file" name="file" id="import-file" class="hidden" accept=".xlsx, .xls"
                                x-on:change="hasSubmittedFile = true" />
                            <x-primary-button type="button" onclick="document.getElementById('import-file').click()">
                                {{ __('imports.choose_file') }}
                            </x-primary-button>

                            <div class="flex justify-end mt-4">
                                <button type="submit" :disabled="!hasSubmittedFile"
                                    class="inline-flex items-center px-4 py-2 bg-primary-800 dark:bg-primary-400 border border-transparent rounded-md font-semibold text-xs text-white dark:text-background-800 uppercase tracking-widest hover:bg-background-700 dark:hover:bg-primary-600 focus:bg-background-700 dark:focus:bg-primary-500 active:bg-background-900 dark:active:bg-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-background-800 transition ease-in-out duration-150 disabled:cursor-not-allowed disabled:pointer-events-none disabled:opacity-60 ">
                                    {{ __('imports.submit') }}
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
