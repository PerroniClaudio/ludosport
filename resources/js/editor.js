// Tiptap Editor

import { Editor } from "@tiptap/core";
import StarterKit from "@tiptap/starter-kit";
import Link from "@tiptap/extension-link";
import TextAlign from "@tiptap/extension-text-align";

String.prototype.deentitize = function () {
    var ret = this.replace(/&gt;/g, ">");
    ret = ret.replace(/&lt;/g, "<");
    ret = ret.replace(/&quot;/g, '"');
    ret = ret.replace(/&apos;/g, "'");
    ret = ret.replace(/&amp;/g, "&");
    return ret;
};

export const editor = (content) => {
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
                ],
                content: content.deentitize(),
                editable: true,
                autofocus: true,
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
    };
};
