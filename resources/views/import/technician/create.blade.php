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
                    <form method="POST" action="{{ route('technician.imports.store') }}" enctype="multipart/form-data"
                        x-data="{
                            selectedType: null,
                            selectedEvent: null,
                            hasSubmittedFile: false,
                            file: null,
                            downloadTemplate: function() {
                                if (this.selectedType != null) {
                                    window.location.href = '/technician/imports/template?type=' + this.selectedType + (this.selectedEvent ? ('&event_id=' + this.selectedEvent) : '');
                                }
                            },
                            handleSubmittedFile: function(e) {
                                this.file = e.target.files[0];
                                this.hasSubmittedFile = true;
                            },
                            init() {
                                this.$watch('selectedType', value => {
                                    this.selectedEvent = null;
                                });
                            }
                        }">
                        @csrf
                        <div class="flex flex-col gap-2 w-1/2">
                            <x-form.select name="type" label="Type" required="{{ true }}"
                                :options="$types" x-model="selectedType" shouldHaveEmptyOption="true" />

                            <template x-if="selectedType == 'event_instructor_results'">
                                <x-form.select name="selectedEvent" label="Event" required="{{ false }}"
                                    :options="$instructorEvents" x-model="selectedEvent" shouldHaveEmptyOption="true" />
                            </template>
                            <template x-if="['event_war', 'event_style'].includes(selectedType)">
                                <x-form.select name="selectedEvent" label="Event" required="{{ false }}"
                                    :options="$rankingEvents" x-model="selectedEvent" shouldHaveEmptyOption="true" />
                            </template>

                            <a x-show="selectedType != null && (!['event_war', 'event_style', 'event_instructor_results'].includes(selectedType) || (selectedEvent != null))"
                                class="w-full" x-on:click="downloadTemplate()">
                                <x-primary-button type="button" class="w-full">
                                    {{ __('imports.download_template') }}
                                </x-primary-button>
                            </a>
                            {{-- <a x-show="selectedType != null" class="w-full" x-on:click="downloadTemplate()">
                                <x-primary-button type="button" class="w-full">
                                    {{ __('imports.download_template') }}
                                </x-primary-button>
                            </a> --}}


                            <div x-show="selectedType != null && (!['event_war', 'event_style', 'event_instructor_results'].includes(selectedType) || (selectedEvent != null))"
                                class="w-full flex flex-col gap-2">

                                <input type="file" name="file" id="import-file" class="hidden"
                                    accept=".xlsx, .xls" x-on:change="handleSubmittedFile($event)" />
                                <x-primary-button type="button" class="w-full"
                                    onclick="document.getElementById('import-file').click()">
                                    {{ __('imports.choose_file') }}
                                </x-primary-button>

                                <div x-show="file != null">
                                    <div
                                        class="border border-primary-500 rounded p-2 text-primary-500 flex gap-1 justify-between">
                                        <x-lucide-file class="w-6 h-6" />
                                        <span class="flex-1" x-text="file.name"></span>
                                        <div>
                                            <x-lucide-x-circle class="w-6 h-6 cursor-pointer"
                                                x-on:click="file = null; hasSubmittedFile = false" />
                                        </div>
                                    </div>
                                </div>

                            </div>

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
