@props([
    'roles' => [],
    'availableRoles' => [],
    'user' => null,
    'editableRoles' => auth()->user() ? auth()->user()->getEditableRoles()->pluck('name') : [],
])
@php
    $authRole = auth()->user()->getRole();
@endphp

{{-- Per caricare le traduzioni dei ruoli nel modal --}}
<script>
    window.translations = @json(__('users'));
</script>

<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 h-full" x-data="{}">

    <div class="flex justify-between">
        <h3 class="text-background-800 dark:text-background-200 text-2xl">
            {{ __('users.authorization') }}
        </h3>
        @if (in_array($authRole, ['admin', 'rector', 'dean', 'manager']))
            <x-primary-button type="button" class="h-fit" x-on:click.prevent="$dispatch('open-modal', 'add-role-modal')">
                <x-lucide-plus class="w-5 h-5" />
            </x-primary-button>
        @endif
    </div>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-background-800 dark:text-background-200">
        @foreach ($roles as $role)
            <div class="border border-background-700 rounded-lg p-4 flex items-center gap-2">

                @switch($role)
                    @case('admin')
                        <x-lucide-crown class="w-6 h-6 text-primary-500 shrink-0" />
                    @break

                    @case('athlete')
                        <x-lucide-swords class="w-6 h-6 text-primary-500 shrink-0" />
                    @break

                    @case('rector')
                        <x-lucide-graduation-cap class="w-6 h-6 text-primary-500 shrink-0" />
                    @break

                    @case('dean')
                        <x-lucide-book-marked class="w-6 h-6 text-primary-500 shrink-0" />
                    @break

                    @case('manager')
                        <x-lucide-briefcase class="w-6 h-6 text-primary-500 shrink-0" />
                    @break

                    @case('technician')
                        <x-lucide-wrench class="w-6 h-6 text-primary-500 shrink-0" />
                    @break

                    @case('instructor')
                        <x-lucide-megaphone class="w-6 h-6 text-primary-500 shrink-0" />
                    @break

                    @default
                @endswitch

                <span>{{ __("users.{$role}") }}</span>
            </div>
        @endforeach

        {{-- @if ($user->hasRole('manager'))
            <div class="sm:col-span-2">
                <x-user.custom-role :user="$user->id" :roleid="isset($user->customRoles()->first()->id) ? $user->customRoles()->first()->id : 0" />
            </div>
        @endif --}}
    </div>

    @if (in_array($authRole, ['admin', 'rector', 'dean', 'manager']))
        <x-modal name="add-role-modal" focusable>
            <div class="p-6 flex flex-col gap-2" x-data="{
                roles: {{ collect($availableRoles) }},
                userRoles: {{ collect($roles) }},
                selectedRoles: {{ collect($roles) }},
                editableRoles: {{ collect($editableRoles) }},
                shouldShowRole(name) {
                    {{-- return !this.userRoles.find(role => role.id === id) && !this.selectedRoles.find(role => role.id === id); --}}
                    return !this.selectedRoles.find(role => role === name);
                },
                addRole(role) {
                    if (this.editableRoles.includes(role.name)) {
                        this.selectedRoles.push(role.name);
                    } else {
                        FlashMessage.displayCustomMessage('You cannot edit this role', 2000);
                    }
                },
            
                removeRole(role) {
                    {{-- Modificare i permessi per i ruoli in base al ruolo dell'utente autenticato --}}
                    if (this.editableRoles.includes(role)) {
                        this.selectedRoles = this.selectedRoles.filter(selectedRole => selectedRole !== role);
                    } else {
                        FlashMessage.displayCustomMessage('You cannot edit this role', 2000);
                    }
                },
                associateRoles() {
                    const roles = this.selectedRoles;
            
                    {{-- Modificare i permessi per i ruoli in base al ruolo dell'utente autenticato --}}
            
                    const formData = new FormData();
                    formData.append('roles', roles);
            
                    fetch('{{ $authRole === 'admin' ? '' : '/' . $authRole }}/user-roles/{{ $user->id }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData,
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                FlashMessage.displayCustomMessage('Error: ' + data.error, 2000);
                                return;
                            }
                            console.log(data)
                            window.location.reload();
                        })
                        .catch(error => {
                            FlashMessage.displayCustomMessage('Error'.error.message ? +': ' + error.message : '', 2000);
                            console.error('Error:', error);
                        });
                },
            }">
                <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                    {{ __('users.select_roles') }}
                </h2>
                <div>

                    <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                        {{ __('users.available_roles') }}</h4>

                    <div class="grid grid-cols-4 gap-2">
                        <template x-for="role in roles" :key="role.id">
                            <div x-show="shouldShowRole(role.name)" x-on:click="addRole(role)"
                                class="p-2 border border-background-100 dark:border-background-700 rounded-lg cursor-pointer">
                                <p class="text-sm text-background-500 dark:text-background-300"
                                    x-text="window.translations[role.name] || role.name">
                                </p>
                            </div>
                        </template>
                    </div>

                </div>

                <template x-if="selectedRoles && selectedRoles.length > 0" x-effect="selectedRoles">
                    <div class="mt-4">
                        <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                            {{ __('users.selected_roles') }}</h4>

                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="role in selectedRoles" :key="role">
                                <div x-on:click="removeRole(role)"
                                    class="p-2 border border-primary-500 dark:border-primary-500 rounded-lg cursor-pointer">
                                    <p class="text-sm text-primary-500" x-text="window.translations[role] || role"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <div class="flex justify-end">
                    <x-primary-button type="button" @click="associateRoles">
                        <span>{{ __('users.roles_edit') }}</span>
                    </x-primary-button>
                </div>
            </div>
        </x-modal>
    @endif

</div>
