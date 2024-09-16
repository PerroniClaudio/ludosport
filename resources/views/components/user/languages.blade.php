@props([
    'languages' => [],
    'availableLanguages' => [],
    'user' => null,
])

<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8" x-data="{

}">

    <div class="flex justify-between">
        <h3 class="text-background-800 dark:text-background-200 text-2xl">
            {{ __('users.languages') }}</h3>
        <x-primary-button type="button" x-on:click.prevent="$dispatch('open-modal', 'add-language-modal')">
            <x-lucide-plus class="w-5 h-5 text-white" />
        </x-primary-button>
    </div>


    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
    <x-table striped="false" :columns="[
        [
            'name' => 'Name',
            'field' => 'name',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td
        ],
    ]" :rows="$languages" />


    <x-modal name="add-language-modal" :show="$errors->customrole->isNotEmpty()" focusable>
        <div class="p-6 flex flex-col gap-2" x-data="{
            languages: {{ collect($availableLanguages) }},
            userLanguages: {{ collect($languages) }},
            selectedLanguages: {{ collect($languages) }},
            shouldShowLanguage(id) {
                return !this.userLanguages.find(language => language.id === id) && !this.selectedLanguages.find(language => language.id === id);
            },
            addLanguage(language) {
                this.selectedLanguages.push(language);
            },
            removeLanguage(language) {
                this.selectedLanguages = this.selectedLanguages.filter(selectedLanguage => selectedLanguage.id !== language.id);
            },
            associateLanguages() {
                const languages = this.selectedLanguages.map(language => language.id);
        
                const formData = new FormData();
                formData.append('languages', languages);
        
                fetch('/users/{{ $user }}/languages', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data)
                        window.location.reload();
                    })
            },
        }">
            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('users.languages_add') }}
            </h2>
            <div>

                <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                    {{ __('users.available_languages') }}</h4>

                <div class="grid grid-cols-4 gap-2">
                    <template x-for="language in languages" :key="language.id">
                        <div x-show="shouldShowLanguage(language.id)" x-on:click="addLanguage(language)"
                            class="p-2 border border-background-100 dark:border-background-700 rounded-lg cursor-pointer">
                            <p class="text-background-500 dark:text-background-300" x-text="language.name"></p>
                        </div>
                    </template>
                </div>

            </div>

            <div class="mt-4" x-show="selectedLanguages.length > 0">
                <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                    {{ __('users.selected_languages') }}</h4>

                <div class="grid grid-cols-4 gap-2">
                    <template x-for="language in selectedLanguages" :key="language.id">
                        <div x-on:click="removeLanguage(language)"
                            class="p-2 border border-primary-500 dark:border-primary-500 rounded-lg cursor-pointer">
                            <p class="text-primary-500" x-text="language.name"></p>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex justify-end">
                <x-primary-button type="button" @click="associateLanguages">
                    <span>{{ __('users.languages_edit') }}</span>
                </x-primary-button>
            </div>
        </div>
    </x-modal>

</div>
