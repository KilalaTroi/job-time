<template>
    <div class="content">
        <div class="container">
            <card v-show="!actionNewReport">
				<template slot="header">
					<div class="d-flex justify-content-between">
						<h4 class="card-title">
							Filter Report          
						</h4>
					</div>
				</template>
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							<label class="">Report Type</label>
                            <select-2 v-model="report_type" class="select2">
                                <option value="Trouble">Trouble</option>
                                <option value="Meeting">Meeting</option>
                            </select-2>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class>{{$ml.with('VueJS').get('txtStartDate')}}</label>
							<datepicker
							name="startDate"
							input-class="form-control"
							placeholder="Select Date"
							v-model="start_date"
							:format="customFormatter"
							:disabled-dates="disabledEndDates()"
							:language="getLanguage(this.$ml)"
							></datepicker>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class>{{$ml.with('VueJS').get('txtEndDate')}}</label>
							<datepicker
							name="endDate"
							input-class="form-control"
							placeholder="Select Date"
							v-model="end_date"
							:format="customFormatter"
							:disabled-dates="disabledStartDates()"
							:language="getLanguage(this.$ml)"
							></datepicker>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class>{{$ml.with('VueJS').get('txtDepts')}}</label>
							<div>
								<multiselect
								:multiple="false"
								v-model="deptSelects"
								:options="departments"
								:clear-on-select="true"
								:preserve-search="false"
								:placeholder="$ml.with('VueJS').get('txtSelectOne')"
								label="text"
								track-by="text"
								:preselect-first="true"
								></multiselect>
							</div>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class>{{$ml.with('VueJS').get('txtProjects')}}</label>
							<div>
								<multiselect
								:multiple="false"
								v-model="projectSelects"
								:options="projects"
								:clear-on-select="true"
								:preserve-search="false"
								:placeholder="$ml.with('VueJS').get('txtSelectOne')"
								label="text"
								track-by="text"
								:preselect-first="true"
								></multiselect>
							</div>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class>{{$ml.with('VueJS').get('txtIssue')}}</label>
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
								:preselect-first="true"
								></multiselect>
							</div>
						</div>
					</div>
				</div>
		    </card>

            <div class="form-group" v-show="!actionNewReport">
                <button @click="addNewReport" class="btn btn-primary"><i class="fa fa-plus"></i> Create New Report</button>
            </div>

            <add-new v-if="actionNewReport" v-on:finish-new-report="finishNewReport"></add-new>

			<card class="strpied-tabled-with-hover" v-show="!actionNewReport">
				<template slot="header">
					<div class="d-flex justify-content-between">
						<h4 class="card-title">
							Report List           
						</h4>
					</div>
				</template>
				<div class="table-responsive">
					<table-report class="table-hover table-striped" :columns="columns" :data="reports.data"></table-report>
				</div>
				<pagination
				:data="reports"
				:show-disabled="jShowDisabled"
				:limit="jLimit"
				:align="jAlign"
				:size="jSize"
				@pagination-change-page="getResults"
				></pagination>
			</card>
        </div>
    </div>
</template>
<script>
import AddNew from './AddNew';
import Card from "../../components/Cards/Card";
import Multiselect from "vue-multiselect";
import Datepicker from "vuejs-datepicker";
import { vi, ja, en } from "vuejs-datepicker/dist/locale";
import moment from "moment";
import Select2 from '../../components/SelectTwo/SelectTwo.vue';
import TableReport from "../../components/TableReport";

export default {
    components: {
        AddNew,
        Card,
		Datepicker,
        Multiselect, 
		Select2,
		TableReport
    },
    data() {
        return {
			columns: [
				{ id: "type", value: 'Report Type', width: "120", class: "" },
				{ id: "date_time", value: 'Report Date', width: "", class: "" },
				{ id: "dept_name", value: 'Department', width: "", class: "" },
				{ id: "project_name", value: 'Project', width: "", class: "" },
				{ id: "issue_name", value: 'Issue', width: "120", class: "" },
				{ id: "title", value: 'Title', width: "120", class: "" }
			],
            report_type: 'Trouble',
            start_date: new Date(moment().startOf('month').format('YYYY/MM/DD')),
			end_date: new Date(),
			deptSelects: null,
			projectSelects: null,
			issueSelects: null,
			txtAll: this.$ml.with('VueJS').get('txtSelectAll'),

			departments: [],
			projects: [],
			issues: [],
			reports: {},
            dataLang: {
                vi: vi,
                ja: ja,
                en: en
            },

			actionNewReport: false,
			jLimit: 2,
			jShowDisabled: true,
			jAlign: "right",
			jSize: "small",
        }
    },
    mounted() {
		let _this = this;
		_this.fetchData();
    },
	methods: {
        customFormatter(date) {
			return moment(date).format("YYYY/MM/DD");
		},
		customFormatter2(date) {
			return moment(date).format("DD-MM-YYYY") !== "Invalid date"
			? moment(date).format("MMM DD, YYYY")
			: "";
		},
		disabledStartDates() {
			let obj = {
                to: new Date(this.start_date), // Disable all dates after specific date
                from: new Date() // Disable all dates after specific date
                // days: [0], // Disable Saturday's and Sunday's
            };
            return obj;
        },
        disabledEndDates() {
            let obj = {
                from: new Date(this.end_date) // Disable all dates after specific date
                // days: [0], // Disable Saturday's and Sunday's
            };
            return obj;
        },
        dateFormatter(date) {
            return moment(date).format("YYYY-MM-DD");
        },
        getLanguage(data) {
            return this.dataLang[data.current]
        },
        fetchData() {
			let uri = "/data/reports";
			axios
			.post(uri, {
				user_id: this.user_id,
				deptSelects: this.deptSelects,
				projectSelects: this.projectSelects,
				issueSelects: this.issueSelects
			})
			.then(res => {
				this.departments = res.data.departments;
				this.projects = res.data.projects;
				this.issues = res.data.issues;
				this.reports = res.data.reports;
			})
			.catch(err => {
				console.log(err);
				alert("Could not load data");
			});
        },
        fetchDataFilter() {
			let uri = "/data/reports";
			axios
			.post(uri, {
				user_id: this.user_id,
				deptSelects: this.deptSelects,
				projectSelects: this.projectSelects,
				issueSelects: this.issueSelects
			})
			.then(res => {
				this.projects = res.data.projects;
				this.issues = res.data.issues;
				this.reports = res.data.reports;
			})
			.catch(err => {
				console.log(err);
				alert("Could not load data");
			});
        },
        addNewReport() {
            this.actionNewReport = true;
        },
        finishNewReport(newData = false) {
			this.actionNewReport = false;
			
			if ( newData ) this.fetchDataFilter();
		},
		getResults(page = 1) {
			let uri = "/data/reports?page=" + page;
			axios
			.post(uri, {
				user_id: this.user_id,
				deptSelects: this.deptSelects,
				projectSelects: this.projectSelects,
				issueSelects: this.issueSelects
			})
			.then(res => {
				this.projects = res.data.projects;
				this.issues = res.data.issues;
				this.reports = res.data.reports;
			})
			.catch(err => {
				console.log(err);
				alert("Could not load data");
			});
		},
		resetProject() {
			this.projectSelects = null;
		},
		resetIssue() {
			this.issueSelects = null;
		}
    },
    watch: {
        deptSelects: [
			{ handler: "fetchDataFilter" },
			{ handler: "resetProject" }
		],
		projectSelects: [
			{ handler: "fetchDataFilter" },
			{ handler: "resetIssue" }
		]
    }
}
</script>

<style lang="scss">
@import "~vue-multiselect/dist/vue-multiselect.min.css";

.multiselect__single {
	white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>