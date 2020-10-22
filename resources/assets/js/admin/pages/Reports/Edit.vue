<template>
    <card>
        <template slot="header">
            <div class="d-flex justify-content-between">
                <h4 class="card-title">{{$ml.with('VueJS').get('txtEdit')}}</h4>
                <div class="align-self-end">
                    <button v-if="checkTranslate()" @click="translateContent()" class="btn btn-success mr-3">{{$ml.with('VueJS').get('txtTranslate')}}</button>
                    <button @click="$emit('delete-report', currentReport.id)" class="btn btn-danger mr-3">{{$ml.with('VueJS').get('txtDelete')}}</button>
                    <button @click="$emit('back-to-list')" class="btn btn-primary">{{$ml.with('VueJS').get('txtBack')}}</button>
                </div>
            </div>
        </template>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label class=""><strong>{{$ml.with('VueJS').get('txtTitle')}}</strong></label>
                    <input v-if="editLanguage=='vi'" v-model="title" type="text" class="form-control">
                    <input v-if="editLanguage=='ja'" v-model="titleJA" type="text" class="form-control">
                </div>
            </div>

            <div class="col-sm-3">
                <div class="form-group">
                    <label class=""><strong>{{$ml.with('VueJS').get('txtReportType')}}</strong></label>
                    <select-2 v-model="reportType" class="select2">
                        <option value="Trouble">{{$ml.with('VueJS').get('txtTrouble')}}</option>
                        <option value="Meeting">{{$ml.with('VueJS').get('txtMeeting')}}</option>
                        <option value="Notice">{{$ml.with('VueJS').get('txtNotice')}}</option>
                    </select-2>
                </div>
            </div>

            <div class="col-sm-3">
                <div class="form-group">
                    <label class=""><strong>{{$ml.with('VueJS').get('txtTeam')}}</strong></label>
                    <select-2 :options="currentTeamOption" v-model="team" class="select2" />
                </div>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-sm-3">
                <div class="form-group">
                    <label><strong>{{$ml.with('VueJS').get('lblDate')}}</strong></label>
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

            <div class="col-sm-3" v-if="isMeeting() || isNotice()">
                <label><strong>{{$ml.with('VueJS').get('lblTime')}}</strong></label>
                <vue-timepicker input-class="form-control" v-model="time" hide-disabled-items :minute-range="MinuteRange" :hour-range="HourRange"  input-width="100%" close-on-complete required></vue-timepicker>
            </div>

            <div :class="[{'col-sm-6' : isMeeting() || isNotice()}, {'col-sm-9' : !isMeeting() && !isNotice()}]">
                <div class="form-group">
                    <label class><strong>{{$ml.with('VueJS').get('txtReporter')}}</strong></label>
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

            <div class="col-sm-12" v-if="isMeeting() || isNotice()">
                <div class="form-group">
                    <label v-if="isNotice()"><strong>{{$ml.with('VueJS').get('txtDestination')}}</strong></label>
                    <label v-else><strong>{{$ml.with('VueJS').get('txtAttendPerson')}}</strong></label>
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

            <div class="col-sm-9" v-if="isMeeting() || isNotice()">
                <div class="form-group">
                    <label class><strong>{{$ml.with('VueJS').get('txtAttendPerson')}} (Other)</strong></label>
                    <input v-model="attendPersonOther" type="text" class="form-control">
                </div>
            </div>

            <div class="col-sm-3" v-if="!isMeeting() && !isNotice()">
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
            <div class="col-sm-3" v-if="!isMeeting() && !isNotice()">
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
            <div class="col-sm-3" v-if="!isMeeting() && !isNotice()">
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
                    <select-2 v-model="editLanguage" class="select2">
                        <option value="vi">{{$ml.with('VueJS').get('txtVi')}}</option>
                        <option value="ja">{{$ml.with('VueJS').get('txtJa')}}</option>
                    </select-2>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div id="toolbar-container"></div>
            <div id="ck-editor">
                <ckeditor v-if="editLanguage=='vi'" :editor="editor" v-model="editorData" :config="editorConfig" @ready="onReady"></ckeditor>
                <ckeditor v-if="editLanguage=='ja'" :editor="editor" v-model="editorDataJA" :config="editorConfig" @ready="onReady"></ckeditor>
            </div>
        </div>

        <error-item :errors="errors"></error-item>

        <div class="form-group text-right">
            <button @click="emitUpdateReport" class="btn btn-primary">{{$ml.with('VueJS').get('txtUpdate')}}</button>
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
import { mapGetters, mapActions } from "vuex";

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
    name: 'edit',
    components: {
        Select2,
        Datepicker,
        Multiselect,
        Card,
        ErrorItem,
        VueTimepicker
    },
    computed: {
        ...mapGetters({
			currentTeamOption: 'currentTeamOption'
        }),
    },
    props: ['currentReport', 'userOptionsParent', 'departmentsParent', 'projectsParent', 'issuesParent', 'userID'],
    data() {
        return {
            countLoad: 0,
            title: this.currentReport.title,
            titleJA: this.currentReport.title_ja,
            team: this.currentReport.team_id,
            date: this.currentReport.date_time,
            time: this.currentReport.date_time.split(' ')[1],
            HourRange: [[8, 17]],
            MinuteRange: [0, 10, 20, 30, 40, 50],
            dataLang: {
                vi: vi,
                ja: ja
            },
            user_id: '',
            attendPerson: '',
            attendPersonOther: this.currentReport.attend_other_person,
            deptSelects: this.getDepartment(this.currentReport),
			projectSelects: this.getProject(this.currentReport),
			issueSelects: this.getIssue(this.currentReport),
            reportType: this.currentReport.type,
            txtAll: this.$ml.with('VueJS').get('txtSelectAll'),
            userOptions: this.userOptionsParent,
            departments: this.departmentsParent,
            projects: this.projectsParent,
            issues: this.issuesParent,

            isEditing: false,
            editor: DecoupledEditor,
            editorData: this.currentReport.content,
            editorDataJA: this.currentReport.content_ja,
            editorConfig: {
                // The configuration of the editor.
                // language: 'ja'
                extraPlugins: [ MyCustomUploadAdapterPlugin ],
            },

            editLanguage: this.$ml.current,
            translatable: this.currentReport.translatable,
            errors: []
        }
    },
    mounted() {
        let _this = this;
        _this.user_id = this.getReporter(this.currentReport.reporter);
        _this.attendPerson = this.getReporter(this.currentReport.attend_person);
        if ( _this.currentReport.isSeen ) _this.updateSeen();
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
        checkTranslate() {
            return (! this.translatable) && (this.currentReport.language != this.editLanguage);
        },
        translateContent() {
            let uri = "/data/translate-content";
            let title = this.editLanguage == 'vi' ? this.title : this.titleJA;
            let content = this.editLanguage == 'vi' ? this.editorData : this.editorDataJA;

            this.translatable = 1;
            
            // translate Title
			axios
			.post(uri, {
				lang: this.editLanguage,
				text: title,
			})
			.then(res => {
				if ( this.editLanguage == 'vi' ) {
                    this.title = res.data.contentTranslated;
                } else {
                    this.titleJA = res.data.contentTranslated;
                }
			})
			.catch(err => {
				console.log(err);
				alert("Could not translate");
            });
            
            // translate Content
            axios
			.post(uri, {
				lang: this.editLanguage,
				text: content,
			})
			.then(res => {
				if ( this.editLanguage == 'vi' ) {
                    this.editorData = res.data.contentTranslated;
                } else {
                    this.editorDataJA = res.data.contentTranslated; 
                }
			})
			.catch(err => {
				console.log(err);
				alert("Could not translate");
			});
        },
        getObjectValue(data, id) {
            let obj = data.filter((elem) => {
                if (elem.id == id) return elem;
            });

            if (obj.length > 0)
                return obj[0];
        },
        getReporter(data) {
            if (!data) return [];

            let result = [];
            let arrData = data.split(',');
            result = arrData.map((item, index) => {
                return this.getObjectValue(this.userOptions, item);
            });
            return result;
        },
        getDepartment(data) {
            if (!data) return null;

            return {
                id: data.dept_id,
                text: data.dept_name
            };
        },
        getProject(data) {
            if (!data) return null;

            return {
                id: data.project_id,
                text: data.project_name
            };
        },
        getIssue(data) {
            if (!data) return null;

            return {
                id: data.issue,
                text: data.issue_name
            };
        },
        fetchDataFilter() {
			let uri = "/data/reports?team_id=" + this.team;
			axios
			.post(uri, {
				deptSelects: this.deptSelects,
				projectSelects: this.projectSelects,
                issueSelects: this.issueSelects,
                team_id: this.team
			})
			.then(res => {
                this.departments = res.data.departments;
				this.projects = res.data.projects;
                this.issues = res.data.issues;
                this.userOptions = res.data.users;
                this.countLoad += 1;

                if ( this.countLoad === 1 ) {
                    this.projectSelects = this.getProject(this.currentReport);
                }

                if ( this.countLoad === 2 ) {
                    this.issueSelects = this.getIssue(this.currentReport);
                }
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
        emitUpdateReport() {
            this.errors = [];

            if ( !this.title && this.editLanguage == 'vi' ) {
                this.errors = [['Please typing the title'], ...this.errors];
            }

            if ( !this.titleJA && this.editLanguage == 'ja' ) {
                this.errors = [['Please typing the title'], ...this.errors];
            }

            if ( !this.date ) {
                this.errors = [['Please choosing the date'], ...this.errors];
            }

            if ( !this.user_id.length ) {
                this.errors = [['Please choosing the user report'], ...this.errors];
            }

            if ( this.isMeeting() || this.isNotice() ) {
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

            if ( !this.editorData && this.editLanguage == 'vi' ) {
                this.errors = [['Please typing the content'], ...this.errors];
            }

            if ( !this.editorDataJA && this.editLanguage == 'ja' ) {
                this.errors = [['Please typing the content'], ...this.errors];
            }

            if ( !this.errors.length ) {
                let uri = '/data/reports-action/' + this.currentReport.id;
                let newItem = {
                    type: this.reportType,
                    team_id: this.team,
                    seen: this.userID.toString(),
                    author: this.user_id.map((item, index) => { return item.id }).toString(),
                };

                if ( this.editLanguage == 'vi' ) {
                    newItem.title = this.title;
                    newItem.content = this.editorData;
                } else {
                    newItem.title_ja = this.titleJA;
                    newItem.content_ja = this.editorDataJA;
                }

                if ( this.translatable ) {
                    newItem.translatable = this.translatable;
                }

                if ( this.isMeeting() || this.isNotice() ) {
                    newItem.attend_person = this.attendPerson.map((item, index) => { return item.id }).toString();
                    newItem.attend_other_person = this.attendPersonOther;
                    newItem.date_time = moment(this.date).format("YYYY-MM-DD") + " " + this.time;
                    newItem.issue = "";
                } else {
                    newItem.date_time = moment(this.date).format("YYYY-MM-DD HH:mm");
                    newItem.issue = this.issueSelects.id;
                    newItem.attend_person = "";
                    newItem.attend_other_person = "";
                }

                axios.patch(uri, newItem)
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
        resetDepartment() {
			this.deptSelects = null;
		},
		resetProject() {
			this.projectSelects = null;
		},
		resetIssue() {
			this.issueSelects = null;
        },
        isMeeting() {
            return this.reportType == 'Meeting';
        },
        isNotice() {
            return this.reportType == 'Notice';
        },
        updateSeen() {
            this.$emit('update-seen');
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
			{ handler: function(value, oldValue) {
                console.log(value, oldValue)
                if ( value != oldValue ) {
                    this.fetchDataFilter()
                    this.resetProject()
                }
            } }
		],
		projectSelects: [
			{ handler: function(value, oldValue) {
                if ( value != oldValue ) {
                    this.fetchDataFilter()
                    this.resetIssue()
                }
            } }
        ],
        team: [{
            handler: function(value, oldValue) {
                if ( value != oldValue ) {
                    this.resetDepartment()
                    this.fetchDataFilter()
                }
            }
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