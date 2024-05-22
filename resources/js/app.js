import "./bootstrap";

import Alpine from "alpinejs";

import { Editor } from "@tiptap/core";
import StarterKit from "@tiptap/starter-kit";
import Link from "@tiptap/extension-link";

String.prototype.deentitize = function () {
    var ret = this.replace(/&gt;/g, ">");
    ret = ret.replace(/&lt;/g, "<");
    ret = ret.replace(/&quot;/g, '"');
    ret = ret.replace(/&apos;/g, "'");
    ret = ret.replace(/&amp;/g, "&");
    return ret;
};

document.addEventListener("alpine:init", () => {
    Alpine.data("editor", (content) => {
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
                        Link,
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
                        const element =
                            document.querySelector("#editor-content");
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
                editor.chain().focus().toggleHorizontalRule().run();
            },
            toggleLink() {
                editor
                    .chain()
                    .focus()
                    .toggleLink({ href: "https://example.com" })
                    .run();
            },
            undo() {
                editor.chain().focus().undo().run();
            },
            redo() {
                editor.chain().focus().redo().run();
            },
        };
    });
});

window.Alpine = Alpine;

Alpine.start();
