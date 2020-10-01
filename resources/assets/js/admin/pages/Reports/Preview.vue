<template>
    <card class="preview">
        <template slot="header">
            <div class="d-flex justify-content-between">
                <h4 class="card-title">{{$ml.with('VueJS').get('txtPreview')}}</h4>
                <div class="align-self-end">
                    <button @click="$emit('send-report')" class="btn btn-success btn-process mr-3"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                    <button @click="exportPDF" class="btn btn-danger mr-3">{{$ml.with('VueJS').get('txtExportPDF')}}</button>
                    <button @click="$emit('back-to-list')" class="btn btn-primary">{{$ml.with('VueJS').get('txtBack')}}</button>
                </div>
            </div>
        </template>
        <div class="row">
            <div class="col-sm-9">
                <div class="form-group">
                    <label class=""><strong>{{$ml.with('VueJS').get('txtTitle')}}</strong></label>
                    <input v-if="preLanguage=='vi'" :value="currentReport.title" type="text" class="form-control" :disabled="true">
                    <input v-if="preLanguage=='ja'" :value="currentReport.title_ja" type="text" class="form-control" :disabled="true">
                </div>
            </div>

            <div class="col-sm-3">
                <div class="form-group">
                    <label class=""><strong>{{$ml.with('VueJS').get('txtReportType')}}</strong></label>
                    <input :value="currentReport.type" type="text" class="form-control" :disabled="true">
                </div>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-sm-3">
                <div class="form-group">
                    <label><strong>{{$ml.with('VueJS').get('lblDate')}}</strong></label>
                    <input :value="currentReport.date_time" type="text" class="form-control" :disabled="true">
                </div>
            </div>

            <div class="col-sm-9">
                <div class="form-group">
                    <label class><strong>{{$ml.with('VueJS').get('txtReporter')}}</strong></label>
                    <input :value="getReporter(currentReport.reporter)" type="text" class="form-control" :disabled="true">
                </div>
            </div>

            <div class="col-sm-12" v-if="isMeeting()">
                <div class="form-group">
                    <label class><strong>{{$ml.with('VueJS').get('txtAttendPerson')}} (KILALA)</strong></label>
                    <input :value="getReporter(currentReport.attend_person)" type="text" class="form-control" :disabled="true">
                </div>
            </div>

            <div class="col-sm-9" v-if="isMeeting()">
                <div class="form-group">
                    <label class><strong>{{$ml.with('VueJS').get('txtAttendPerson')}} ({{$ml.with('VueJS').get('txtOther')}} )</strong></label>
                    <input :value="currentReport.attend_other_person" type="text" class="form-control" :disabled="true">
                </div>
            </div>

            <div class="col-sm-3" v-if="!isMeeting()">
                <div class="form-group">
                    <label class><strong>{{$ml.with('VueJS').get('txtDepts')}}</strong></label>
                    <input :value="currentReport.dept_name" type="text" class="form-control" :disabled="true">
                </div>
            </div>
            <div class="col-sm-3" v-if="!isMeeting()">
                <div class="form-group">
                    <label class><strong>{{$ml.with('VueJS').get('txtProjects')}}</strong></label>
                    <input :value="currentReport.project_name" type="text" class="form-control" :disabled="true">
                </div>
            </div>
            <div class="col-sm-3" v-if="!isMeeting()">
                <div class="form-group">
                    <label class><strong>{{$ml.with('VueJS').get('txtIssue')}}</strong></label>
                    <input :value="currentReport.issue_name" type="text" class="form-control" :disabled="true">
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <label class=""><strong>{{$ml.with('VueJS').get('txtLang')}}</strong></label>
                    <select-2 v-model="preLanguage" class="select2">
                        <option value="vi">{{$ml.with('VueJS').get('txtVi')}}</option>
                        <option value="ja">{{$ml.with('VueJS').get('txtJa')}}</option>
                    </select-2>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div id="toolbar-container"></div>
            <div id="ck-editor">
                <ckeditor v-if="preLanguage=='vi'" :editor="editor" :value="currentReport.content" :disabled="editorDisabled"></ckeditor>
                <ckeditor v-if="preLanguage=='ja'" :editor="editor" :value="currentReport.content_ja" :disabled="editorDisabled"></ckeditor>
            </div>
        </div>
    </card>
</template>
<script>
import moment from "moment";
import DecoupledEditor from '@ckeditor/ckeditor5-build-decoupled-document';
import Card from "../../components/Cards/Card";
import Select2 from '../../components/SelectTwo/SelectTwo.vue';

export default {
    name: 'preview',
    components: {
        Select2,
        Card,
    },
    props: ['currentReport', 'userOptions'],
    data() {
        return {
            editor: DecoupledEditor,
            editorDisabled: true,
            preLanguage: this.$ml.current,
        }
    },
    mounted() {
        let _this = this;
        if ( this.currentReport.isSeen ) _this.updateSeen();
    },
    methods: {
        getObjectValue(data, id) {
            let obj = data.filter((elem) => {
                if (elem.id == id) return elem;
            });

            if (obj.length > 0)
                return obj[0];
        },
        isMeeting() {
            return this.currentReport.type == 'Meeting';
        },
        getReporter(data) {
            let result = [];
            let arrData = data.split(',');
            result = arrData.map((item, index) => {
                return this.getObjectValue(this.userOptions, item).text;
            });
            return result.join(', ');
        },
        updateSeen() {
            this.$emit('update-seen');
        },
        exportPDF() {
            let uri = "/pdf/report";
            let data = {
                is_metting: this.isMeeting() ? 1 : 0,
                title: this.preLanguage=='vi' ? this.currentReport.title : this.currentReport.title_ja,
                date_time: this.currentReport.date_time,
                reporter: this.getReporter(this.currentReport.reporter),
                attend_person: this.isMeeting() ? this.getReporter(this.currentReport.attend_person) : '',
                attend_other_person: this.currentReport.attend_other_person,
                dept_name: this.currentReport.dept_name,
                project_name: this.currentReport.project_name,
                issue_name: this.currentReport.issue_name,
                content: this.preLanguage=='vi' ? this.currentReport.content : this.currentReport.content_ja
            };

			axios
			.post(uri, {
				data: data
			})
			.then(res => {
                window.open(res.data.file_name, "_blank");
			})
			.catch(err => {
				console.log(err);
				alert("Could not load data");
			});
        }
    }
}
</script>

<style lang="scss">
.preview #ck-editor {
    border: 1px solid rgba(0, 0, 0, 0.15);
}
.btn-process {
    color: #6c757d;
    cursor: pointer;

    &:hover {
        color: #dc3545;
    }
}
</style>