@props(['id' => 'editor', 'model'])

<div
    class="w-full"
    x-data="{
        content: @entangle($model),
        quill: null,
        init() {
            this.quill = new Quill(this.$refs.editor, {
                theme: 'snow',
                modules: {
                    resize: {},
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'align': [] }],
                        ['link', 'image'],
                        ['clean']
                    ],
                    history: {
                        delay: 2000,
                        maxStack: 500,
                        userOnly: true
                    },
                }
            });

            this.$nextTick(() => {
                this.quill.root.innerHTML = this.content || '';
            });

            this.quill.on('text-change', () => {
                this.content = this.quill.root.innerHTML;
            });
        }
    }"
    wire:ignore
>
    <div x-ref="editor" id="{{ $id }}" class="bg-white dark:bg-zinc-900 min-h-[400px]"></div>
</div>
