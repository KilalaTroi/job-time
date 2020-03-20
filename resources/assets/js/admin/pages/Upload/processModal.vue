<template>
    <modal id="processModal" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">Upload</template>
        <div v-if="currentProcess">
            <h5>Project Issue</h5>
            <div class="table-responsive">
                <no-action-table class="table-hover table-striped" :columns="columns" :data="dataProcess"></no-action-table>
            </div>
            <div class="form-group">
                <h5>New Message</h5>
                <textarea v-model="newMessage" class="form-control" rows="4"></textarea>
            </div>
            <div class="form-group border p-3">
                <h5>BOX DESTINATION <input type="file" name=""></h5>
                <p>https://yuidea.app.box.com/folder/49217853872</p>
            </div>
            <div class="form-group border p-3">
                <h5>File <input type="file" name=""></h5>
                <p>\\192.168.0.233\daichi\tsuchi_kilala\Job\2020_114\1st\indd</p>
            </div>
            <div class="form-group d-flex justify-content-between">
                <base-checkbox v-model="finishProcess">Finish</base-checkbox>
                <button type="button" class="btn btn-primary">Send</button>
            </div>
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <hr/>
            <div class="form-group">
                <h5>Process List</h5>
                <no-action-table class="table-hover table-striped" :columns="columns2" :data="dataProcessList"></no-action-table>
            </div>
        </div>
    </modal>
</template>

<script>
    import NoActionTable from "../../components/TableNoAction";
    import ErrorItem from "../../components/Validations/Error";
    import SuccessItem from "../../components/Validations/Success";
    import Modal from "../../components/Modals/Modal";

    export default {
        name: "process-modal",
        components: {
            Modal,
            ErrorItem,
            SuccessItem,
            NoActionTable,
        },
        props: ["currentProcess"],
        data() {
            return {
                columns: [
                    { id: "p_name", value: this.$ml.with('VueJS').get('txtProject'), width: "", class: "" },
                    { id: "i_name", value: this.$ml.with('VueJS').get('txtIssue'), width: "", class: "" },
                    { id: "phase", value: this.$ml.with('VueJS').get('txtPhase'), width: "", class: "" },
                    { id: "page", value: this.$ml.with('VueJS').get('txtPage'), width: "", class: "" }
                ],
                columns2: [
                    { id: "date", value: 'Date', width: "", class: "" },
                    { id: "name", value: 'Name', width: "", class: "" },
                    { id: "comment", value: 'Comment', width: "", class: "" },
                    { id: "info", value: 'Box', width: "", class: "text-center" }
                ],
                dataProcess: [],
                dataProcessList: [],
                newMessage: '',
                errors: '',
                success: '',
                finishProcess: false,
            }
        },
        methods: {
            getDataProcess() {
                this.dataProcess = [
                    {
                        p_name: this.currentProcess.project,
                        i_name: this.currentProcess.issue,
                        phase: this.currentProcess.phase,
                        page: this.currentProcess.page
                    }
                ]
            },
            getDataProcessList() {
                this.dataProcessList = [
                    {
                        date: '2020/Jan/14',
                        name: 'Lợi',
                        comment: '20, 21, 30 page',
                        info: '<button type="button" class="btn btn-second">Show</button>'
                    },
                    {
                        date: '2020/Jan/14',
                        name: 'Ngọc',
                        comment: '22, 23, 24 page',
                        info: '<button type="button" class="btn btn-second">Show</button>'
                    }
                ]
            }
        },
        watch: {
            currentProcess: [
                {
                    handler: "getDataProcessList"
                },
                {
                    handler: "getDataProcess"
                }
            ]
        } 
    };
</script>
<style lang="scss">
    
</style>