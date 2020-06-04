<template>
    <div class="content">
        <div class="container-fluid">
            <ckeditor :editor="editor" v-model="editorData" :config="editorConfig" @ready="onReady"></ckeditor>
        </div>
    </div>
</template>
<script>
    import DecoupledEditor from '@ckeditor/ckeditor5-build-decoupled-document';
    // import Table from '@ckeditor/ckeditor5-table/src/table';
    // import TableToolbar from '@ckeditor/ckeditor5-table/src/tabletoolbar';
    // import TableProperties from '@ckeditor/ckeditor5-table/src/tableproperties';
    // import TableCellProperties from '@ckeditor/ckeditor5-table/src/tablecellproperties';
    // import '@ckeditor/ckeditor5-build-decoupled-document/build/translations/ja';

    class UploadAdapter {
        constructor(loader) {
            this.loader = loader;
        }

        upload() {
            return new Promise((resolve, reject) => {
                const data = new FormData();
                data.append('file', this.loader.file);
                data.append('allowSize', 10);// M
                axios.post('http://my-domains/path/to/uploadfile', data)
                .then(res=>{
                    if (res.file) {
                        resolve({
                            default: 'http://my-domains/uploads/' + data.file
                        });
                    } else {
                        reject(res.msg);
                    }
                }).catch(err => {
                    console.log(err);
                });

            });
        }

        abort() {
        }
    }

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
                },
            };
        },
        methods: {
            onReady( editor )  {
                // Insert the toolbar before the editable area.
                console.log(editor.ui.view.toolbar.element);
                editor.ui.getEditableElement().parentElement.insertBefore(
                    editor.ui.view.toolbar.element,
                    editor.ui.getEditableElement()
                );
            }
        }
    };
</script>
