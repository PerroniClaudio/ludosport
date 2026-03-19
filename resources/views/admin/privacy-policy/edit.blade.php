<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
            {{ __('privacy_policy.edit_title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <div class="text-background-900 dark:text-background-100">
                    @if ($policy && $policy->lastModifiedBy)
                        <div class="mb-6 p-4 bg-background-100 dark:bg-background-700 rounded-lg text-sm">
                            <p class="text-background-700 dark:text-background-300">
                                <strong>{{ __('privacy_policy.last_modified_by') }}:</strong>
                                {{ $policy->lastModifiedBy->name }} {{ $policy->lastModifiedBy->surname }}
                            </p>
                            <p class="text-background-600 dark:text-background-400">
                                <strong>{{ __('privacy_policy.last_modified_at') }}:</strong>
                                {{ $policy->updated_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-6 p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form id="privacyForm" method="POST" action="{{ route('privacy-policy.update') }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="content" value="{{ $policy->content ?? '' }}" id="editor-content">

                        <div>
                            <label class="block text-sm font-medium text-background-700 dark:text-background-300 mb-4">
                                {{ __('privacy_policy.content') }}
                            </label>
                        </div>

                        <div x-load x-data="editor(@js($policy->content ?? ''), true)" class="space-y-4">
                            <template x-if="isLoaded()">
                                <div class="menu flex items-center justify-between mb-4 p-4 bg-background-50 dark:bg-background-900 rounded-lg border border-background-200 dark:border-background-700">
                                    <div class="flex items-center gap-1 flex-wrap">
                                        <!-- Dimensioni -->
                                        <select class="editor-button w-32" @change="toggleHeading({ level: parseInt($event.target.value)})"
                                            :value="getActiveHeadingLevel(updatedAt)">
                                            <option value="0" :selected="getActiveHeadingLevel(updatedAt) === 0">Normal </option>
                                            <option value="1" :selected="getActiveHeadingLevel(updatedAt) === 1">Heading 1 </option>
                                            <option value="2" :selected="getActiveHeadingLevel(updatedAt) === 2">Heading 2 </option>
                                            <option value="3" :selected="getActiveHeadingLevel(updatedAt) === 3">Heading 3 </option>
                                            <option value="4" :selected="getActiveHeadingLevel(updatedAt) === 4">Heading 4 </option>
                                            <option value="5" :selected="getActiveHeadingLevel(updatedAt) === 5">Heading 5 </option>
                                            <option value="6" :selected="getActiveHeadingLevel(updatedAt) === 6">Heading 6 </option>
                                        </select>

                                        <!-- Allineamento -->
                                        <select class="editor-button w-32" @change="toggleTextAlign($event.target.value)">
                                            <option value="left" :selected="getActiveTextalign(updatedAt) === 'left'">Left </option>
                                            <option value="center" :selected="getActiveTextalign(updatedAt) === 'center'">Center</option>
                                            <option value="right" :selected="getActiveTextalign(updatedAt) === 'right'">Right </option>
                                            <option value="justify" :selected="getActiveTextalign(updatedAt) === 'justify'">Justify</option>
                                        </select>

                                        <!-- Stili scrittura -->
                                        <button type="button" class="editor-button" @click="toggleBold()"
                                            :class="{ 'is-active': isActive('bold', updatedAt) }">
                                            <x-lucide-bold class="w-5 h-5 cursor-pointer" />
                                        </button>
                                        <button type="button" class="editor-button" @click="toggleItalic()"
                                            :class="{ 'is-active': isActive('italic', updatedAt) }">
                                            <x-lucide-italic class="w-5 h-5 cursor-pointer" />
                                        </button>
                                        <button type="button" class="editor-button" @click="toggleStrike()"
                                            :class="{ 'is-active': isActive('strike', updatedAt) }">
                                            <x-lucide-strikethrough class="w-5 h-5 cursor-pointer" />
                                        </button>

                                        <!-- Liste -->
                                        <button type="button" class="editor-button" @click="toggleBulletList()"
                                            :class="{ 'is-active': isActive('bulletList', updatedAt) }">
                                            <x-lucide-list class="w-5 h-5 cursor-pointer" />
                                        </button>
                                        <button type="button" class="editor-button" @click="toggleOrderedList()"
                                            :class="{ 'is-active': isActive('orderedList', updatedAt) }">
                                            <x-lucide-list-ordered class="w-5 h-5 cursor-pointer" />
                                        </button>

                                        <!-- Citazioni -->
                                        <button type="button" class="editor-button" @click="toggleBlockquote()"
                                            :class="{ 'is-active': isActive('blockquote', updatedAt) }">
                                            <x-lucide-quote class="w-5 h-5 cursor-pointer" />
                                        </button>

                                        <!-- Linea orizzontale -->
                                        <button type="button" class="editor-button" @click="toggleHorizontalRule()"
                                            :class="{ 'is-active': isActive('horizontalRule', updatedAt) }">
                                            <x-lucide-minus class="w-5 h-5 cursor-pointer" />
                                        </button>

                                        <!-- Link -->
                                        <button type="button" class="editor-button" @click="toggleLink()"
                                            :class="{ 'is-active': isActive('link', updatedAt) }">
                                            <x-lucide-link class="w-5 h-5 cursor-pointer" />
                                        </button>

                                        <div class="flex ml-auto gap-1">
                                            <button type="button" class="editor-button" @click="undo()"
                                                :class="{ 'is-active': isActive('undo', updatedAt) }">
                                                <x-lucide-undo class="w-5 h-5 cursor-pointer" />
                                            </button>
                                            <button type="button" class="editor-button" @click="redo()"
                                                :class="{ 'is-active': isActive('redo', updatedAt) }">
                                                <x-lucide-redo class="w-5 h-5 cursor-pointer" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div x-ref="element" class="border border-background-300 dark:border-background-600 rounded-lg p-4"></div>
                        </div>

                        @error('content')
                            <div class="text-sm text-red-600 dark:text-red-400">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="flex gap-4 justify-end">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-background-200 dark:bg-background-700 border border-transparent rounded-md font-semibold text-xs text-background-800 dark:text-background-200 uppercase tracking-widest hover:bg-background-300 dark:hover:bg-background-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-background-800 transition ease-in-out duration-150">
                                {{ __('privacy_policy.cancel') }}
                            </a>
                            <x-primary-button type="submit">
                                {{ __('privacy_policy.save') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .editor-button {
            @apply px-3 py-2 rounded text-background-600 dark:text-background-400 bg-white dark:bg-background-800 hover:bg-background-100 dark:hover:bg-background-700 border border-background-200 dark:border-background-600 transition duration-150;
        }

        .editor-button.is-active {
            @apply bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-400 border-primary-300 dark:border-primary-600;
        }

        .ProseMirror {
            @apply min-h-96 outline-none;
        }
    </style>
</x-app-layout>
