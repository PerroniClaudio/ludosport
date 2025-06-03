<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12">
            <h1
                class="text-6xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                {{ __('website.search_user_title') }}
            </h1>

            <p class="text-background-800 dark:text-background-200 text-justify">{{ __('website.search_user_text') }}
            </p>

            <div x-data="{
                value: '',
                users: [],
                searchUsers() {
                    if (this.value.length > 3) {
            
                        let url = '/website-users/search?search=' + this.value;
            
                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                console.log(data);
                                this.users = data;
                            })
                            .catch((error) => {
                                console.error('Error:', error);
                            });
            
                    }
                },
                userDetail(slug) {
                    window.location.href = '/website-users/' + slug;
                }
            }">
                <div class="flex items-center gap-2">
                    <div class="flex-1">
                        <input type="search" name="search" x-model="value" x-on:input.debounce.200ms="searchUsers"
                            class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
                    </div>
                    <x-primary-button @click="searchUsers">
                        <x-lucide-search class="h-5 w-5 text-white" />
                    </x-primary-button>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 py-8 text-background-900 dark:text-background-100">
                    <template x-for="user in users" :key="user.id">
                        <div class="bg-white dark:bg-transparent flex gap-4 mb-4  border dark:border-background-700 rounded p-4 hover:border-primary-500 cursor-pointer"
                            @click="userDetail(user.battle_name)">
                            <div class="rounded-full h-12 w-12">
                                <img x-bind:src="'{{ env('APP_URL') }}/profile-picture/' + user.id" alt="avatar"
                                    class="rounded-full h-12 w-12 object-cover object-center" />
                            </div>
                            <div>
                                <div class="text-md text-primary-500" x-text="user.name + ' ' + user.surname"></div>
                                <div class="text-xs text-background-500 dark:text-background-400"
                                    x-text="'@'+user.battle_name"></div>

                                <div class="flex items-center gap-2">
                                    <x-lucide-flag class="h-4 w-4 text-background-500 dark:text-background-400" />
                                    <div>
                                        <span class="text-xs" x-text="user.nation.name"> </span>
                                    </div>
                                    <img x-bind:src="'/nation/' + user.nation.id + '/flag'"
                                        x-bind:alt="user.nation.name" class="h-2 w-4">
                                </div>
                            </div>
                        </div>
                    </template>

                </div>
            </div>
        </section>
    </div>
</x-website-layout>
