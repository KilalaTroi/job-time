<template>
	<div class="content">
		<div class="container-fluid">
			<card>
				<div class="row">
					<div class="col-sm-3">
						<div class="form-group">
							<label class>{{$ml.with('VueJS').get('txtUsers')}}</label>
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
								:preselect-first="true"
								></multiselect>
							</div>
						</div>
					</div>
					<div class="col-sm-3">
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
					<div class="col-sm-3">
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
					<div class="col-sm-3">
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
					<div class="col-sm-3">
						<div class="form-group">
							<label class>{{$ml.with('VueJS').get('txtProjects')}}</label>
							<div>
								<multiselect
								:multiple="true"
								v-model="projectSelects"
								:options="projects"
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
					<div class="col-sm-3">
						<div class="form-group">
							<label class>
								{{$ml.with('VueJS').get('txtIssue')}}
							</label>
							<input v-model="issue" type="text" name="issue" class="form-control" />
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<label class>{{$ml.with('VueJS').get('txtTypes')}}</label>
							<div>
								<multiselect
								:multiple="true"
								v-model="typeSelects"
								:options="types"
								:clear-on-select="false"
								:preserve-search="true"
								:placeholder="$ml.with('VueJS').get('txtPickSome')"
								label="slug"
								track-by="slug"
								:preselect-first="true"
								>
									<template slot="option" slot-scope="props">
										<div>
											<span class="type-color" :style="optionStyle(props.option.value)"></span>
											{{ props.option.slug }}
										</div>
									</template>
								</multiselect>
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<label class="">{{$ml.with('VueJS').get('txtTeam')}}</label>
							<div>
								<select-2 :options="currentTeamOption" v-model="team" class="select2" />
							</div>
						</div>
					</div>
				</div>
			</card>

			<card class="strpied-tabled-with-hover">
				<template slot="header">
					<div class="d-flex justify-content-between">
						<h4 class="card-title">
							{{$ml.with('VueJS').get('txtTimeRecord')}}             
						</h4>
						<div class="align-self-end">
							<button @click="exportExcel" class="btn btn-primary">
								<i class="fa fa-download"></i>
								{{$ml.with('VueJS').get('txtExportExcel')}}
							</button>
						</div>
					</div>
				</template>
				<div class="table-responsive">
					<table-no-action class="table-hover table-striped" :columns="columns" :data="logTime"></table-no-action>
					<div v-if="!logTimeData.data" class="text-center mt-3">
                        <img src="https://i.imgur.com/JfPpwOA.gif">
                    </div>
				</div>
				<pagination
				:data="logTimeData"
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
import TableNoAction from "../../components/TableNoAction";
import Card from "../../components/Cards/Card";
import Select2 from '../../components/SelectTwo/SelectTwo.vue'
import Multiselect from "vue-multiselect";
import Datepicker from "vuejs-datepicker";
import { vi, ja, en } from "vuejs-datepicker/dist/locale";
import moment from "moment";
import { mapGetters, mapActions } from "vuex"

export default {
	components: {
		TableNoAction,
		Card,
		Datepicker,
		Select2,
		Multiselect
	},

	computed: {
        ...mapGetters({
			currentTeamOption: 'currentTeamOption',
			currentTeam: 'currentTeam',
			getObjectByID: 'getObjectByID',
			getTeamText: 'getTeamText'
        }),
    },

	data() {
		return {
			user_id: [],
			columns: [
				{ id: "username", value: this.$ml.with('VueJS').get('lblUsername'), width: "", class: "" },
				{ id: "date", value: this.$ml.with('VueJS').get('lblDate'), width: "120", class: "" },
				{ id: "start_time", value: this.$ml.with('VueJS').get('lblStartTime'), width: "120", class: "" },
				{ id: "end_time", value: this.$ml.with('VueJS').get('lblEndTime'), width: "120", class: "" },
				{ id: "total", value: this.$ml.with('VueJS').get('lblTime'), width: "120", class: "" },
				{ id: "d_name", value: this.$ml.with('VueJS').get('txtDepartment'), width: "120", class: "" },
				{ id: "p_name", value: this.$ml.with('VueJS').get('txtProject'), width: "", class: "" },
				{ id: "i_name", value: this.$ml.with('VueJS').get('txtIssue'), width: "120", class: "" },
				{ id: "t_name", value: this.$ml.with('VueJS').get('txtJobType'), width: "120", class: "" },
				{ id: 'html_team', value: this.$ml.with('VueJS').get('txtTeam'), width: '', class: 'text-center' },
			],
			users: [],
			userOptions: [],
			start_date: new Date(moment().startOf('month').format('YYYY/MM/DD')),
			end_date: new Date(),
			deptSelects: [],
			typeSelects: [],
			projectSelects: [],
			issue: "",
			team: "",
			txtAll: this.$ml.with('VueJS').get('txtSelectAll'),

			departments: [],
			types: [],
			projects: [],

			logTimeData: {},
			logTime: [],
			jLimit: 2,
			jShowDisabled: true,
			jAlign: "right",
			jSize: "small",
			dataLang: {
                vi: vi,
                ja: ja,
                en: en
			},
			firstLoad: 0
		};
	},
	mounted() {
		let _this = this
		_this.team = _this.currentTeam ? _this.currentTeam.id : ""
		if ( _this.team ) _this.fetchData()
		console.log('1111111111', _this.team)
		$(document).on('click', '.languages button', function() {
			_this.txtAll = _this.$ml.with('VueJS').get('txtSelectAll')
			_this.columns = [
				{ id: "username", value: _this.$ml.with('VueJS').get('lblUsername'), width: "", class: "" },
				{ id: "date", value: _this.$ml.with('VueJS').get('lblDate'), width: "120", class: "" },
				{ id: "start_time", value: _this.$ml.with('VueJS').get('lblStartTime'), width: "120", class: "" },
				{ id: "end_time", value: _this.$ml.with('VueJS').get('lblEndTime'), width: "120", class: "" },
				{ id: "total", value: _this.$ml.with('VueJS').get('lblTime'), width: "120", class: "" },
				{ id: "d_name", value: _this.$ml.with('VueJS').get('txtDepartment'), width: "120", class: "" },
				{ id: "p_name", value: _this.$ml.with('VueJS').get('txtProject'), width: "", class: "" },
				{ id: "i_name", value: _this.$ml.with('VueJS').get('txtIssue'), width: "120", class: "" },
				{ id: "t_name", value: _this.$ml.with('VueJS').get('txtJobType'), width: "120", class: "" },
				{ id: 'html_team', value: _this.$ml.with('VueJS').get('txtTeam'), width: '', class: 'text-center' },
			];
		});
	},
	methods: {
		fetchData() {
			let uri = "/data/statistic/datatotaling";
			axios
			.post(uri, {
				user_id: this.user_id,
				start_date: this.dateFormatter(this.start_date),
				end_date: this.dateFormatter(this.end_date),
				deptSelects: this.deptSelects,
				typeSelects: this.typeSelects,
				projectSelects: this.projectSelects,
				issueFilter: this.issue,
				team: this.team
			})
			.then(res => {
				this.users = res.data.users;
				this.logTimeData = res.data.dataLogTime;
				this.departments = res.data.departments;
				this.types = res.data.types;
				this.projects = res.data.projects;
				this.firstLoad++;
			})
			.catch(err => {
				console.log(err);
				alert("Could not load data");
			});
		},
		optionStyle(color) {
			return {
				backgroundColor: color
			};
		},
		fetchDataFilter() {
			let uri = "/data/statistic/datatotaling";
			axios
			.post(uri, {
				user_id: this.user_id,
				start_date: this.dateFormatter(this.start_date),
				end_date: this.dateFormatter(this.end_date),
				deptSelects: this.deptSelects,
				typeSelects: this.typeSelects,
				projectSelects: this.projectSelects,
				issueFilter: this.issue,
				team: this.team
			})
			.then(res => {
				this.logTimeData = res.data.dataLogTime;
				this.projects = res.data.projects;
			})
			.catch(err => {
				console.log(err);
				alert("Could not load data");
			});
		},
		getResults(page = 1) {
			axios
			.post("/data/statistic/datatotaling?page=" + page, {
				user_id: this.user_id,
				start_date: this.dateFormatter(this.start_date),
				end_date: this.dateFormatter(this.end_date),
				deptSelects: this.deptSelects,
				typeSelects: this.typeSelects,
				projectSelects: this.projectSelects,
				issueFilter: this.issue,
				team: this.team
			})
			.then(res => {
				this.logTimeData = res.data.dataLogTime;
			});
		},
		exportExcel() {
			let uri = "/data/export-report-time-user";
			axios
			.post(uri, {
				user_id: this.user_id,
				start_date: this.dateFormatter(this.start_date),
				end_date: this.dateFormatter(this.end_date),
				deptSelects: this.deptSelects,
				typeSelects: this.typeSelects,
				projectSelects: this.projectSelects,
				issueFilter: this.issue,
				team: this.team
			})
			.then(res => {
				window.open(res.data, "_blank");
			})
			.catch(err => {
				alert("Error!");
			});
		},
		getUserOptions() {
			// let data = this.users;
			// if (data.length) {
			// 	let obj = {
			// 		id: 0,
			// 		text: this.txtAll
			// 	};
			// 	this.userOptions = [obj].concat(data);
			// }
			this.userOptions = this.users;
		},
		getObjectValue(data, id) {
			let obj = data.filter(elem => {
				if (elem.id === id) return elem;
			});

			if (obj.length > 0) return obj[0];
		},
		getDataLogTime(logTimeData) {
			if (logTimeData.data.length) {
				this.logTime = logTimeData.data.map((item, index) => {
					return {
						username: this.getObjectValue(this.users, item.user_id).text,
						date: this.customFormatter2(item.date),
						start_time: item.start_time,
						end_time: item.end_time,
						total: this.hourFormatter(item.total),
						d_name: item.department === "All" ? "" : item.department,
						p_name: item.project,
						i_name: item.issue,
						t_name: item.job_type,
						html_team: this.getTeamText('' + item.team)
					};
				});
			} else {
				this.logTime = [];
			}
		},
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
hourFormatter(totalSeconds) {
	var hours = Math.floor(totalSeconds / 3600);
	var minutes = Math.floor((totalSeconds - hours * 3600) / 60);

	var result = hours < 10 ? "0" + hours : hours;
	result += "h " + (minutes < 10 ? "0" + minutes : minutes) + "m";

	return result;
},
resetValidate() {
	this.validationSuccess = "";
	this.validationErrors = "";
},
getLanguage(data) {
	return this.dataLang[data.current]
},
setTeam() {
	this.team = this.currentTeam.id
}
},
watch: {
	logTimeData: [
	{
		handler: "getDataLogTime"
	}
	],
	users: [
	{
		handler: "getUserOptions"
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
	user_id: [
	{
		handler: "fetchDataFilter"
	}
	],
	deptSelects: [
	{
		handler: "fetchDataFilter"
	}
	],
	typeSelects: [
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
	team: [
	{
		handler: function() {
			if ( this.team ) {
				if ( this.firstLoad >= 1 && this.team ) {
					this.fetchData()
				}
			}
		}
	}
	],
	currentTeamOption: {
		handler: function() {
			this.setTeam()
			this.fetchData()
		}
	}
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
