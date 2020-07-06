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
                                <option value="0">Select type</option>
                                <option value="trouble">Trouble</option>
                                <option value="meeting">Meeting</option>
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
					<div class="col-sm-4" v-show="deptSelects">
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
					<div class="col-sm-4" v-show="projectSelects">
						<div class="form-group">
							<label class>
								{{$ml.with('VueJS').get('txtIssue')}}
							</label>
							<input v-model="issue" type="text" name="issue" class="form-control" />
						</div>
					</div>
				</div>
		    </card>
            <div class="form-group" v-show="!actionNewReport">
                <button @click="addNewReport" class="btn btn-primary">Create New Report</button>
            </div>
            <add-new v-show="actionNewReport" v-on:finish-new-report="finishNewReport"></add-new>
			<card class="strpied-tabled-with-hover">
				<template slot="header">
					<div class="d-flex justify-content-between">
						<h4 class="card-title">
							Report List           
						</h4>
					</div>
				</template>
				<div class="table-responsive">
					<table-upload class="table-hover table-striped" :columns="columns" :data="projects"></table-upload>
				</div>
				<pagination
				:data="dataProjects"
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
            report_type: 0,
            start_date: new Date(moment().startOf('month').format('YYYY/MM/DD')),
			end_date: new Date(),
			deptSelects: null,
			projectSelects: null,
			issue: "",
			txtAll: this.$ml.with('VueJS').get('txtSelectAll'),

			departments: [],
            projects: [],
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
				issueFilter: this.issue
			})
			.then(res => {
				this.departments = res.data.departments;
				this.projects = res.data.projects;
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
				issueFilter: this.issue
			})
			.then(res => {
				this.projects = res.data.projects;
			})
			.catch(err => {
				console.log(err);
				alert("Could not load data");
			});
        },
        addNewReport() {
            this.actionNewReport = true;
        },
        finishNewReport() {
            this.actionNewReport = false;
        }
    },
    watch: {
        deptSelects: [{
            handler: "fetchDataFilter"
        }]
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