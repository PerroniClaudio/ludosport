@props(['value' => ''])

<style>
    button.is-active {
        background: black;
        color: white;
    }

    .tiptap {
        padding: 0.5rem 1rem;
        margin: 1rem 0;
        border: 1px solid #ccc;
    }
</style>

<div x-data="editor('{{ $value }}')">

    <template x-if="isLoaded()">
        <div class="menu">

            <!-- Stili scrittura -->

            <button @click="toggleBold()" :class="{ 'is-active': isActive('bold', updatedAt) }">
                <x-lucide-bold class="w-5 h-5 text-background-800 dark:text-background-200 cursor-pointer" />
            </button>
            <button @click="toggleItalic()" :class="{ 'is-active': isActive('italic', updatedAt) }">
                <x-lucide-italic class="w-5 h-5 text-background-800 dark:text-background-200 cursor-pointer" />
            </button>
            <button @click="toggleStrike()" :class="{ 'is-active': isActive('strike', updatedAt) }">
                <x-lucide-strikethrough class="w-5 h-5 text-background-800 dark:text-background-200 cursor-pointer" />
            </button>

            <!-- Paragrafo -->

            <button @click="toggleParagraph()" :class="{ 'is-active': isActive('paragraph', updatedAt) }">
                <x-lucide-pilcrow class="w-5 h-5 text-background-800 dark:text-background-200 cursor-pointer" />
            </button>

            <!-- Dimensioni -->

            <button @click="toggleHeading({ level: 1 })"
                :class="{ 'is-active': isActive('heading', { level: 1 }, updatedAt) }">
                <x-lucide-heading-1 class="w-5 h-5 text-background-800 dark:text-background-200 cursor-pointer" />
            </button>
            <button @click="toggleHeading({ level: 2 })"
                :class="{ 'is-active': isActive('heading', { level: 2 }, updatedAt) }">
                <x-lucide-heading-2 class="w-5 h-5 text-background-800 dark:text-background-200 cursor-pointer" />
            </button>
            <button @click="toggleHeading({ level: 3 })"
                :class="{ 'is-active': isActive('heading', { level: 3 }, updatedAt) }">
                <x-lucide-heading-3 class="w-5 h-5 text-background-800 dark:text-background-200 cursor-pointer" />
            </button>
            <button @click="toggleHeading({ level: 4 })"
                :class="{ 'is-active': isActive('heading', { level: 4 }, updatedAt) }">
                <x-lucide-heading-4 class="w-5 h-5 text-background-800 dark:text-background-200 cursor-pointer" />
            </button>
            <button @click="toggleHeading({ level: 5 })"
                :class="{ 'is-active': isActive('heading', { level: 5 }, updatedAt) }">
                <x-lucide-heading-5 class="w-5 h-5 text-background-800 dark:text-background-200 cursor-pointer" />
            </button>
            <button @click="toggleHeading({ level: 6 })"
                :class="{ 'is-active': isActive('heading', { level: 6 }, updatedAt) }">
                <x-lucide-heading-6 class="w-5 h-5 text-background-800 dark:text-background-200 cursor-pointer" />
            </button>

            <!-- Liste -->

            <button @click="toggleBulletList()" :class="{ 'is-active': isActive('bulletList', updatedAt) }">
                <x-lucide-list class="w-5 h-5 text-background-800 dark:text-background-200 cursor-pointer" />
            </button>
            <button @click="toggleOrderedList()" :class="{ 'is-active': isActive('orderedList', updatedAt) }">
                <x-lucide-list-ordered class="w-5 h-5 text-background-800 dark:text-background-200 cursor-pointer" />
            </button>

            <!-- Citazioni -->

            <button @click="toggleBlockquote()" :class="{ 'is-active': isActive('blockquote', updatedAt) }">
                <x-lucide-quote class="w-5 h-5 text-background-800 dark:text-background-200 cursor-pointer" />
            </button>

            <!-- Linea orizzontale -->

            <button @click="toggleHorizontalRule()" :class="{ 'is-active': isActive('horizontalRule', updatedAt) }">
                <x-lucide-minus class="w-5 h-5 text-background-800 dark:text-background-200 cursor-pointer" />
            </button>

        </div>
    </template>

    <div x-ref="element" class="text-background-200"></div>
</div>
