<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
            {{ __('navigation.approve_users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">
                        {{ __('users.pending_minor_approvals') }}
                    </h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    @if ($users->isEmpty())
                        <p>{{ __('users.no_pending_minor_approvals') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-background-100 dark:divide-background-700">
                                <thead>
                                    <tr class="text-left text-sm font-semibold text-background-700 dark:text-background-200">
                                        <th class="px-4 py-3">{{ __('Name') }}</th>
                                        <th class="px-4 py-3">{{ __('Surname') }}</th>
                                        <th class="px-4 py-3">{{ __('Email') }}</th>
                                        <th class="px-4 py-3">{{ __('users.document') }}</th>
                                        <th class="px-4 py-3">{{ __('users.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-background-100 dark:divide-background-700">
                                    @foreach ($users as $user)
                                        <tr>
                                            <td class="px-4 py-3">{{ $user->name }}</td>
                                            <td class="px-4 py-3">{{ $user->surname }}</td>
                                            <td class="px-4 py-3">{{ $user->email }}</td>
                                            <td class="px-4 py-3">
                                                @if ($user->has_user_uploaded_documents)
                                                    <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                                        {{ __('users.document_uploaded') }}
                                                    </span>
                                                @else
                                                    <span class="text-sm font-medium text-amber-600 dark:text-amber-400">
                                                        {{ __('users.document_missing') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    @if ($user->has_user_uploaded_documents)
                                                        <x-primary-link-button-small href="{{ route('rector.users.approval-document', $user->id) }}"
                                                            target="_blank" rel="noopener noreferrer">
                                                            {{ __('users.upload_document') }}
                                                        </x-primary-link-button-small>
                                                    @else
                                                        <x-primary-button-small type="button"
                                                            x-data=""
                                                            x-on:click.prevent="$dispatch('open-modal', 'upload-minor-document-{{ $user->id }}')">
                                                            {{ __('users.upload_document') }}
                                                        </x-primary-button-small>
                                                    @endif

                                                    <form method="POST"
                                                        action="{{ route('rector.users.approve-minor', $user->id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <x-primary-button-small :disabled="!$user->has_user_uploaded_documents">
                                                            {{ __('users.approve') }}
                                                        </x-primary-button-small>
                                                    </form>

                                                    <x-danger-button-small type="button"
                                                        x-data=""
                                                        x-on:click.prevent="$dispatch('open-modal', 'deny-minor-{{ $user->id }}')"
                                                        :disabled="!$user->has_user_uploaded_documents">
                                                        {{ __('users.deny') }}
                                                    </x-danger-button-small>

                                                    <x-modal name="deny-minor-{{ $user->id }}" :show="false" focusable>
                                                        <form method="POST"
                                                            action="{{ route('rector.users.deny-minor', $user->id) }}"
                                                            class="p-6">
                                                            @csrf
                                                            @method('PUT')

                                                            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                                                                {{ __('users.deny_registration') }}
                                                            </h2>

                                                            <p class="mt-1 text-sm text-background-600 dark:text-background-300">
                                                                {{ __('users.deny_reason') }}
                                                            </p>

                                                            <div class="mt-6">
                                                                <label for="deny_reason_{{ $user->id }}"
                                                                    class="mb-2 block text-sm font-medium text-background-700 dark:text-background-300">
                                                                    {{ __('users.deny_reason') }}
                                                                </label>
                                                                <textarea id="deny_reason_{{ $user->id }}" name="reason" rows="4" required
                                                                    class="block w-full rounded-md border-background-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-background-700 dark:bg-background-900 dark:text-background-100"
                                                                    placeholder="{{ __('users.deny_reason_placeholder') }}">{{ old('reason') }}</textarea>
                                                                <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                                                            </div>

                                                            <div class="mt-6 flex justify-end gap-3">
                                                                <x-secondary-button type="button"
                                                                    x-on:click="$dispatch('close-modal', 'deny-minor-{{ $user->id }}')">
                                                                    {{ __('users.cancel') }}
                                                                </x-secondary-button>

                                                                <x-danger-button>
                                                                    {{ __('users.deny_registration') }}
                                                                </x-danger-button>
                                                            </div>
                                                        </form>
                                                    </x-modal>

                                                    <x-modal name="upload-minor-document-{{ $user->id }}" :show="false" focusable>
                                                        <form method="POST"
                                                            action="{{ route('rector.users.approval-document-upload', $user->id) }}"
                                                            enctype="multipart/form-data"
                                                            class="p-6">
                                                            @csrf
                                                            @method('PUT')

                                                            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                                                                {{ __('users.upload_document_and_approve') }}
                                                            </h2>

                                                            <p class="mt-1 text-sm text-background-600 dark:text-background-300">
                                                                {{ __('users.upload_document_and_approve_help') }}
                                                            </p>

                                                            <div class="mt-6">
                                                                <label for="minor_documents_{{ $user->id }}"
                                                                    class="mb-2 block text-sm font-medium text-background-700 dark:text-background-300">
                                                                    {{ __('users.document') }}
                                                                </label>
                                                                <input id="minor_documents_{{ $user->id }}"
                                                                    name="minor_documents"
                                                                    type="file"
                                                                    accept="application/pdf"
                                                                    required
                                                                    class="block w-full text-sm text-background-700 file:mr-4 file:rounded-md file:border-0 file:bg-primary-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-background-700 dark:text-background-200 dark:file:bg-primary-400 dark:file:text-background-900 dark:hover:file:bg-primary-600" />
                                                                <x-input-error :messages="$errors->get('minor_documents')" class="mt-2" />
                                                            </div>

                                                            <div class="mt-6 flex justify-end gap-3">
                                                                <x-secondary-button type="button"
                                                                    x-on:click="$dispatch('close-modal', 'upload-minor-document-{{ $user->id }}')">
                                                                    {{ __('users.cancel') }}
                                                                </x-secondary-button>

                                                                <x-primary-button>
                                                                    {{ __('users.upload_document_and_approve') }}
                                                                </x-primary-button>
                                                            </div>
                                                        </form>
                                                    </x-modal>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
