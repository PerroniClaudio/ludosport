@props([
    'name' => 'content',
    'label' => 'Content',
    'value' => '',
    'required' => false,
    'placeholder' => '',
    'description' => '',
    'editable' => true,
])

<div>
    @if ($label)
        @if ($description)
            <div class="flex gap-1">
                <x-input-label value="{{ $label }}" />
                <div class="has-tooltip">
                    <x-lucide-info class="h-4 text-background-300" />
                    <div
                        class="tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full">
                        {{ $description }}
                    </div>
                </div>
            </div>
        @else
            <x-input-label value="{{ $label }}" />
        @endif
    @endif

    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

    <div x-load x-data="editor(@js($value), {{ $editable ? 'true' : 'false' }})">
        <template x-if="isLoaded()">
            <div class="menu flex items-center justify-between">
                <div class="flex items-center gap-1">
                    <!-- Dimensioni -->
                    <select class="editor-button w-32" @change="toggleHeading({ level: parseInt($event.target.value)})"
                        :value="getActiveHeadingLevel(updatedAt)">
                        <option value="0" :selected="getActiveHeadingLevel(updatedAt) === 0">Normal</option>
                        <option value="1" :selected="getActiveHeadingLevel(updatedAt) === 1">Heading 1</option>
                        <option value="2" :selected="getActiveHeadingLevel(updatedAt) === 2">Heading 2</option>
                        <option value="3" :selected="getActiveHeadingLevel(updatedAt) === 3">Heading 3</option>
                        <option value="4" :selected="getActiveHeadingLevel(updatedAt) === 4">Heading 4</option>
                        <option value="5" :selected="getActiveHeadingLevel(updatedAt) === 5">Heading 5</option>
                        <option value="6" :selected="getActiveHeadingLevel(updatedAt) === 6">Heading 6</option>
                    </select>

                    <!-- Allineamento -->
                    <select class="editor-button w-32" @change="toggleTextAlign($event.target.value)">
                        <option value="left" :selected="getActiveTextalign(updatedAt) === 'left'">Left
                        </option>
                        <option value="center" :selected="getActiveTextalign(updatedAt) === 'center'">Center
                        </option>
                        <option value="right" :selected="getActiveTextalign(updatedAt) === 'right'">Right
                        </option>
                        <option value="justify" :selected="getActiveTextalign(updatedAt) === 'justify'">Justify
                        </option>
                    </select>

                    <!-- Stili scrittura -->
                    <button class="editor-button" @click="toggleBold()"
                        :class="{ 'is-active': isActive('bold', updatedAt) }">
                        <x-lucide-bold class="w-5 h-5 cursor-pointer" />
                    </button>
                    <button class="editor-button" @click="toggleItalic()"
                        :class="{ 'is-active': isActive('italic', updatedAt) }">
                        <x-lucide-italic class="w-5 h-5 cursor-pointer" />
                    </button>
                    <button class="editor-button" @click="toggleStrike()"
                        :class="{ 'is-active': isActive('strike', updatedAt) }">
                        <x-lucide-strikethrough class="w-5 h-5 cursor-pointer" />
                    </button>

                    <!-- Liste -->
                    <button class="editor-button" @click="toggleBulletList()"
                        :class="{ 'is-active': isActive('bulletList', updatedAt) }">
                        <x-lucide-list class="w-5 h-5 cursor-pointer" />
                    </button>
                    <button class="editor-button" @click="toggleOrderedList()"
                        :class="{ 'is-active': isActive('orderedList', updatedAt) }">
                        <x-lucide-list-ordered class="w-5 h-5 cursor-pointer" />
                    </button>

                    <!-- Citazioni -->
                    <button class="editor-button" @click="toggleBlockquote()"
                        :class="{ 'is-active': isActive('blockquote', updatedAt) }">
                        <x-lucide-quote class="w-5 h-5 cursor-pointer" />
                    </button>

                    <!-- Linea orizzontale -->
                    <button class="editor-button" @click="toggleHorizontalRule()"
                        :class="{ 'is-active': isActive('horizontalRule', updatedAt) }">
                        <x-lucide-minus class="w-5 h-5 cursor-pointer" />
                    </button>

                    <!-- Link -->
                    <button class="editor-button" @click="toggleLink()"
                        :class="{ 'is-active': isActive('link', updatedAt) }">
                        <x-lucide-link class="w-5 h-5 cursor-pointer" />
                    </button>
                </div>

                <!-- Avanti e indietro -->
                <div>
                    <button class="editor-button" @click="undo()"
                        :class="{ 'is-active': isActive('undo', updatedAt) }">
                        <x-lucide-undo class="w-5 h-5 cursor-pointer" />
                    </button>

                    <button class="editor-button" @click="redo()"
                        :class="{ 'is-active': isActive('redo', updatedAt) }">
                        <x-lucide-redo class="w-5 h-5 cursor-pointer" />
                    </button>
                </div>
            </div>
        </template>

        <div x-ref="element"></div>
    </div>


    <!-- Hidden input to store the content -->
    <input type="hidden" name="{{ $name }}" value="{{ $value }}" id="editor-content">

    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>
