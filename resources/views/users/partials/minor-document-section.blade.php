@php
    $canUpload = $canUpload ?? false;
    $uploadModalName = $uploadModalName ?? null;
@endphp

<div class="mt-4 border-t border-background-100 dark:border-background-700 pt-4">
    <div class="flex flex-col gap-4">
        <div>
            <x-input-label :value="__('users.minor_approval_documents')" />
            <p class="text-sm text-background-600 dark:text-background-300">
                {{ __('users.minor_approval_documents_help') }}
            </p>
        </div>

        <div class="rounded-lg border border-background-200 dark:border-background-700 p-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="font-medium text-background-800 dark:text-background-100">
                        {{ __('users.current_document') }}
                    </p>
                    <div class="mt-1 flex items-center gap-2 text-sm">
                        @if ($user->uploaded_documents_path)
                            <x-lucide-check-circle class="w-5 h-5 text-green-500" />
                            <span class="text-green-600 dark:text-green-400">
                                {{ __('users.document_uploaded') }}
                            </span>
                        @else
                            <x-lucide-x-circle class="w-5 h-5 text-amber-500" />
                            <span class="text-amber-600 dark:text-amber-400">
                                {{ __('users.document_missing') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if ($user->uploaded_documents_path)
                        <x-primary-link-button-small href="{{ route($currentDocumentRoute, $user->id) }}"
                            target="_blank" rel="noopener noreferrer">
                            {{ __('users.view_document') }}
                        </x-primary-link-button-small>
                    @endif

                    @if ($canUpload && $uploadModalName)
                        <x-primary-button-small type="button"
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', '{{ $uploadModalName }}')">
                            {{ $user->uploaded_documents_path ? __('users.replace') . ' ' . __('users.document') : __('users.upload_document') }}
                        </x-primary-button-small>
                    @endif
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-background-200 dark:border-background-700 p-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="font-medium text-background-800 dark:text-background-100">
                        {{ __('users.document_history') }}
                    </p>
                    <p class="text-sm text-background-600 dark:text-background-300">
                        {{ __('users.document_history_help') }}
                    </p>
                </div>
                <span class="text-sm text-background-500 dark:text-background-400">
                    {{ $user->minorDocumentHistories->count() }}
                </span>
            </div>

            @if ($user->minorDocumentHistories->isEmpty())
                <p class="mt-4 text-sm text-background-500 dark:text-background-400">
                    {{ __('users.no_document_history') }}
                </p>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-background-200 dark:divide-background-700 text-sm">
                        <thead>
                            <tr class="text-left text-background-500 dark:text-background-400">
                                <th class="py-2 pr-4 font-medium">{{ __('users.document') }}</th>
                                <th class="py-2 pr-4 font-medium">{{ __('users.archived_at') }}</th>
                                <th class="py-2 pr-4 font-medium">{{ __('users.approval_status') }}</th>
                                <th class="py-2 font-medium">{{ __('users.download_document') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-background-100 dark:divide-background-800">
                            @foreach ($user->minorDocumentHistories as $history)
                                <tr class="text-background-700 dark:text-background-200">
                                    <td class="py-3 pr-4">
                                        #{{ $history->id }}
                                    </td>
                                    <td class="py-3 pr-4">
                                        {{ $history->archived_at?->format('d/m/Y H:i') ?? '-' }}
                                    </td>
                                    <td class="py-3 pr-4">
                                        {{ $history->was_admin_approved ? __('users.approved') : __('users.not_approved') }}
                                    </td>
                                    <td class="py-3">
                                        <x-primary-link-button-small
                                            href="{{ route($historyDocumentRoute, [$user->id, $history->id]) }}">
                                            {{ __('users.download_document') }}
                                        </x-primary-link-button-small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <x-input-error :messages="$errors->get('minor_documents')" />
    </div>
</div>
