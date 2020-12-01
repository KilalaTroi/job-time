<template>
	<div class="content">
		<div class="container-fluid">
            <div class="row">
                <!-- <div class="col col-sm-auto">
                    <card>
                        <template slot="header">
                            <h4 class="card-title text-center">{{ this.customFormatter(start_date) }}</h4>
                        </template>
                        <datepicker name="startDate" v-model="start_date" :format="customFormatter" :inline="false" :disabled-dates="disabledEndDates()" :language="getLanguage(this.$ml)">
                        </datepicker>
                    </card>
                </div> -->
                <div class="col">
                    <card>
                        <template slot="header">
                            <div class="d-flex justify-content-between">
                                <h4 class="card-title">{{$ml.with('VueJS').get('txtFinish')}} List</h4>
                                <div class="form-group mb-0 d-flex justify-content-between" style="min-width: 160px;">
									<div class="d-flex align-items-stretch mr-3">
										<label class="mr-2 mb-0 d-flex align-items-center text-dark">{{ $ml.with('VueJS').get('lblDate') }}</label>
										<div style="min-width: 110px">
											<datepicker 
												input-class="form-control" 
												name="startDate" 
												v-model="start_date" 
												:format="customFormatter" 
												:inline="false" :disabled-dates="disabledEndDates()" :language="getLanguage(this.$ml)">
											</datepicker>
										</div>
									</div>
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
                        <div class="table-responsive">
							<table-finish class="table-hover table-striped" :columns="columns" :data="projects" v-on:get-process="getProcess" v-on:update-process="getProcess"></table-finish>
						</div>
						<process-modal :currentProcess="currentProcess" :arrCurrentProcess="arrCurrentProcess" v-on:reset-validation="resetValidate"></process-modal>
						<process-detail-modal :currentProcess="currentProcess" :arrCurrentProcess="arrCurrentProcess" v-on:reset-validation="resetValidate"></process-detail-modal>
						<pagination
						:data="dataProjects"
						:show-disabled="jShowDisabled"
						:limit="jLimit"
						:align="jAlign"
						:size="jSize"
						@pagination-change-page="fetchData"
						></pagination>
					</card>
                </div>
            </div>
        </div>
	</div>
</template>
<script>
import TableFinish from "../../components/TableFinish";
import ProcessModal from './ProcessModal';
import ProcessDetailModal from './ProcessDetailModal';
import CommentsModal from './CommentsModal';
import Card from "../../components/Cards/Card";
import Datepicker from "vuejs-datepicker";
import { vi, ja, en } from "vuejs-datepicker/dist/locale";
import moment from "moment";
import Select2 from '../../components/SelectTwo/SelectTwo.vue'
import { mapGetters, mapActions } from "vuex"

export default {
	components: {
		TableFinish,
		Card,
		Datepicker,
		Select2,
		ProcessModal,
		ProcessDetailModal
	},

	computed: {
        ...mapGetters({
            currentTeamOption: "currentTeamOption", 
			currentTeam: "currentTeam",
			dateFormat: "dateFormat"
        })
    },

	data() {
		return {
			columns: [
				{ id: "d_name", value: this.$ml.with('VueJS').get('txtDepartment'), width: "120", class: "" },
				{ id: "t_name", value: this.$ml.with('VueJS').get('txtJobType'), width: "120", class: "" },
				{ id: "p_name", value: this.$ml.with('VueJS').get('txtProject'), width: "", class: "" },
				{ id: "i_name", value: this.$ml.with('VueJS').get('txtIssue'), width: "120", class: "" },
				{ id: "phase", value: this.$ml.with('VueJS').get('txtPhase'), width: "", class: "" },
				{ id: "date", value: this.$ml.with('VueJS').get('lblDate'), width: "160", class: "" },
				{ id: "user_name", value: this.$ml.with('VueJS').get('txtReporter'), width: "", class: "" },
				{ id: "page", value: this.$ml.with('VueJS').get('txtPagesWorked'), width: "", class: "" },
				{ id: "status", value: this.$ml.with('VueJS').get('txtStatus'), width: "135", class: "" }
			],
			start_date: new Date(),
			selectTeam: '',
			txtAll: this.$ml.with('VueJS').get('txtSelectAll'),

			dataProjects: {},
			dataProcesses: [],
			projects: [],

			jLimit: 2,
			jShowDisabled: true,
			jAlign: "right",
			jSize: "small",

			showFilter: 'showSchedule',
			optionsFilter: [],

			currentProcess: {},
			arrCurrentProcess: [],
			dataLang: {
                vi: vi,
                ja: ja,
                en: en
			},
			
			page: 1
		};
	},
	mounted() {
		let _this = this;
		_this.selectTeam = _this.currentTeam.id
		_this.getOptions();
		$(document).on('click', '.languages button', function() {
			_this.txtAll = _this.$ml.with('VueJS').get('txtSelectAll')
			_this.columns = [
				{ id: "d_name", value: _this.$ml.with('VueJS').get('txtDepartment'), width: "120", class: "" },
				{ id: "t_name", value: _this.$ml.with('VueJS').get('txtJobType'), width: "120", class: "" },
				{ id: "p_name", value: _this.$ml.with('VueJS').get('txtProject'), width: "", class: "" },
				{ id: "i_name", value: _this.$ml.with('VueJS').get('txtIssue'), width: "120", class: "" },
				{ id: "phase", value: _this.$ml.with('VueJS').get('txtPhase'), width: "", class: "" },
				{ id: "date", value: _this.$ml.with('VueJS').get('lblDate'), width: "160", class: "" },
				{ id: "user_name", value: _this.$ml.with('VueJS').get('txtReporter'), width: "", class: "" },
				{ id: "page", value: _this.$ml.with('VueJS').get('txtPagesWorked'), width: "", class: "" },
				{ id: "status", value: _this.$ml.with('VueJS').get('txtStatus'), width: "135", class: "" }
			];
			_this.getOptions();
            _this.showFilter = 'showSchedule';
		});
	},
	methods: {
		getProcessObjectValue(data, id, phase) {
            const arrProcess = data.filter((elem) => {
                if (elem.issue_id === id && elem.phase === phase) return elem;
            });

            return arrProcess;
        },
		fetchData(page = 1) {
			this.page = page;
			let uri = "/data/finish/uploaded?page=" + page;
			axios
			.post(uri, {
				start_date: this.dateFormatter(this.start_date),
				selectTeam: this.selectTeam,
				showFilter: this.showFilter
			})
			.then(res => {
				this.dataProjects = res.data.dataProjects;
				this.dataProcesses = res.data.dataProcesses;

				if (res.data.dataProjects.data.length) {
					this.projects = res.data.dataProjects.data.map((item, index) => {
						const arrProcess = this.dataProcesses.length ? this.getProcessObjectValue(this.dataProcesses, item.id, item.phase) : [];
						const lastProcess = arrProcess[arrProcess.length - 1];
						return Object.assign({}, item, {
							d_name: item.department === "All" ? "" : item.department,
							p_name: item.project,
							i_name: item.issue,
							t_name: item.job_type,
							status: arrProcess.length ? lastProcess.status : '',
							page: arrProcess.length ? arrProcess.reduce((total, item) => { return total + (item.page*1) }, 0) : '',
							user_name: arrProcess.length ? lastProcess.user_name : '',
							date: arrProcess.length ? this.dateFormat(lastProcess.date, 'MMM DD, YYYY HH:mm') : '',
						});
					});
				} else {
					this.projects = [];
				}
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
		getProcess(item) {
			this.currentProcess = Object.assign({}, item, {status: null});
			this.arrCurrentProcess = this.dataProcesses.length ? this.getProcessObjectValue(this.dataProcesses, this.currentProcess.id, this.currentProcess.phase) : [];
		},
		changeStatusProcess(item) {
			item.status = item.status ? 0 : 1;
			
			const uri = "/data/finish/update-status";
			axios.post(uri, {
					currentProcess: item
				})
				.then(res => {
					this.fetchData(this.page)
				})
				.catch(err => {
					console.log(err);
				});
            
		},
		customFormatter(date) {
			return moment(date).format("YYYY/MM/DD");
		},
		disabledEndDates() {
			let obj = {
				from: new Date()
			};
			return obj;
		},
		dateFormatter(date) {
			return moment(date).format("YYYY-MM-DD") !== 'Invalid date' ? moment(date).format("YYYY-MM-DD") : false;
		},
		resetValidate() {
			this.currentProcess = {};
			this.fetchData(this.page)
		},
		getLanguage(data) {
			return this.dataLang[data.current]
		}
	},
	watch: {
		selectTeam: [{
            handler: function(value, oldValue) {
                if ( value != oldValue ) this.fetchData()
            }
        }],
		start_date: [
		{
			handler: "fetchData"
		}
		],
		showFilter: [
		{
			handler: "fetchData"
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
.message-content {
>span {display: block; height: 38px; position: relative; overflow: hidden;}
button {position: absolute; right: 0; top: 0; width: 110px;
.less{display:none;}
}
&.active {
>span {height: auto;}
button {
.less{display:inline;}	
.more{display:none;}	
}
}
}
</style>
