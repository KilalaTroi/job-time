<template>
    <card class="preview">
        <template slot="header">
            <div class="d-flex justify-content-between">
                <h4 class="card-title">Preview Report</h4>
                <div class="align-self-end">
                    <button @click="$emit('back-to-list')" class="btn btn-primary">Back</button>
                </div>
            </div>
        </template>
        <div class="row">
            <div class="col-sm-9">
                <div class="form-group">
                    <label class=""><strong>Title</strong></label>
                    <input :value="currentReport.title" type="text" class="form-control" :disabled="true">
                </div>
            </div>

            <div class="col-sm-3">
                <div class="form-group">
                    <label class=""><strong>Report Type</strong></label>
                    <input :value="currentReport.type" type="text" class="form-control" :disabled="true">
                </div>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-sm-3">
                <div class="form-group">
                    <label><strong>Date</strong></label>
                    <input :value="currentReport.date_time" type="text" class="form-control" :disabled="true">
                </div>
            </div>

            <div class="col-sm-9">
                <div class="form-group">
                    <label class><strong>Reporter</strong></label>
                    <input :value="getReporter(currentReport.reporter)" type="text" class="form-control" :disabled="true">
                </div>
            </div>

            <div class="col-sm-12" v-if="isMeeting()">
                <div class="form-group">
                    <label class><strong>Attend Person (KILALA)</strong></label>
                    <input :value="getReporter(currentReport.attend_person)" type="text" class="form-control" :disabled="true">
                </div>
            </div>

            <div class="col-sm-9" v-if="isMeeting()">
                <div class="form-group">
                    <label class><strong>Attend Person (Other)</strong></label>
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
                    <select-2 :value="currentReport.language" class="select2" :disabled="true">
                        <option value="vi">Vietnamese</option>
                        <option value="ja">Japanese</option>
                    </select-2>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div id="toolbar-container"></div>
            <div id="ck-editor">
                <ckeditor :editor="editor" :value="currentReport.content" :disabled="editorDisabled"></ckeditor>
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
            editorDisabled: true
        }
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
            let arrdata = data.split(',');
            result = arrdata.map((item, index) => {
                return this.getObjectValue(this.userOptions, item).text;
            });
            return result.join(', ');
        }
    }
}
</script>

<style lang="scss">
.preview #ck-editor {
    border: 1px solid rgba(0, 0, 0, 0.15);
}
</style>