// Tiptap Editor

import { Editor } from "@tiptap/core";
import StarterKit from "@tiptap/starter-kit";
import Link from "@tiptap/extension-link";
import TextAlign from "@tiptap/extension-text-align";
import Image from "@tiptap/extension-image";

String.prototype.deentitize = function () {
    var ret = this.replace(/&amp;/g, "&");
    ret = ret.replace(/&gt;/g, ">");
    ret = ret.replace(/&lt;/g, "<");
    ret = ret.replace(/&quot;/g, '"');
    ret = ret.replace(/&apos;/g, "'");
    ret = ret.replace(/&amp;/g, "&");

    return ret;
};

export const editor = (content, isEditable = true, entityId = null, entityType = 'event') => {
    let editor; // Alpine's reactive engine automatically wraps component properties in proxy objects. Attempting to use a proxied editor instance to apply a transaction will cause a "Range Error: Applying a mismatched transaction", so be sure to unwrap it using Alpine.raw(), or simply avoid storing your editor as a component property, as shown in this example.

    return {
        updatedAt: Date.now(), // force Alpine to rerender on selection change
        init() {
            const _this = this;

            editor = new Editor({
                element: this.$refs.element,
                extensions: [
                    StarterKit.configure({
                        heading: {
                            HTMLAttributes: {
                                class: "text-2xl font-bold",
                            },
                        },
                    }),
                    Link.configure({
                        openOnClick: false,
                        autolink: false,
                        defaultProtocol: "https",
                    }),
                    TextAlign.configure({
                        types: ["heading", "paragraph"],
                    }),
                    Image.extend({
                        addAttributes() {
                            return {
                                ...this.parent?.(),
                                class: {
                                    default: 'w-full max-w-full h-auto rounded-lg my-4',
                                    parseHTML: element => element.getAttribute('class'),
                                    renderHTML: attributes => {
                                        return {
                                            class: attributes.class || 'w-full max-w-full h-auto rounded-lg my-4'
                                        }
                                    },
                                },
                            }
                        },
                    }).configure({
                        inline: true,
                        allowBase64: false,
                    }),
                ],
                content: content.deentitize(),
                editable: isEditable,
                autofocus: false,
                editorProps: {
                    attributes: {
                        class: "w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:outline-none focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm",
                    },
                },
                onCreate({ editor }) {
                    _this.updatedAt = Date.now();
                },
                onUpdate({ editor }) {
                    _this.updatedAt = Date.now();
                    const html = editor.getHTML();
                    const element = document.querySelector("#editor-content");
                    element.value = html;
                },
                onSelectionUpdate({ editor }) {
                    _this.updatedAt = Date.now();
                },
            });
        },
        isLoaded() {
            return editor;
        },
        isActive(type, opts = {}) {
            return editor.isActive(type, opts);
        },
        toggleHeading(opts) {
            editor.chain().toggleHeading(opts).focus().run();
        },
        toggleTextAlign(opts) {
            console.log(opts);
            editor.chain().focus().setTextAlign(opts).run();
        },
        toggleBold() {
            editor.chain().focus().toggleBold().run();
        },
        toggleItalic() {
            editor.chain().focus().toggleItalic().run();
        },
        toggleStrike() {
            editor.chain().focus().toggleStrike().run();
        },
        toggleParagraph() {
            editor.chain().focus().toggleParagraph().run();
        },
        toggleBulletList() {
            editor.chain().focus().toggleBulletList().run();
        },
        toggleOrderedList() {
            editor.chain().focus().toggleOrderedList().run();
        },
        toggleBlockquote() {
            editor.chain().focus().toggleBlockquote().run();
        },
        toggleHorizontalRule() {
            editor.chain().focus().setHorizontalRule().run();
        },
        toggleLink() {
            const previousUrl = editor.getAttributes("link").href;
            const url = window.prompt("URL", previousUrl);

            // cancelled
            if (url === null) {
                return;
            }

            // empty
            if (url === "") {
                editor
                    .chain()
                    .focus()
                    .extendMarkRange("link")
                    .unsetLink()
                    .run();

                return;
            }

            // update link
            editor
                .chain()
                .focus()
                .extendMarkRange("link")
                .setLink({ href: url })
                .run();
        },
        setImageSize(size) {
            // const sizeClasses = {
            //     'small': 'max-w-xs',      // 320px
            //     'medium': 'max-w-md',     // 448px
            //     'large': 'max-w-2xl',     // 672px
            //     'full': 'max-w-full'      // 100%
            // };
            const sizeClasses = {
                'small': 'w-full max-w-xs',      // 100% width fino a 320px
                'medium': 'w-full max-w-md',     // 100% width fino a 448px
                'large': 'w-full max-w-2xl',     // 100% width fino a 672px
                'full': 'w-full max-w-full'      // 100% width sempre
            };

            if (!sizeClasses[size]) return;

            // Aggiorna gli attributi dell'immagine selezionata
            editor.chain().focus().updateAttributes('image', {
                class: `${sizeClasses[size]} h-auto rounded-lg my-4`
            }).run();

            // Salva automaticamente la descrizione
            if (typeof saveContent === 'function') {
                saveContent();
            }
        },
        getImageSize(updatedAt) {
            if (this.updatedAt !== updatedAt) {
                return '';
            }

            if (!editor.isActive('image')) {
                return '';
            }

            const attrs = editor.getAttributes('image');
            const imgClass = attrs.class || '';

            // Cerca quale classe di dimensione è presente
            if (imgClass.includes('max-w-xs')) return 'small';
            if (imgClass.includes('max-w-md')) return 'medium';
            if (imgClass.includes('max-w-2xl')) return 'large';
            if (imgClass.includes('max-w-full')) return 'full';

            return 'full'; // default
        },
        undo() {
            editor.chain().focus().undo().run();
        },
        redo() {
            editor.chain().focus().redo().run();
        },
        getActiveHeadingLevel(updatedAt) {
            if (this.updatedAt !== updatedAt) {
                return;
            }

            return editor.isActive("heading", { level: 1 })
                ? 1
                : editor.isActive("heading", { level: 2 })
                ? 2
                : editor.isActive("heading", { level: 3 })
                ? 3
                : editor.isActive("heading", { level: 4 })
                ? 4
                : editor.isActive("heading", { level: 5 })
                ? 5
                : editor.isActive("heading", { level: 6 })
                ? 6
                : 0;
        },
        getActiveTextalign(updatedAt) {
            if (this.updatedAt !== updatedAt) {
                return;
            }

            if (editor.isActive({ textAlign: "left" })) {
                return "left";
            }
            if (editor.isActive({ textAlign: "center" })) {
                return "center";
            }
            if (editor.isActive({ textAlign: "right" })) {
                return "right";
            }
            if (editor.isActive({ textAlign: "justify" })) {
                return "justify";
            }
        },
        async uploadImage(file) {
            if (!entityId || !isEditable) {
                console.error('Cannot upload image: entityId not provided or editor not editable');
                return null;
            }

            const formData = new FormData();
            formData.append('image', file);

            try {
                let uploadUrl;
                
                if (entityType === 'announcement') {
                    // Per gli annunci, la route è sempre senza prefisso di ruolo
                    uploadUrl = `/announcements/${entityId}/content/upload-image`;
                } else {
                    // Per gli eventi, determina il ruolo dall'URL corrente
                    const currentPath = window.location.pathname;
                    let routePrefix = '';
                    
                    if (currentPath.includes('/rector/')) {
                        routePrefix = '/rector';
                    } else if (currentPath.includes('/manager/')) {
                        routePrefix = '/manager';
                    } else if (currentPath.includes('/instructor/')) {
                        routePrefix = '/instructor';
                    }

                    uploadUrl = `${routePrefix}/events/${entityId}/description/upload-image`;
                }

                const response = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    return data.path;
                } else {
                    console.error('Upload failed:', data.error);
                    return null;
                }
            } catch (error) {
                console.error('Error uploading image:', error);
                return null;
            }
        },
        async insertImage() {
            if (!isEditable) return;

            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            
            input.onchange = async (e) => {
                const file = e.target.files[0];
                if (!file) return;

                // Mostra feedback all'utente
                const originalContent = editor.getHTML();
                editor.commands.insertContent('<p>Uploading image...</p>');

                const imagePath = await this.uploadImage(file);
                
                if (imagePath) {
                    // Rimuovi il messaggio di caricamento
                    editor.commands.setContent(originalContent);
                    // Inserisci l'immagine con il path e classe di default (full width)
                    editor.chain().focus().setImage({ 
                        src: imagePath,
                        class: 'w-full max-w-full h-auto rounded-lg my-4'
                    }).run();
                    
                    // Salva automaticamente la descrizione
                    if (typeof saveContent === 'function') {
                        await saveContent();
                    }
                } else {
                    // Ripristina il contenuto originale in caso di errore
                    editor.commands.setContent(originalContent);
                    alert('Error uploading image. Please try again.');
                }
            };

            input.click();
        },
    };
};
