<template>
    <div class="content">
        <div class="container-fluid">
            <div id="toolbar-container"></div>
            <div id="ck-editor">
                <ckeditor :editor="editor" v-model="editorData" :config="editorConfig" @ready="onReady"></ckeditor>
            </div>
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

    class MyUploadAdapter {
        constructor( loader ) {
            // The file loader instance to use during the upload.
            this.loader = loader;
        }

        // Starts the upload process.
        upload() {
            return this.loader.file
                .then( file => new Promise( ( resolve, reject ) => {
                    this._initRequest();
                    this._initListeners( resolve, reject, file );
                    this._sendRequest( file );
                } ) );
        }

        // Aborts the upload process.
        abort() {
            if ( this.xhr ) {
                this.xhr.abort();
            }
        }

        // Initializes the XMLHttpRequest object using the URL passed to the constructor.
        _initRequest() {
            const xhr = this.xhr = new XMLHttpRequest();

            // Note that your request may look different. It is up to you and your editor
            // integration to choose the right communication channel. This example uses
            // a POST request with JSON as a data structure but your configuration
            // could be different.
            xhr.open( 'POST', '/data/upload/report', true );
            xhr.responseType = 'json';
            xhr.setRequestHeader('X-CSRF-TOKEN', document.head.querySelector('meta[name="csrf-token"]').content);
        }

        // Initializes XMLHttpRequest listeners.
        _initListeners( resolve, reject, file ) {
            const xhr = this.xhr;
            const loader = this.loader;
            const genericErrorText = `Couldn't upload file: ${ file.name }.`;

            xhr.addEventListener( 'error', () => reject( genericErrorText ) );
            xhr.addEventListener( 'abort', () => reject() );
            xhr.addEventListener( 'load', () => {
                const response = xhr.response;

                // This example assumes the XHR server's "response" object will come with
                // an "error" which has its own "message" that can be passed to reject()
                // in the upload promise.
                //
                // Your integration may handle upload errors in a different way so make sure
                // it is done properly. The reject() function must be called when the upload fails.
                if ( !response || response.error ) {
                    return reject( response && response.error ? response.error.message : genericErrorText );
                }

                // If the upload is successful, resolve the upload promise with an object containing
                // at least the "default" URL, pointing to the image on the server.
                // This URL will be used to display the image in the content. Learn more in the
                // UploadAdapter#upload documentation.
                resolve( {
                    default: response.url
                } );
            } );

            // Upload progress when it is supported. The file loader has the #uploadTotal and #uploaded
            // properties which are used e.g. to display the upload progress bar in the editor
            // user interface.
            if ( xhr.upload ) {
                xhr.upload.addEventListener( 'progress', evt => {
                    if ( evt.lengthComputable ) {
                        loader.uploadTotal = evt.total;
                        loader.uploaded = evt.loaded;
                    }
                } );
            }
        }

        // Prepares the data and sends the request.
        _sendRequest( file ) {
            // Prepare the form data.
            const data = new FormData();

            data.append( 'upload', file );

            // Important note: This is the right place to implement security mechanisms
            // like authentication and CSRF protection. For instance, you can use
            // XMLHttpRequest.setRequestHeader() to set the request headers containing
            // the CSRF token generated earlier by your application.

            // Send the request.
            this.xhr.send( data );
        }
    }

    // ...

    function MyCustomUploadAdapterPlugin( editor ) {
        editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
            // Configure the URL to the upload script in your back-end here!
            return new MyUploadAdapter( loader );
        };
    }

    export default {
        components: {
        },

        data() {
            return {
                isEditing: false,
                editor: DecoupledEditor,
                editorData: '<p>Content of the editor.</p>',
                editorConfig: {
                    // The configuration of the editor.
                    // language: 'ja'
                    extraPlugins: [ MyCustomUploadAdapterPlugin ],
                },
            };
        },
        beforeMount() {
            window.addEventListener("beforeunload", this.preventNav)
            this.$once("hook:beforeDestroy", () => {
                window.removeEventListener("beforeunload", this.preventNav);
            })
        },
        beforeRouteLeave(to, from, next) {
            if (this.isEditing) {
                if (!window.confirm("Leave without saving?")) {
                    return;
                }
            }
            next();
        },
        methods: {
            onReady( editor )  {
                // Insert the toolbar before the editable area.
                // editor.ui.getEditableElement().parentElement.insertBefore(
                //     editor.ui.view.toolbar.element,
                //     editor.ui.getEditableElement()
                // );

                const toolbarContainer = document.querySelector( '#toolbar-container' );
                toolbarContainer.appendChild( editor.ui.view.toolbar.element );
            },
            preventNav(event) {
                if (!this.isEditing) return
                event.preventDefault()
                event.returnValue = ""
            },
            contentChange() {
                this.isEditing = true;
            }
        },
        watch: {
            editorData: [{
                handler: 'contentChange'
            }],
        }
    };
</script>
