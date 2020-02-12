<template>
    <div class="content">
        <div class="container-fluid">
            <card>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="">Users</label>
                            <div>
                                <select2 :options="userOptions" v-model="user_id" class="select2 no-disable-first-value">
                                    <option disabled value="0">All</option>
                                </select2>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="">Start date</label>
                            <datepicker name="startDate" input-class="form-control" placeholder="Select Date" v-model="start_date" :format="customFormatter" :disabled-dates="disabledEndDates()">
                            </datepicker>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="">End date</label>
                            <datepicker name="endDate" input-class="form-control" placeholder="Select Date" v-model="end_date" :format="customFormatter" :disabled-dates="disabledStartDates()">
                            </datepicker>
                        </div>
                    </div>
                </div>
                
            </card>

            <card class="strpied-tabled-with-hover">
                <template slot="header">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title">Time Record</h4>
                        <div class="align-self-end"><a :href="exportLink" target="_blank"><i class="fa fa-download"></i> Export excel</a></div>
                    </div>
                </template>
                <div class="table-responsive">
                    <no-action-table
                            class="table-hover table-striped"
                            :columns="columns"
                            :data="logTime">
                    </no-action-table>
                </div>
                <pagination :data="logTimeData" :show-disabled="jShowDisabled" :limit="jLimit" :align="jAlign" :size="jSize" @pagination-change-page="getResults"></pagination>
            </card>
        </div>
    </div>
</template>
<script>
    import NoActionTable from "../../components/TableNoAction";
    import Card from "../../components/Cards/Card";
    import CreateButton from "../../components/Buttons/Create";
    import Select2 from '../../components/SelectTwo/SelectTwo.vue';
    import Datepicker from 'vuejs-datepicker';
    import moment from 'moment';

    const tableColumns = [
        { id: "username", value: "Username", width: "", class: "" },
        { id: "date", value: "Date", width: "120", class: "" },
        { id: 'start_time', value: 'Start Time', width: '120', class: '' },
        { id: 'end_time', value: 'End Time', width: '120', class: '' },
        { id: 'total', value: 'Time', width: '120', class: '' },
        { id: "d_name", value: "Department", width: "120", class: "" },
        { id: "p_name", value: "Project", width: "", class: "" },
        { id: "i_name", value: "Issue", width: "120", class: "" },
        { id: "t_name", value: "Job Type", width: "120", class: "" },
    ];

    export default {
        components: {
            NoActionTable,
            Card,
            CreateButton,
            Select2,
            Datepicker
        },

        data() {
            return {
                user_id: 0,
                columns: [...tableColumns],
                users: [],
                userOptions: [],
                start_date: moment(new Date()).format('YYYY/MM') + '/01',
                end_date: new Date(),

                logTimeData: {},
                logTime: [],
                jLimit: 2,
                jShowDisabled: true,
                jAlign: 'right',
                jSize: 'small',

                exportLink: ''
            };
        },
        mounted() {
            this.fetchData();
        },
        methods: {
            fetchData() {
                this.exportLink = "/data/export-report-time-user/" + this.user_id + "/" + this.dateFormatter(this.start_date) + "/" + this.dateFormatter(this.end_date);
                let uri = "/data/statistic/totaling/" + this.user_id + "/" + this.dateFormatter(this.start_date) + "/" + this.dateFormatter(this.end_date);
                axios
                    .get(uri)
                    .then(res => {
                        this.users = res.data.users;
                        this.logTimeData = res.data.dataLogTime;
                    })
                    .catch(err => {
                        console.log(err);
                        alert("Could not load users");
                    });
            },
            fetchDataFilter() {
                this.exportLink = "/data/export-report-time-user/" + this.user_id + "/" + this.dateFormatter(this.start_date) + "/" + this.dateFormatter(this.end_date);
                let uri = "/data/statistic/totaling/" + this.user_id + "/" + this.dateFormatter(this.start_date) + "/" + this.dateFormatter(this.end_date);
                axios
                    .get(uri)
                    .then(res => {
                        this.logTimeData = res.data.dataLogTime;
                    })
                    .catch(err => {
                        console.log(err);
                        alert("Could not load dât");
                    });
            },
            getResults(page = 1) {
                axios.get("/data/statistic/totaling/" + this.user_id + "/" + this.dateFormatter(this.start_date) + "/" + this.dateFormatter(this.end_date) + "/?page=" + page)
                    .then(res => {
                        this.logTimeData = res.data.dataLogTime;
                    });
            },
            getUserOptions(data) {
                if (data.length) {
                    let obj = {
                        id: 0,
                        text: 'All'
                    };
                    this.userOptions = [obj].concat(data);
                }
            },
            getObjectValue(data, id) {
                let obj = data.filter((elem) => {
                    if (elem.id === id) return elem;
                });

                if (obj.length > 0)
                    return obj[0];
            },
            getDataLogTime(logTimeData) {
                if (logTimeData.data.length) {
                    this.logTime = logTimeData.data.map((item, index) => {
                        return {
                            username: item.username,
                            date: this.customFormatter2(item.date),
                            start_time: item.start_time,
                            end_time: item.end_time,
                            total: this.hourFormatter(item.total),
                            d_name: item.department === "All" ? '' : item.department,
                            p_name: item.project,
                            i_name: item.issue,
                            t_name: item.job_type,
                        };
                    });
                } else {
                    this.logTime = [];
                }
            },
            customFormatter(date) {
                return moment(date).format('YYYY/MM/DD');
            },
            customFormatter2(date) {
                return moment(date).format('DD-MM-YYYY') !== 'Invalid date' ? moment(date).format('MMM DD, YYYY') : '';
            },
            disabledStartDates() {
                let obj = {
                    to: new Date(this.start_date), // Disable all dates after specific date
                    from: new Date(), // Disable all dates after specific date
                    // days: [0], // Disable Saturday's and Sunday's
                };
                return obj;
            },
            disabledEndDates() {
                let obj = {
                    from: new Date(this.end_date), // Disable all dates after specific date
                    // days: [0], // Disable Saturday's and Sunday's
                };
                return obj;
            },
            dateFormatter(date) {
                return moment(date).format('YYYY-MM-DD');
            },
            hourFormatter(totalSeconds) {
                var hours   = Math.floor(totalSeconds / 3600);
                var minutes = Math.floor((totalSeconds - (hours * 3600)) / 60);

                var result = (hours < 10 ? "0" + hours : hours);
                result += "h " + (minutes < 10 ? "0" + minutes : minutes) + 'm';

                return result;
            },
            resetValidate() {
                this.validationSuccess = "";
                this.validationErrors = "";
            }
        },
        watch: {
            logTimeData: [{
                handler: 'getDataLogTime'
            }],
            users: [{
                handler: 'getUserOptions'
            }],
            start_date: [{
                handler: 'fetchDataFilter'
            }],
            end_date: [{
                handler: 'fetchDataFilter'
            }],
            user_id: [{
                handler: 'fetchDataFilter'
            }]
        }
    };
</script>
