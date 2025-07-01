@props([
    'user' => 0,
    'roleid' => 0,
])

@php
    $authUser = auth()->user();
    $authUserRole = $authUser->getRole();
    // $canCreateCustomRole = in_array($authUserRole, ['admin', 'rector', 'dean']);
@endphp

<div x-data="{
    user: {{ $user }},
    selectedRole: {{ $roleid }},
    searchResultRoles: [],
    roles: [],
    newCustomRoleName: '',
    searchCustomRoleName: '',
    canShowSearchResult: false,
    loading: false,
    selectedSearchRole: 0,
    authUserRole: '{{ $authUserRole }}',
    uppercaseFirstLetters: function(string) {

        return string.replace(/\b\w/g, function(l) {
            return l.toUpperCase();
        });

    },
    init() {
        this.loading = true;
        const fetchRoute = this.authUserRole === 'admin' ? '/custom-roles' : '/' + this.authUserRole + '/custom-roles'; 
        fetch(fetchRoute)
            .then(response => response.json())
            .then(data => {
                this.roles = data.map(role => ({
                    id: role.id,
                    name: this.uppercaseFirstLetters(role.name)
                }));
                this.loading = false;
            })
            .catch(error => {
                console.log(error);
                this.loading = false;
            });
    },
    createNewCustomRole() {

        const body = new FormData();
        body.append('name', this.newCustomRoleName);
        body.append('user_id', this.user)
        const fetchRoute = this.authUserRole === 'admin' ? '/custom-roles' : '/' + this.authUserRole + '/custom-roles'; 
        fetch(fetchRoute, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: body
            })
            .then(response => response.json())
            .then(data => {

                this.selectedRole = data.id;
                $dispatch('close-modal', 'new-role-modal');
                this.init();

            }).catch(error => {
                console.log(error);
            });

    },

    searchCustomRoles() {
        const params = new URLSearchParams({
            name: this.searchCustomRoleName
        });
        const fetchRoute = this.authUserRole === 'admin' ? '/custom-roles/search' : '/' + this.authUserRole + '/custom-roles/search'; 
        fetch(fetchRoute + `?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                console.log(data)
                this.searchResultRoles = data.map(role => ({
                    id: role.id,
                    name: this.uppercaseFirstLetters(role.name)
                }));
                this.canShowSearchResult = true;
            })
            .catch(error => {
                console.log(error);
            });

    },
    searchConfirmRole() {
        console.log(this.selectedSearchRole)
        this.selectedRole = this.searchResultRoles.filter(role => role.name == this.selectedSearchRole)[0].id;
        this.assignRole();
    },
    assignRole() {
        const body = new FormData();
        body.append('role_id', this.selectedRole);
        body.append('user_id', this.user)
        const fetchRoute = this.authUserRole === 'admin' ? '/custom-roles/assign' : '/' + this.authUserRole + '/custom-roles/assign'; 
        fetch(fetchRoute, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: body
            })
            .then(response => response.json())
            .then(data => {
                console.log(data)
                $dispatch('close-modal', 'search-role-modal');
            }).catch(error => {
                console.log(error);
            });
    }

}" class="mt-2">

    <x-input-label for="custom_role" value="Custom manager role" />
    <div class="flex items-center gap-2">

        <select name="custom_role" id="custom_role" x-model="selectedRole"
            class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm">

            <template x-for="role in roles" :key="role.id">
                <option x-text="role.name" :selected="role.id == selectedRole"></option>
            </template>

        </select>

        <x-primary-button type="button" x-on:click.prevent="$dispatch('open-modal', 'new-role-modal')">
            <x-lucide-plus class="w-5 h-5 text-white" />
        </x-primary-button>

        <x-primary-button type="button" x-on:click.prevent="$dispatch('open-modal', 'search-role-modal')">
            <x-lucide-search class="w-5 h-5 text-white" />
        </x-primary-button>


    </div>

    <x-modal name="new-role-modal" :show="$errors->customrole->isNotEmpty()" focusable>
        <div class="p-6 flex flex-col gap-2">
            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('users.custom_role_create') }}
            </h2>
            <div>
                <x-input-label value="Name" />
                <input x-model="newCustomRoleName" type="text" placeholder="Custom role Name"
                    class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="flex justify-end">
                <x-primary-button type="button" @click="createNewCustomRole">
                    <span>{{ __('users.custom_role_create') }}</span>
                </x-primary-button>
            </div>
        </div>
    </x-modal>

    <x-modal name="search-role-modal" :show="$errors->searchcustomrole->isNotEmpty()" focusable>
        <div class="p-6 flex flex-col gap-2">
            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('users.custom_role_search') }}
            </h2>

            <div class="flex items-center">
                <div class="flex-1">
                    <input type="text" placeholder="Search custom role" x-model="searchCustomRoleName"
                        class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
                </div>
                <div class="ml-2">
                    <x-primary-button type="button" @click="searchCustomRoles">
                        <x-lucide-search class="w-5 h-5 text-white" />
                    </x-primary-button>
                </div>
            </div>

            <div x-show="canShowSearchResult">
                <select name="search_results_role" id="search_results_role" x-model="selectedSearchRole"
                    class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm">
                    <template x-for="role in searchResultRoles" :key="role.id">
                        <option x-text="role.name" :selected="role.id == selectedSearchRole"></option>
                    </template>
                    <option value="0">{{ __('Select an option') }}</option>
                </select>
                </select>

                <div class="flex justify-end mt-2">
                    <x-primary-button type="button" @click="searchConfirmRole">
                        <span>{{ __('users.custom_role_select') }}</span>
                    </x-primary-button>
                </div>
            </div>


        </div>
    </x-modal>

</div>
