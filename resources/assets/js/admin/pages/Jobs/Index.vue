<template>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col col-sm-auto">
                    <card>
                        <template slot="header">
                            <h4 class="card-title text-center">{{ this.customFormatter(start_date) }}</h4>
                        </template>
                        <datepicker name="startDate" v-model="start_date" :format="customFormatter" :inline="true" :disabled-dates="disabledEndDates()" :language="getLanguage(this.$ml)">
                        </datepicker>
                    </card>
                </div>
                <div class="col">
                    <card>
                        <template slot="header">
                            <div class="d-flex justify-content-between">
                                <h4 class="card-title">{{$ml.with('VueJS').get('txtJobsList')}}</h4>
                                <div class="form-group mb-0 d-flex justify-content-between" style="min-width: 160px;">
                                    <select-2
                                        :options="currentTeamOption"
                                        v-model="selectTeam"
                                        class="select2"
                                    ></select-2>
                                    <div class="ml-3"></div>
                                    <select-2 v-model="showFilter" :options="optionsFilter" class="select2"></select-2>
                                </div>
                            </div>
                        </template>
                        <div class="table-responsive" v-if="jobs">
                            <table-job
                                class="table-hover table-striped"
                                :columns="columns"
                                :data="jobs"
                                v-on:get-job="getJob">
                            </table-job>
                        </div>
                        <pagination :data="jobData" :show-disabled="jShowDisabled" :limit="jLimit" :align="jAlign" :size="jSize" @pagination-change-page="getResults"></pagination>
                    </card>
                    <card>
                        <template slot="header">
                            <h4 class="card-title">{{$ml.with('VueJS').get('txtTimeRecord')}}</h4>
                        </template>
                        <div class="table-responsive" :class="{'path-team': selectTeam == 2}">
                            <table-action
                                class="table-hover table-striped time-record"
                                :columns="logColumns"
                                :data="logTime"
                                v-on:get-item="getItem" 
                                v-on:delete-item="deleteItem">
                            </table-action>
                        </div>
                        <div class="alert alert-danger" v-if="timeTotal > 28800">
                          <span>{{$ml.with('VueJS').get('msgOverTime')}}</span>
                        </div>
                    </card>
                </div>
            </div>

            <AddTime :team="selectTeam" :currentJob="currentJob" :logTimeData="logTimeData" :errors="validationErrors" :success="validationSuccess" v-on:overlap-time="overlapTime" v-on:add-time="addTime" v-on:reset-validation="resetValidate"></AddTime>
            <EditTime :team="selectTeam" :currentTimeLog="currentTimeLog" :logTimeData="logTimeData" :errors="validationErrors" :success="validationSuccess"  v-on:overlap-time="overlapTime" v-on:update-time="updateTime" v-on:reset-validation="resetValidate"></EditTime>
        </div>
    </div>
</template>
<script>
import Card from '../../components/Cards/Card'
import Datepicker from 'vuejs-datepicker';
import { vi, ja, en } from 'vuejs-datepicker/dist/locale'
import moment from 'moment'
import TableJob from '../../components/TableJob'
import TableAction from '../../components/TableAction'
import AddTime from './AddTime'
import EditTime from './EditTime'
import Select2 from '../../components/SelectTwo/SelectTwo.vue'
import { mapGetters, mapActions } from "vuex"

export default {
    components: {
        Card,
        datepicker: Datepicker,
        TableJob,
        TableAction,
        AddTime,
        EditTime,
        Select2
    },
    computed: {
        ...mapGetters({
            currentTeamOption: "currentTeamOption", 
            currentTeam: "currentTeam"
        })
    },
    data() {
        return {
            userID: document.querySelector("meta[name='user-id']").getAttribute('content'),
            userCreatedAt: document.querySelector("meta[name='user-created-at']").getAttribute('content'),
            columns: [
                { id: 'department', value: this.$ml.with('VueJS').get('txtDepartment'), width: '', class: '' },
                { id: 'project', value: this.$ml.with('VueJS').get('txtProject'), width: '', class: '' },
                { id: 'issue', value: this.$ml.with('VueJS').get('txtIssue'), width: '60', class: 'text-center' },
                { id: 'phase', value: this.$ml.with('VueJS').get('txtPhase'), width: '', class: 'text-center' },
                { id: 'time', value: this.$ml.with('VueJS').get('lblTime'), width: '110', class: 'text-center' }
            ],
            logColumns: [
                { id: 'project', value: this.$ml.with('VueJS').get('txtProject'), width: '', class: '' },
                { id: 'issue', value: this.$ml.with('VueJS').get('txtIssue'), width: '60', class: 'text-center' },
                { id: 'note', value: this.$ml.with('VueJS').get('txtWork'), width: '', class: 'note' },
                { id: 'phase', value: this.$ml.with('VueJS').get('txtPhase'), width: '', class: 'text-center' },
                { id: 'start_time', value: this.$ml.with('VueJS').get('lblStartTime'), width: '110', class: 'text-center' },
                { id: 'end_time', value: this.$ml.with('VueJS').get('lblEndTime'), width: '110', class: 'text-center' },
                { id: 'total', value: this.$ml.with('VueJS').get('lblTime'), width: '110', class: 'text-center' }
            ],
            departments: [],
            jobs: [],
            allJobs: [],
            jobData: {},
            logTime: [],
            logTimeData: [],
            timeTotal: 0,
            jobsTime: [],
            optionsFilter: [],
            // schedules: [],
            currentJob: null,
            currentTimeLog: null,

            start_date: new Date(),

            validationErrors: '',
            validationSuccess: '',

            jLimit: 2,
            jShowDisabled: true,
            jAlign: 'right',
            jSize: 'small',

            showFilter: 'showSchedule',
            dataLang: {
                vi: vi,
                ja: ja,
                en: en
            },
            selectTeam: ''
        }
    },
    mounted() {
        let _this = this;
        this.selectTeam = this.currentTeam.id
        _this.getOptions();
        
        $(document).on('click', '.languages button', function() {
            _this.getOptions();
            _this.showFilter = 'showSchedule';

            _this.columns = [
                { id: 'department', value: _this.$ml.with('VueJS').get('txtDepartment'), width: '', class: '' },
                { id: 'project', value: _this.$ml.with('VueJS').get('txtProject'), width: '', class: '' },
                { id: 'issue', value: _this.$ml.with('VueJS').get('txtIssue'), width: '60', class: 'text-center' },
                { id: 'phase', value: _this.$ml.with('VueJS').get('txtPhase'), width: '', class: 'text-center' },
                { id: 'time', value: _this.$ml.with('VueJS').get('lblTime'), width: '110', class: 'text-center' }
            ];

            _this.logColumns = [
                { id: 'project', value: _this.$ml.with('VueJS').get('txtProject'), width: '', class: '' },
                { id: 'issue', value: _this.$ml.with('VueJS').get('txtIssue'), width: '60', class: 'text-center' },
                { id: 'note', value: _this.$ml.with('VueJS').get('txtWork'), width: '', class: 'note' },
                { id: 'phase', value: _this.$ml.with('VueJS').get('txtPhase'), width: '', class: 'text-center' },
                { id: 'start_time', value: _this.$ml.with('VueJS').get('lblStartTime'), width: '110', class: 'text-center' },
                { id: 'end_time', value: _this.$ml.with('VueJS').get('lblEndTime'), width: '110', class: 'text-center' },
                { id: 'total', value: _this.$ml.with('VueJS').get('lblTime'), width: '110', class: 'text-center' }
            ]
        });
    },
    methods: {
        fetchItems() {
            let uri = '/data/jobs?date=' + this.dateFormatter(this.start_date) + '&user_id=' + this.userID + '&show=' + this.showFilter + '&team_id=' + this.selectTeam;
            axios.get(uri)
                .then(res => {
                    this.departments = res.data.departments;
                    this.jobData = res.data.jobs;
                    this.allJobs = res.data.allJobs;
                    this.jobsTime = res.data.jobsTime;
                    this.logTimeData = res.data.logTime;
                    // this.schedules = res.data.schedules;
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not load projects");
                });
        },
        getOptions() {
            let arr = [
                {id: 'showSchedule', text: this.$ml.with('VueJS').get('txtShowBySchedule')},
                {id: 'all', text: this.$ml.with('VueJS').get('txtShowAll')}
            ];
            this.optionsFilter = [...arr];
        },
        changeShowFilter() {
            let uri = '/data/jobs?date=' + this.dateFormatter(this.start_date) + '&user_id=' + this.userID + '&show=' + this.showFilter + '&team_id=' + this.selectTeam;
            axios.get(uri)
                .then(res => {
                    this.jobData = res.data.jobs;
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not load projects");
                });
        },
        getResults(page = 1) {
            axios.get('/data/jobs?page=' + page + '&date=' + this.dateFormatter(this.start_date) + '&user_id=' + this.userID + '&show=' + this.showFilter + '&team_id=' + this.selectTeam)
                .then(response => {
                    this.jobData = response.data.jobs; 
                });
        },
        getObjectValue(data, id) {
            let obj = data.filter((elem) => {
                if (elem.id === id) return elem;
            });

            if (obj.length > 0)
                return obj[0];
        },
         getJobObjectValue(data, id, schedule_id) {
            let obj = data.filter((elem) => {
                if (elem.id === id && elem.schedule_id === schedule_id) return elem;
            });

            if (obj.length > 0)
                return obj[0];
        },
        checkTimeTotal() {
            return this.timeTotal > 28800 ? true : false;
        },
        getDataJobs(jobData) {
            if (jobData.data.length) {
                this.jobs = jobData.data.map((item, index) => {
                    let time = typeof(this.getJobObjectValue(this.jobsTime, item.id, item.schedule_id)) !== 'undefined' ? this.getJobObjectValue(this.jobsTime, item.id, item.schedule_id).total : false;
                    let checkTR = item.type.includes("_tr") ? " (TR)" : "";
                    return Object.assign({}, item, {
                        department: this.getObjectValue(this.departments, item.dept_id).text != 'All' ? this.getObjectValue(this.departments, item.dept_id).text : '',
                        project: item.p_name + checkTR,
                        issue: item.i_name,
                        time: time ? this.hourFormatter(time) : '00:00'
                    })
                });
            } else {
                this.jobs = [];
            }
        },
        getDataLogTime(logTimeData) {
            if (logTimeData.length) {
                let dataTimes = logTimeData.filter((item) => {
                    return typeof(this.getObjectValue(this.allJobs, item.issue_id)) !== 'undefined';
                }).map((item, index) => {
                    let issue = this.getObjectValue(this.allJobs, item.issue_id);
                    let checkTR = issue.type.includes("_tr") ? " (TR)" : "";
                    return Object.assign({}, item, {
                        project: issue.p_name + checkTR,
                        issue: issue.i_name,
                        total: item.total ? this.hourFormatter(item.total) : '00:00'
                    })
                });

                if ( dataTimes.length ) {
                    this.timeTotal = this.totalArrayObject(logTimeData);
                    let total = {
                        id: '',
                        project: '',
                        issue: '',
                        note: '',
                        start_time: '',
                        end_time: 'Total:',
                        total: this.timeTotal ? this.hourFormatter(this.timeTotal) : '00:00'
                    };
                    dataTimes.push(total);
                }
                
                this.logTime = dataTimes;
            } else {
                this.logTime = [];
            }
        },
        getJob(id, schedule_id) {
            let job = this.getJobObjectValue(this.jobs, id, schedule_id);
            let obj = {
                id: job.id,
                schedule_id: job.schedule_id,
                p_name: job.project,
                i_name: job.issue,
                date: this.start_date
            };
            this.currentJob = obj;
        },
        getItem(id) {
            this.validationErrors = '';
            this.validationSuccess = '';

            let time = this.getObjectValue(this.logTime, id);
            let obj = {
                id: time.id,
                p_name: time.project,
                i_name: time.issue,
                phase: time.phase,
                note: time.note,
                start_time: time.start_time,
                end_time: time.end_time,
                date: this.start_date
            };
            this.currentTimeLog = obj;
        },
        addTime(newTime) {
            // Reset validate
            this.validationErrors = '';
            this.validationSuccess = '';

            let uri = '/data/jobs';
            newTime.user_id = this.userID;
            newTime.team_id = this.selectTeam;
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
        overlapTime(data) {
            this.validationSuccess = '';
            if ( data ) {
                this.validationErrors = data;
            } else {
                this.validationErrors = '';
            }
        },
        updateTime(newTime) {
            // Reset validate
            this.validationErrors = '';
            this.validationSuccess = '';

            let uri = '/data/jobs/' + this.currentTimeLog.id;
            axios.patch(uri, newTime)
                .then(res => {
                    this.validationSuccess = res.data.message;
                    this.fetchItems();
                    $('#itemDetail button[data-dismiss]').trigger('click');
                })
                .catch(err => {
                    this.validationErrors = err.response.data;
                    this.validationSuccess = '';
                });
        },
        deleteItem(id) {
            if (confirm(this.$ml.with('VueJS').get('msgConfirmDelete'))) {
                let uri = "/data/jobs/" + id;
                axios
                    .delete(uri)
                    .then(res => {
                        this.fetchItems();
                        console.log(res.data);
                    })
                    .catch(err => console.log(err));
            }
        },
        disabledEndDates() {
            let obj = {
                to: new Date(this.userCreatedAt),
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
        totalArrayObject(arr) {
            return arr.reduce((total, item) => { return total + item.total }, 0);
        },
        resetValidate() {
            this.validationSuccess = '';
            this.validationErrors = '';
        },
        getLanguage(data) {
            return this.dataLang[data.current]
        },
    },
    watch: {
        jobData: [{
            handler: 'getDataJobs'
        }],
        logTimeData: [{
            handler: 'getDataLogTime'
        }],
        start_date: [{
            handler: 'fetchItems'
        }],
        showFilter: [{
            handler: 'changeShowFilter'
        }],
        selectTeam: [{
            handler: function(value, oldValue) {
                if ( value != oldValue ) this.fetchItems()
            }
        }]
    }
}
</script>
<style lang="scss">
.time-record tr:last-child button {
    display: none;
}
.table-responsive:not(.path-team) {
    .note {
        display: none;
    }
}
</style>