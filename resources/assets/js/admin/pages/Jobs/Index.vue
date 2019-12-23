<template>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-auto">
                    <card>
                        <template slot="header">
                            <h4 class="card-title text-center">{{ this.customFormatter(start_date) }}</h4>
                        </template>
                        <datepicker name="startDate" v-model="start_date" :format="customFormatter" :inline="true" :disabled-dates="disabledEndDates()">
                        </datepicker>
                    </card>
                </div>
                <div class="col">
                    <card>
                        <template slot="header">
                            <h4 class="card-title">Jobs List</h4>
                        </template>
                        <div class="table-responsive" v-if="jobs">
                            <job-table
                                class="table-hover table-striped"
                                :columns="columns"
                                :data="jobs"
                                v-on:get-job="getJob">
                            </job-table>
                        </div>
                        <pagination :data="jobData" :show-disabled="jShowDisabled" :limit="JLimit" :align="jAlign" :size="jSize" @pagination-change-page="getResults"></pagination>
                    </card>
                    <card>
                        <template slot="header">
                            <h4 class="card-title">Time Record</h4>
                        </template>
                        <div class="table-responsive"></div>
                    </card>
                </div>
            </div>

            <AddTime :currentJob="currentJob" :errors="validationErrors" :success="validationSuccess"  v-on:add-time="addTime" v-on:reset-validation="resetValidate"></AddTime> 
        </div>
    </div>
</template>
<script>
import Card from '../../components/Cards/Card'
import Datepicker from 'vuejs-datepicker';
import { en, ja } from 'vuejs-datepicker/dist/locale'
import moment from 'moment'
import JobTable from '../../components/TableJob'
import AddTime from './AddTime'

const tableColumns = [
    { id: 'department', value: 'Department', width: '', class: '' },
    { id: 'project', value: 'Project', width: '', class: '' },
    { id: 'issue', value: 'Issue', width: '60', class: 'text-center' },
    { id: 'time', value: 'Time', width: '110', class: 'text-center' }
];

export default {
    components: {
        Card,
        datepicker: Datepicker,
        JobTable,
        AddTime
    },
    data() {
        return {
            userID: document.querySelector("meta[name='user-id']").getAttribute('content'),
            columns: [...tableColumns],
            departments: [],
            jobs: [],
            jobData: [],
            jobsTime: [],
            currentJob: null,

            start_date: new Date(),

            validationErrors: '',
            validationSuccess: '',

            jLimit: 2,
            jShowDisabled: true,
            jAlign: 'right',
            jSize: 'small'
        }
    },
    mounted() {
        this.fetchItems();
    },
    methods: {
        fetchItems() {
            let uri = '/data/jobs/?date=' + this.dateFormatter(this.start_date) + '&user_id=' + this.userID;
            axios.get(uri)
                .then(res => {
                    this.departments = res.data.departments;
                    this.jobData = res.data.jobs;
                    this.jobsTime = res.data.jobsTime;
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not load projects");
                });
        },
        getResults(page = 1) {
            axios.get('/data/jobs?page=' + page + '&date=' + this.dateFormatter(this.start_date) + '&user_id=' + this.userID)
                .then(response => {
                    this.jobData = response.data.jobs; 
                });
        },
        getObjectValue(data, id) {
            let obj = data.filter(function(elem) {
                if (elem.id === id) return elem;
            });

            if (obj.length > 0)
                return obj[0];
        },
        getDataJobs(jobData) {
            if (jobData.data.length) {
                let dataJobs = [];

                for (let i = 0; i < jobData.data.length; i++) {
                    let time = typeof(this.getObjectValue(this.jobsTime, jobData.data[i].id)) !== 'undefined' ? this.getObjectValue(this.jobsTime, jobData.data[i].id).total : false;
                    let obj = {
                        id: jobData.data[i].id,
                        department: this.getObjectValue(this.departments, jobData.data[i].dept_id).text != 'All' ? this.getObjectValue(this.departments, jobData.data[i].dept_id).text : '',
                        project: jobData.data[i].p_name,
                        issue: jobData.data[i].i_name,
                        time: time ? this.hourFormatter(time) : '00:00'
                    };
                    dataJobs.push(obj);
                }
                this.jobs = dataJobs;
            } else {
                this.jobs = [];
            }
        },
        getJob(id) {
            let job = this.getObjectValue(this.jobs, id);
            let obj = {
                id: job.id,
                p_name: job.project,
                i_name: job.issue,
                date: this.start_date
            };
            this.currentJob = obj;
        },
        addTime(newTime) {
            // Reset validate
            this.validationErrors = '';
            this.validationSuccess = '';

            let uri = '/data/jobs';
            newTime.user_id = this.userID;
            newTime.date = this.dateFormatter(this.start_date);

            axios.post(uri, newTime)
                .then(res => {
                    this.validationSuccess = res.data.message;
                    this.fetchItems();
                })
                .catch(err => {
                    console.log(err);
                    if (err.response.status == 422) {
                        this.validationErrors = err.response.data;
                    }
                });
        },
        disabledEndDates() {
            let obj = {
                from: new Date(), // Disable all dates after specific date
                // days: [0], // Disable Saturday's and Sunday's
            };
            return obj;
        },
        customFormatter(date) {
            return moment(date).format('DD-MM-YYYY') !== 'Invalid date' ? moment(date).format('DD MMM YYYY') : '';
        },
        dateFormatter(date) {
            return moment(date).format('YYYY-MM-DD');
        },
        hourFormatter(totalSeconds) {
            var hours   = Math.floor(totalSeconds / 3600);
            var minutes = Math.floor((totalSeconds - (hours * 3600)) / 60);

            var result = (hours < 10 ? "0" + hours : hours);
            result += ":" + (minutes < 10 ? "0" + minutes : minutes);

            return result;
        },
        resetValidate() {
            this.validationSuccess = '';
            this.validationErrors = '';
        }
    },
    watch: {
        jobData: [{
            handler: 'getDataJobs'
        }],
        start_date: [{
            handler: 'fetchItems'
        }]
    }
}
</script>
<style lang="scss">
</style>