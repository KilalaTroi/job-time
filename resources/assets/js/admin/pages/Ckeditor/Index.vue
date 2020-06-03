<template>
    <div class="content">
        <div class="container-fluid">
            <ckeditor :editor="editor" v-model="editorData" :config="editorConfig" @ready="onReady"></ckeditor>
        </div>
    </div>
</template>
<script>
    import DecoupledEditor from '@ckeditor/ckeditor5-build-decoupled-document';
    // import '@ckeditor/ckeditor5-build-decoupled-document/build/translations/ja';

    export default {
        components: {
        },

        data() {
            return {
                editor: DecoupledEditor,
                editorData: '<p>Content of the editor.</p>',
                editorConfig: {
                    // The configuration of the editor.
                    // language: 'ja'
                }
            };
        },
        methods: {
            onReady( editor )  {
                // Insert the toolbar before the editable area.
                editor.ui.getEditableElement().parentElement.insertBefore(
                    editor.ui.view.toolbar.element,
                    editor.ui.getEditableElement()
                );
            }
        }
    };
</script>
