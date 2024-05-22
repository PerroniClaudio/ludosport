@props(['value' => '', 'label' => '', 'event' => []])

<style>
    .tiptap {
        padding: 0.5rem 1rem;
        margin: 1rem 0;
        border: 1px solid var(--background-700);
    }
</style>



<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">

    <div class="flex items-center justify-between">
        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ $label }}</h3>
        <form method="POST" action={{ route('technician.events.save.description', $event->id) }}>
            @csrf
            <x-primary-button type="sumbit">
                <x-lucide-save class="w-5 h-5 text-white" />
            </x-primary-button>
            <input type="hidden" name="description" value="{{ $value }}" id="editor-content">
        </form>
    </div>

    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
    <div x-data="editor('{{ $value }}')">

        <template x-if="isLoaded()">
            <div class="menu flex items-center justify-between">
                <div>

                    <!-- Stili scrittura -->

                    <button class="editor-button" @click="toggleBold()"
                        :class="{ 'is-active': isActive('bold', updatedAt) }">
                        <x-lucide-bold class="w-5 h-5  cursor-pointer" />
                    </button>
                    <button class="editor-button" @click="toggleItalic()"
                        :class="{ 'is-active': isActive('italic', updatedAt) }">
                        <x-lucide-italic class="w-5 h-5  cursor-pointer" />
                    </button>
                    <button class="editor-button" @click="toggleStrike()"
                        :class="{ 'is-active': isActive('strike', updatedAt) }">
                        <x-lucide-strikethrough class="w-5 h-5  cursor-pointer" />
                    </button>

                    <!-- Paragrafo -->

                    <button class="editor-button" @click="toggleParagraph()"
                        :class="{ 'is-active': isActive('paragraph', updatedAt) }">
                        <x-lucide-pilcrow class="w-5 h-5  cursor-pointer" />
                    </button>

                    <!-- Dimensioni -->

                    <button class="editor-button" @click="toggleHeading({ level: 1 })"
                        :class="{ 'is-active': isActive('heading', { level: 1 }, updatedAt) }">
                        <x-lucide-heading-1 class="w-5 h-5  cursor-pointer" />
                    </button>
                    <button class="editor-button" @click="toggleHeading({ level: 2 })"
                        :class="{ 'is-active': isActive('heading', { level: 2 }, updatedAt) }">
                        <x-lucide-heading-2 class="w-5 h-5  cursor-pointer" />
                    </button>
                    <button class="editor-button" @click="toggleHeading({ level: 3 })"
                        :class="{ 'is-active': isActive('heading', { level: 3 }, updatedAt) }">
                        <x-lucide-heading-3 class="w-5 h-5  cursor-pointer" />
                    </button>
                    <button class="editor-button" @click="toggleHeading({ level: 4 })"
                        :class="{ 'is-active': isActive('heading', { level: 4 }, updatedAt) }">
                        <x-lucide-heading-4 class="w-5 h-5  cursor-pointer" />
                    </button>
                    <button class="editor-button" @click="toggleHeading({ level: 5 })"
                        :class="{ 'is-active': isActive('heading', { level: 5 }, updatedAt) }">
                        <x-lucide-heading-5 class="w-5 h-5  cursor-pointer" />
                    </button>
                    <button class="editor-button" @click="toggleHeading({ level: 6 })"
                        :class="{ 'is-active': isActive('heading', { level: 6 }, updatedAt) }">
                        <x-lucide-heading-6 class="w-5 h-5  cursor-pointer" />
                    </button>

                    <!-- Liste -->

                    <button class="editor-button" @click="toggleBulletList()"
                        :class="{ 'is-active': isActive('bulletList', updatedAt) }">
                        <x-lucide-list class="w-5 h-5  cursor-pointer" />
                    </button>
                    <button class="editor-button" @click="toggleOrderedList()"
                        :class="{ 'is-active': isActive('orderedList', updatedAt) }">
                        <x-lucide-list-ordered class="w-5 h-5  cursor-pointer" />
                    </button>

                    <!-- Citazioni -->

                    <button class="editor-button" @click="toggleBlockquote()"
                        :class="{ 'is-active': isActive('blockquote', updatedAt) }">
                        <x-lucide-quote class="w-5 h-5  cursor-pointer" />
                    </button>

                    <!-- Linea orizzontale -->

                    <button class="editor-button" @click="toggleHorizontalRule()"
                        :class="{ 'is-active': isActive('horizontalRule', updatedAt) }">
                        <x-lucide-minus class="w-5 h-5  cursor-pointer" />
                    </button>

                    <!-- Link -->

                    <button class="editor-button" @click="toggleLink()"
                        :class="{ 'is-active': isActive('link', updatedAt) }">
                        <x-lucide-link class="w-5 h-5  cursor-pointer" />
                    </button>

                </div>

                <!-- Avanti e indietro -->

                <div>

                    <button class="editor-button" @click="undo()"
                        :class="{ 'is-active': isActive('undo', updatedAt) }">
                        <x-lucide-undo class="w-5 h-5  cursor-pointer" />
                    </button>

                    <button class="editor-button" @click="redo()"
                        :class="{ 'is-active': isActive('redo', updatedAt) }">
                        <x-lucide-redo class="w-5 h-5  cursor-pointer" />
                    </button>

                </div>

            </div>
        </template>

        <div x-ref="element"></div>


    </div>

</div>
