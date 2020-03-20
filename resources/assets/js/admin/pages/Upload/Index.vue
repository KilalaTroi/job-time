<template>
	<div class="content">
		<div class="container">
			<card>
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							<label class>{{$ml.with('VueJS').get('txtShowBy')}}</label>
							<div>
								<select-2 v-model="showFilter" :options="optionsFilter" class="select2"></select-2>
							</div>
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
								:multiple="true"
								v-model="deptSelects"
								:options="departments"
								:clear-on-select="false"
								:preserve-search="true"
								:placeholder="$ml.with('VueJS').get('txtPickSome')"
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
								:multiple="true"
								v-model="projectSelects"
								:options="projectOptions"
								:clear-on-select="false"
								:preserve-search="true"
								:placeholder="$ml.with('VueJS').get('txtPickSome')"
								label="text"
								track-by="text"
								:preselect-first="true"
								></multiselect>
							</div>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class>
								{{$ml.with('VueJS').get('txtIssue')}}
							</label>
							<input v-model="issue" type="text" name="issue" class="form-control" />
						</div>
					</div>
				</div>
			</card>

			<card class="strpied-tabled-with-hover">
				<template slot="header">
					<div class="d-flex justify-content-between">
						<h4 class="card-title">
							{{$ml.with('VueJS').get('txtUploadList')}}           
						</h4>
					</div>
				</template>
				<div class="table-responsive">
					<upload-table class="table-hover table-striped" :columns="columns" :data="projects" v-on:get-process="getProcess"></upload-table>
				</div>
				<process-modal :currentProcess="currentProcess" v-on:reset-validation="resetValidate"></process-modal>
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
import UploadTable from "../../components/TableUpload";
import ProcessModal from './ProcessModal'
import Card from "../../components/Cards/Card";
import Multiselect from "vue-multiselect";
import Datepicker from "vuejs-datepicker";
import { vi, ja, en } from "vuejs-datepicker/dist/locale";
import moment from "moment";
import Select2 from '../../components/SelectTwo/SelectTwo.vue'

export default {
	components: {
		UploadTable,
		Card,
		Datepicker,
		Multiselect,
		Select2,
		ProcessModal
	},

	data() {
		return {
			columns: [
				{ id: "d_name", value: this.$ml.with('VueJS').get('txtDepartment'), width: "120", class: "" },
				{ id: "p_name", value: this.$ml.with('VueJS').get('txtProject'), width: "", class: "" },
				{ id: "i_name", value: this.$ml.with('VueJS').get('txtIssue'), width: "120", class: "" },
				{ id: "t_name", value: this.$ml.with('VueJS').get('txtJobType'), width: "120", class: "" },
				{ id: "phase", value: this.$ml.with('VueJS').get('txtPhase'), width: "160", class: "" }
			],
			start_date: "",
			end_date: "",
			deptSelects: [],
			projectSelects: [],
			issue: "",
			txtAll: this.$ml.with('VueJS').get('txtSelectAll'),

			departments: [],
			dataProjects: {},
			projects: [],
			projectOptions: [],

			jLimit: 2,
			jShowDisabled: true,
			jAlign: "right",
			jSize: "small",

			showFilter: 'showSchedule',
			optionsFilter: [],

			currentProcess: {},
			dataLang: {
                vi: vi,
                ja: ja,
                en: en
            }
		};
	},
	mounted() {
		let _this = this;
		_this.fetchData();
		_this.getOptions();
		$(document).on('click', '.languages button', function() {
			_this.txtAll = _this.$ml.with('VueJS').get('txtSelectAll')
			_this.columns = [
				{ id: "d_name", value: _this.$ml.with('VueJS').get('txtDepartment'), width: "120", class: "" },
				{ id: "p_name", value: _this.$ml.with('VueJS').get('txtProject'), width: "", class: "" },
				{ id: "i_name", value: _this.$ml.with('VueJS').get('txtIssue'), width: "120", class: "" },
				{ id: "t_name", value: _this.$ml.with('VueJS').get('txtJobType'), width: "120", class: "" },
				{ id: "phase", value: _this.$ml.with('VueJS').get('txtPhase'), width: "160", class: "" }
			];
			_this.getOptions();
            _this.showFilter = 'showSchedule';
		});
	},
	methods: {
		fetchData() {
			let uri = "/data/upload/data";
			axios
			.post(uri, {
				start_date: this.dateFormatter(this.start_date),
				end_date: this.dateFormatter(this.end_date),
				deptSelects: this.deptSelects,
				projectSelects: this.projectSelects,
				issueFilter: this.issue,
				showFilter: this.showFilter
			})
			.then(res => {
				this.departments = res.data.departments;
				this.projectOptions = res.data.projectOptions;
				this.dataProjects = res.data.dataProjects;
			})
			.catch(err => {
				console.log(err);
				alert("Could not load data");
			});
		},
		getOptions() {
            let arr = [
                {id: 'showSchedule', text: this.$ml.with('VueJS').get('txtShowBySchedule')},
                {id: 'all', text: this.$ml.with('VueJS').get('txtShowAll')}
            ];
            this.optionsFilter = [...arr];
        },
		fetchDataFilter() {
			let uri = "/data/upload/data";
			axios
			.post(uri, {
				start_date: this.dateFormatter(this.start_date),
				end_date: this.dateFormatter(this.end_date),
				deptSelects: this.deptSelects,
				projectSelects: this.projectSelects,
				issueFilter: this.issue,
				showFilter: this.showFilter
			})
			.then(res => {
				this.dataProjects = res.data.dataProjects;
			})
			.catch(err => {
				console.log(err);
				alert("Could not load data");
			});
		},
		getResults(page = 1) {
			axios
			.post("/data/upload/data?page=" + page, {
				start_date: this.dateFormatter(this.start_date),
				end_date: this.dateFormatter(this.end_date),
				deptSelects: this.deptSelects,
				projectSelects: this.projectSelects,
				issueFilter: this.issue,
				showFilter: this.showFilter
			})
			.then(res => {
				this.dataProjects = res.data.dataProjects;
			});
		},
		getObjectValue(data, id) {
			let obj = data.filter(elem => {
				if (elem.id === id) return elem;
			});

			if (obj.length > 0) return obj[0];
		},
		getDataProjects(dataProjects) {
			if (dataProjects.data.length) {
				this.projects = dataProjects.data.map((item, index) => {
					return {
						id: item.id,
						d_name: item.department === "All" ? "" : item.department,
						p_name: item.project,
						i_name: item.issue,
						t_name: item.job_type,
						phase: item.phase
					};
				});
			} else {
				this.projects = [];
			}
		},
		getProcess(id) {
			this.currentProcess = this.getObjectValue(this.dataProjects.data, id);
		},
		customFormatter(date) {
			return moment(date).format("YYYY/MM/DD");
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
				from: this.end_date ? new Date(this.end_date) : new Date() // Disable all dates after specific date
				// days: [0], // Disable Saturday's and Sunday's
			};
			return obj;
		},
		dateFormatter(date) {
			return moment(date).format("YYYY-MM-DD") !== 'Invalid date' ? moment(date).format("YYYY-MM-DD") : false;
		},
		resetValidate() {
			this.currentProcess = {};
		},
		getLanguage(data) {
			return this.dataLang[data.current]
		}
	},
	watch: {
		dataProjects: [
		{
			handler: "getDataProjects"
		}
		],
		txtAll: [
		{
			handler: "getUserOptions"
		}
		],
		start_date: [
		{
			handler: "fetchDataFilter"
		}
		],
		end_date: [
		{
			handler: "fetchDataFilter"
		}
		],
		deptSelects: [
		{
			handler: "fetchDataFilter"
		}
		],
		projectSelects: [
		{
			handler: "fetchDataFilter"
		}
		],
		issue: [
		{
			handler: "fetchDataFilter"
		}
		],
		showFilter: [
		{
			handler: "fetchDataFilter"
		}
		]
	}
};
</script>
<style lang="scss">
@import "~vue-multiselect/dist/vue-multiselect.min.css";
.type-color {
	width: 30px;
	height: 20px;
	margin-right: 5px;
	display: inline-block;
	vertical-align: middle;
}
</style>
