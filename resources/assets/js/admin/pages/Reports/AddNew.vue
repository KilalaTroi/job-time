<template>
    <card>
        <template slot="header">
            <div class="d-flex justify-content-between">
                <h4 class="card-title">Create New Report</h4>
                <div class="align-self-end">
                    <button @click="$emit('back-to-list')" class="btn btn-primary">Back</button>
                </div>
            </div>
        </template>
        <div class="row">
            <div class="col-sm-9">
                <div v-if="language=='vi'" class="form-group">
                    <label class=""><strong>Title</strong></label>
                    <input v-model="title" type="text" class="form-control">
                </div>
                <div v-if="language=='ja'" class="form-group">
                    <label class=""><strong>Title</strong></label>
                    <input v-model="titleJA" type="text" class="form-control">
                </div>
            </div>

            <div class="col-sm-3">
                <div class="form-group">
                    <label class=""><strong>Report Type</strong></label>
                    <select-2 v-model="reportType" class="select2">
                        <option value="Trouble">Trouble</option>
                        <option value="Meeting">Meeting</option>
                    </select-2>
                </div>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-sm-3">
                <div class="form-group">
                    <label><strong>Date</strong></label>
                    <datepicker
                    name="date"
                    input-class="form-control"
                    placeholder=""
                    v-model="date"
                    :format="customFormatter"
                    :disabled-dates="disabledEndDates()"
                    :language="getLanguage(this.$ml)"
                    ></datepicker>
                </div>
            </div>

            <div class="col-sm-3" v-if="isMeeting()">
                <label><strong>Time</strong></label>
                <vue-timepicker input-class="form-control" v-model="time" hide-disabled-items :minute-range="MinuteRange" :hour-range="HourRange"  input-width="100%" close-on-complete required></vue-timepicker>
            </div>

            <div :class="[{'col-sm-6' : isMeeting()}, {'col-sm-9' : !isMeeting()}]">
                <div class="form-group">
                    <label class><strong>Reporter</strong></label>
                    <div>
                        <multiselect
                        :multiple="true"
                        v-model="user_id"
                        :options="userOptions"
                        :clear-on-select="false"
                        :preserve-search="true"
                        :placeholder="$ml.with('VueJS').get('txtPickSome')"
                        label="text"
                        track-by="text"
                        :preselect-first="false"
                        ></multiselect>
                    </div>
                </div>
            </div>

            <div class="col-sm-12" v-if="isMeeting()">
                <div class="form-group">
                    <label class><strong>Attend Person (KILALA)</strong></label>
                    <div>
                        <multiselect
                        :multiple="true"
                        v-model="attendPerson"
                        :options="userOptions"
                        :clear-on-select="false"
                        :preserve-search="true"
                        :placeholder="$ml.with('VueJS').get('txtPickSome')"
                        label="text"
                        track-by="text"
                        :preselect-first="false"
                        ></multiselect>
                    </div>
                </div>
            </div>

            <div class="col-sm-9" v-if="isMeeting()">
                <div class="form-group">
                    <label class><strong>Attend Person (Other)</strong></label>
                    <input v-model="attendPersonOther" type="text" class="form-control">
                </div>
            </div>

            <div class="col-sm-3" v-if="!isMeeting()">
                <div class="form-group">
                    <label class><strong>{{$ml.with('VueJS').get('txtDepts')}}</strong></label>
                    <div>
                        <multiselect
                        :multiple="false"
                        v-model="deptSelects"
                        :options="departments"
                        :clear-on-select="false"
                        :preserve-search="true"
                        :placeholder="$ml.with('VueJS').get('txtSelectOne')"
                        label="text"
                        track-by="text"
                        :preselect-first="false"
                        ></multiselect>
                    </div>
                </div>
            </div>
            <div class="col-sm-3" v-if="!isMeeting()">
                <div class="form-group">
                    <label class><strong>{{$ml.with('VueJS').get('txtProjects')}}</strong></label>
                    <div>
                        <multiselect
                        :multiple="false"
                        v-model="projectSelects"
                        :options="projects"
                        :clear-on-select="false"
                        :preserve-search="true"
                        :placeholder="$ml.with('VueJS').get('txtSelectOne')"
                        label="text"
                        track-by="text"
                        :preselect-first="false"
                        ></multiselect>
                    </div>
                </div>
            </div>
            <div class="col-sm-3" v-if="!isMeeting()">
                <div class="form-group">
                    <label class><strong>{{$ml.with('VueJS').get('txtIssue')}}</strong></label>
                    <div>
                        <multiselect
                        :multiple="false"
                        v-model="issueSelects"
                        :options="issues"
                        :clear-on-select="true"
                        :preserve-search="false"
                        :placeholder="$ml.with('VueJS').get('txtSelectOne')"
                        label="text"
                        track-by="text"
                        :preselect-first="false"
                        ></multiselect>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <label class=""><strong>{{$ml.with('VueJS').get('txtLang')}}</strong></label>
                    <select-2 v-model="language" class="select2">
                        <option value="vi">Vietnamese</option>
                        <option value="ja">Japanese</option>
                    </select-2>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div id="toolbar-container"></div>
            <div id="ck-editor">
                <ckeditor v-if="language=='vi'" :editor="editor" v-model="editorData" :config="editorConfig" @ready="onReady"></ckeditor>
                <ckeditor v-if="language=='ja'" :editor="editor" v-model="editorDataJA" :config="editorConfig" @ready="onReady"></ckeditor>
            </div>
        </div>

        <error-item :errors="errors"></error-item>

        <div class="form-group text-right">
            <button @click="emitCreateReport" class="btn btn-primary">{{$ml.with('VueJS').get('txtCreate')}}</button>
        </div>
    </card>
</template>
<script>
import Datepicker from "vuejs-datepicker";
import VueTimepicker from 'vue2-timepicker';
import Multiselect from "vue-multiselect";
import { vi, ja, en } from "vuejs-datepicker/dist/locale";
import moment from "moment";
import DecoupledEditor from '@ckeditor/ckeditor5-build-decoupled-document';
import Card from "../../components/Cards/Card";
import Select2 from '../../components/SelectTwo/SelectTwo.vue';
import ErrorItem from "../../components/Validations/Error";

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
    name: 'add-new',
    components: {
        Select2,
        Datepicker,
        Multiselect,
        Card,
        ErrorItem,
        VueTimepicker
    },
    props: ['userOptions', 'departments', 'userID', 'actionNewReport'],
    data() {
        return {
            title: '',
            titleJA: '',
            date: '',
            time: '',
            HourRange: [[8, 17]],
            MinuteRange: [0, 10, 20, 30, 40, 50],
            dataLang: {
                vi: vi,
                ja: ja
            },
            user_id: [],
            attendPerson: [],
            attendPersonOther: '',
            deptSelects: null,
			projectSelects: null,
            issueSelects: null,
            reportType: 'Trouble',
			txtAll: this.$ml.with('VueJS').get('txtSelectAll'),
            projects: [],
            issues: [],

            isEditing: false,
            editor: DecoupledEditor,
            editorData: '',
            editorDataJA: '',
            editorConfig: {
                // The configuration of the editor.
                // language: 'ja'
                extraPlugins: [ MyCustomUploadAdapterPlugin ],
            },

            language: this.$ml.current,
            translatable: 0,
            errors: []
        }
    },
    beforeMount() {
        this.defaultContent();

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
        fetchDataFilter() {
			let uri = "/data/reports";
			axios
			.post(uri, {
				deptSelects: this.deptSelects,
				projectSelects: this.projectSelects,
				issueSelects: this.issueSelects
			})
			.then(res => {
				this.projects = res.data.projects;
				this.issues = res.data.issues;
			})
			.catch(err => {
				console.log(err);
				alert("Could not load data");
			});
		},
        getLanguage(data) {
            return this.dataLang[data.current]
        },
        disabledEndDates() {
            let obj = {
                from: new Date() // Disable all dates after specific date
                // days: [0], // Disable Saturday's and Sunday's
            };
            return obj;
        },
        customFormatter(date) {
			return moment(date).format("YYYY/MM/DD");
        },
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
        },
        emitCreateReport() {
            this.errors = [];

            if ( !this.title && !this.titleJA ) {
                this.errors = [['Please typing the title'], ...this.errors];
            }

            if ( !this.date ) {
                this.errors = [['Please choosing the date'], ...this.errors];
            }

            if ( !this.user_id.length ) {
                this.errors = [['Please choosing the user report'], ...this.errors];
            }

            if ( this.isMeeting() ) {
                if ( !this.attendPerson.length ) {
                    this.errors = [['Please choosing the user attend'], ...this.errors];
                }
            } else {
                if ( !this.deptSelects ) {
                    this.errors = [['Please choosing the department'], ...this.errors];
                }

                if ( !this.projectSelects ) {
                    this.errors = [['Please choosing the project'], ...this.errors];
                }

                if ( !this.issueSelects ) {
                    this.errors = [['Please choosing the issue'], ...this.errors];
                }
            }

            if ( !this.editorData && !this.editorDataJA ) {
                this.errors = [['Please typing the content'], ...this.errors];
            }

            if ( !this.errors.length ) {
                let uri = '/data/reports-action';
                let newItem = {
                    language: this.language,
                    translatable: this.translatable,
                    type: this.reportType,
                    seen: this.userID.toString(),
                    author: this.user_id.map((item, index) => { return item.id }).toString(),
                };

                if ( this.language == 'vi' ) {
                    newItem.title = this.title;
                    newItem.content = this.editorData;
                    newItem.title_ja = this.title;
                    newItem.content_ja = this.editorData;
                } else {
                    newItem.title = this.titleJA;
                    newItem.content = this.editorDataJA;
                    newItem.title_ja = this.titleJA;
                    newItem.content_ja = this.editorDataJA;
                }

                if ( this.isMeeting() ) {
                    newItem.attend_person = this.attendPerson.map((item, index) => { return item.id }).toString();
                    newItem.attend_other_person = this.attendPersonOther;
                    newItem.date_time = moment(this.date).format("YYYY-MM-DD") + " " + this.time;
                } else {
                    newItem.date_time = moment(this.date).format("YYYY-MM-DD HH:mm");
                    newItem.issue = this.issueSelects.id;
                }

                axios.post(uri, newItem)
                    .then(res => {
                        console.log(res.data.message);
                        this.title = '';
                        this.titleJA = '';
                        this.date = '';
                        this.time = '';
                        this.attendPerson = [];
                        this.attendPersonOther = '';
                        this.user_id = [];
                        this.deptSelects = null;
                        this.projectSelects = null;
                        this.issueSelects = null;
                        this.reportType = 'Trouble';
                        this.editorData = '';
                        this.editorDataJA = '';
                        this.errors = [];
                        this.$emit('back-to-list', true);
                    })
                    .catch(err => {
                        console.log(err);
                        if (err.response.status == 422) {
                            this.errors = err.response.data;
                        }
                    });
            }
        },
		resetProject() {
			this.projectSelects = null;
		},
		resetIssue() {
			this.issueSelects = null; 
        },
        defaultContent() {
            if ( this.isMeeting() ) {
                this.editorData = '<h4>議事内容</h4><ol><li>会議の内容や決定事項を記入</li><li>会議の内容や決定事項を記入</li></ol><h4>次回の予定</h4><ul><li>次回のミーティング内容、やるべきことを記入</li></ul>';
                this.editorDataJA = '<h4>議事内容</h4><ol><li>会議の内容や決定事項を記入</li><li>会議の内容や決定事項を記入</li></ol><h4>次回の予定</h4><ul><li>次回のミーティング内容、やるべきことを記入</li></ul>';
            } else {
                this.editorData = '<h4>トラブルの内容</h4><ol><li>「いつ」「誰が」「何をした」を時間順に記入</li><li>「いつ」「誰が」「何をした」を時間順に記入</li></ol><h4>参考画像</h4><p style="margin-left:40px;">&nbsp;</p><h4>トラブルの原因</h4><ul><li>トラブルの「原因」を記入</li></ul><h4>改善方法</h4><ul><li>トラブル防止の「改善方法」を記入</li></ul>';
                this.editorDataJA = '<h4>トラブルの内容</h4><ol><li>「いつ」「誰が」「何をした」を時間順に記入</li><li>「いつ」「誰が」「何をした」を時間順に記入</li></ol><h4>参考画像</h4><p style="margin-left:40px;">&nbsp;</p><h4>トラブルの原因</h4><ul><li>トラブルの「原因」を記入</li></ul><h4>改善方法</h4><ul><li>トラブル防止の「改善方法」を記入</li></ul>';
            }
        },
        isMeeting() {
            return this.reportType == 'Meeting';
        },
        typeReportChange() {
            this.errors = [];

            this.defaultContent();

            if ( this.isMeeting() ) {
                this.deptSelects = [];
            } else {
                this.attendPerson = [];
                this.attendPersonOther = '';
            }
        },
        languageChange() {
            if ( this.actionNewReport ) {
                this.errors = [];
                this.title = '';
                this.titleJA = '';
                this.defaultContent();
            }
        }
    },
    watch: {
        editorData: [{
            handler: 'contentChange'
        }],
        editorDataJA: [{
            handler: 'contentChange'
        }],
        deptSelects: [
			{ handler: "fetchDataFilter" },
			{ handler: "resetProject" }
		],
		projectSelects: [
			{ handler: "fetchDataFilter" },
			{ handler: "resetIssue" }
		],
        reportType: [{
            handler: 'typeReportChange'
        }],
        language: [{
            handler: 'languageChange'
        }]
    }
}
</script>

<style lang="scss">
@import "~vue-multiselect/dist/vue-multiselect.min.css";
@import '~vue2-timepicker/dist/VueTimepicker.css';

.multiselect__single {
	white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.vue__time-picker input.display-time {
    height: 40px;
    background-color: transparent;
    cursor: pointer;
}
</style>