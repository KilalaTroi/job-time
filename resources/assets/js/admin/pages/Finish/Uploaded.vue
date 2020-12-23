<template>
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col">
          <card>
            <div class="row">
              <div class="col-sm-3">
                <div class="form-group">
                  <label class>{{ $ml.with("VueJS").get("txtUsers") }}</label>
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
                  <label class>{{
                    $ml.with("VueJS").get("txtStartDate")
                  }}</label>
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
                  <label class>{{ $ml.with("VueJS").get("txtEndDate") }}</label>
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
                  <label class>{{ $ml.with("VueJS").get("txtDepts") }}</label>
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
                  <label class>{{
                    $ml.with("VueJS").get("txtProjects")
                  }}</label>
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
                    {{ $ml.with("VueJS").get("txtIssue") }}
                  </label>
                  <input
                    v-model="issue"
                    type="text"
                    name="issue"
                    class="form-control"
                  />
                </div>
              </div>
              <div class="col-sm-3">
                <div class="form-group">
                  <label class>{{ $ml.with("VueJS").get("txtTypes") }}</label>
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
                          <span
                            class="type-color"
                            :style="optionStyle(props.option.value)"
                          ></span>
                          {{ props.option.slug }}
                        </div>
                      </template>
                    </multiselect>
                  </div>
                </div>
              </div>
              <div class="col-sm-3">
                <div class="form-group">
                  <label class="">{{ $ml.with("VueJS").get("txtTeam") }}</label>
                  <div>
                    <select-2
                      :options="currentTeamOption"
                      v-model="team"
                      class="select2"
                    />
                  </div>
                </div>
              </div>
            </div>
          </card>
          <card>
            <template slot="header">
              <div class="d-flex justify-content-between">
                <h4 class="card-title">
                  {{ $ml.with("VueJS").get("txtFinishRecord") }}
                </h4>
                <div class="align-self-end">
                  <button @click="exportExcel" class="btn btn-primary">
                    <i class="fa fa-download"></i>
                    {{ $ml.with("VueJS").get("txtExportExcel") }}
                  </button>
                </div>
              </div>
            </template>
            <div class="table-responsive">
              <table-finished-upload
                v-show="!loading"
                class="table-hover table-striped"
                :columns="columns"
                :data="items"
                :total="total"
                v-on:get-process="getProcess"
                v-on:update-process="getProcess"
              ></table-finished-upload>
              <div v-if="loading" class="text-center mt-3">
                <img src="https://i.imgur.com/JfPpwOA.gif" />
              </div>
            </div>
            <process-detail-modal
              :currentProcess="currentProcess"
              :arrCurrentProcess="arrCurrentProcess"
              v-on:reset-validation="resetValidate"
            ></process-detail-modal>
            <pagination
              :data="processesUploaded"
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
import Multiselect from "vue-multiselect";
import TableFinishedUpload from "../../components/TableFinishedUpload";
import ProcessDetailModal from "./ProcessDetailModal";
import Card from "../../components/Cards/Card";
import Datepicker from "vuejs-datepicker";
import { vi, ja, en } from "vuejs-datepicker/dist/locale";
import moment from "moment";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    TableFinishedUpload,
    Card,
    Datepicker,
    Select2,
    ProcessDetailModal,
    Multiselect,
  },

  computed: {
    ...mapGetters({
      currentTeamOption: "currentTeamOption",
      currentTeam: "currentTeam",
      dateFormat: "dateFormat",
    }),
  },

  data() {
    return {
      columns: [
        {
          id: "d_name",
          value: this.$ml.with("VueJS").get("txtDepartment"),
          width: "120",
          class: "",
        },
        {
          id: "t_name",
          value: this.$ml.with("VueJS").get("txtJobType"),
          width: "120",
          class: "",
        },
        {
          id: "p_name",
          value: this.$ml.with("VueJS").get("txtProject"),
          width: "",
          class: "",
        },
        {
          id: "i_name",
          value: this.$ml.with("VueJS").get("txtIssue"),
          width: "120",
          class: "",
        },
        {
          id: "phase",
          value: this.$ml.with("VueJS").get("txtPhase"),
          width: "",
          class: "",
        },
        {
          id: "date",
          value: this.$ml.with("VueJS").get("txtDateTime"),
          width: "160",
          class: "",
        },
        {
          id: "user_name",
          value: this.$ml.with("VueJS").get("txtReporter"),
          width: "",
          class: "",
        },
        {
          id: "page",
          value: this.$ml.with("VueJS").get("txtPagesWorked"),
          width: "",
          class: "",
        },
        {
          id: "file",
          value: this.$ml.with("VueJS").get("txtFilesWorked"),
          width: "",
          class: "",
        },
        {
          id: "status",
          value: this.$ml.with("VueJS").get("txtStatus"),
          width: "135",
          class: "",
        },
      ],
      loading: true,
      txtAll: this.$ml.with("VueJS").get("txtSelectAll"),

      processesUploaded: {},
      processDetails: [],
			items: [],
			total: {
				page: 0,
				file: 0,
			},
      jLimit: 2,
      jShowDisabled: true,
      jAlign: "right",
      jSize: "small",

      currentProcess: {},
      arrCurrentProcess: [],
      dataLang: {
        vi: vi,
        ja: ja,
        en: en,
      },

      page: 1,

      // New filter
      user_id: [],
      users: [],
      userOptions: [],
      start_date: new Date(moment().startOf("month").format("YYYY/MM/DD")),
      end_date: new Date(),
      deptSelects: [],
      typeSelects: [],
      projectSelects: [],
      issue: "",
      team: "",
      departments: [],
      types: [],
      projects: [],
      firstLoad: 0,
    };
  },
  mounted() {
    const _this = this;
    $(document).on("click", ".languages button", function () {
      _this.txtAll = _this.$ml.with("VueJS").get("txtSelectAll");
      _this.columns = [
        {
          id: "d_name",
          value: _this.$ml.with("VueJS").get("txtDepartment"),
          width: "120",
          class: "",
        },
        {
          id: "t_name",
          value: _this.$ml.with("VueJS").get("txtJobType"),
          width: "120",
          class: "",
        },
        {
          id: "p_name",
          value: _this.$ml.with("VueJS").get("txtProject"),
          width: "",
          class: "",
        },
        {
          id: "i_name",
          value: _this.$ml.with("VueJS").get("txtIssue"),
          width: "120",
          class: "",
        },
        {
          id: "phase",
          value: _this.$ml.with("VueJS").get("txtPhase"),
          width: "",
          class: "",
        },
        {
          id: "date",
          value: _this.$ml.with("VueJS").get("txtDateTime"),
          width: "160",
          class: "",
        },
        {
          id: "user_name",
          value: _this.$ml.with("VueJS").get("txtReporter"),
          width: "",
          class: "",
        },
        {
          id: "page",
          value: _this.$ml.with("VueJS").get("txtPagesWorked"),
          width: "",
          class: "",
        },
        {
          id: "file",
          value: _this.$ml.with("VueJS").get("txtFilesWorked"),
          width: "",
          class: "",
        },
        {
          id: "status",
          value: _this.$ml.with("VueJS").get("txtStatus"),
          width: "135",
          class: "",
        },
      ];
    });
  },
  methods: {
    ...mapActions({
			setCurrentTeam: "setCurrentTeam",
    }),
    getProcessObjectValue(data, id, phase) {
      const arrProcess = data.filter((elem) => {
        if (elem.issue_id === id && elem.phase === phase) return elem;
      });

      return arrProcess;
    },
    exportExcel() {
      const uri = "/data/finish/export-excel";
      axios
        .post(uri, {
          user_id: this.user_id,
          start_date: this.dateFormatter(this.start_date),
          end_date: this.dateFormatter(this.end_date),
          deptSelects: this.deptSelects,
          typeSelects: this.typeSelects,
          projectSelects: this.projectSelects,
          issueFilter: this.issue,
          team: this.team,
        })
        .then((res) => {
          window.open(res.data, "_blank");
        })
        .catch((err) => {
          alert("Error!");
        });
    },
    fetchData(page = 1, loading = true) {
      this.page = page;
      const uri = "/data/finish/uploaded?page=" + page;
      this.loading = loading;
      this.total = {
        page: 0,
        file: 0,
      }
      axios
        .post(uri, {
          user_id: this.user_id,
          start_date: this.dateFormatter(this.start_date),
          end_date: this.dateFormatter(this.end_date),
          deptSelects: this.deptSelects,
          typeSelects: this.typeSelects,
          projectSelects: this.projectSelects,
          issueFilter: this.issue,
          team: this.team,
        })
        .then((res) => {
          this.users = res.data.users;
          this.types = res.data.types;
          this.departments = res.data.departments;
          this.projects = res.data.projects;
          this.processesUploaded = res.data.processesUploaded;
          this.processDetails = res.data.processDetails;
          this.firstLoad++;

          if (res.data.processesUploaded.data.length) {
            this.items = res.data.processesUploaded.data.map((item, index) => {
              const arrProcess = this.processDetails.length ? this.getProcessObjectValue( this.processDetails, item.id, item.phase ) : [];
              if (arrProcess.length) {
                this.total.page += arrProcess.reduce((total, item) => {
                  return total + item.page * 1;
                }, 0);
                this.total.file += arrProcess.reduce((total, item) => {
                  return total + item.file * 1;
                }, 0);
							}
              const lastProcess = arrProcess[arrProcess.length - 1];
              return Object.assign({}, item, {
                d_name: item.department === "All" ? "" : item.department,
                p_name: item.project,
                i_name: item.issue,
                t_name: item.job_type,
                status: arrProcess.length ? lastProcess.status : "",
                page: arrProcess.length ? arrProcess.reduce((total, item) => { return total + item.page * 1; }, 0) : "",
                file: arrProcess.length ? arrProcess.reduce((total, item) => { return total + item.file * 1; }, 0) : "",
                user_name: arrProcess.length ? lastProcess.user_name : "",
                date: arrProcess.length ? this.dateFormat(lastProcess.date, "MMM DD, YYYY HH:mm") : "",
              });
            });
          } else {
            this.items = [];
          }
        })
        .catch((err) => {
          console.log(err);
          alert("Could not load data");
        });

      this.loading = false;
    },
    getProcess(item) {
      this.currentProcess = Object.assign({}, item, { status: null });
      this.arrCurrentProcess = this.processDetails.length
        ? this.getProcessObjectValue(
            this.processDetails,
            this.currentProcess.id,
            this.currentProcess.phase
          )
        : [];
    },
    customFormatter(date) {
      return moment(date).format("YYYY/MM/DD");
    },
    disabledEndDates() {
      const obj = {
        from: new Date(),
      };
      return obj;
    },
    dateFormatter(date) {
      return moment(date).format("YYYY-MM-DD") !== "Invalid date"
        ? moment(date).format("YYYY-MM-DD")
        : false;
    },
    resetValidate() {
      this.currentProcess = {};
      this.fetchData(this.page, false);
    },
    getLanguage(data) {
      return this.dataLang[data.current];
    },
    disabledStartDates() {
      const obj = {
        to: new Date(this.start_date), // Disable all dates after specific date
        from: new Date(), // Disable all dates after specific date
        // days: [0], // Disable Saturday's and Sunday's
      };
      return obj;
    },
    disabledEndDates() {
      const obj = {
        from: new Date(this.end_date), // Disable all dates after specific date
        // days: [0], // Disable Saturday's and Sunday's
      };
      return obj;
    },
    getUserOptions() {
      this.userOptions = this.users;
    },
    optionStyle(color) {
      return {
        backgroundColor: color,
      };
    },
  },
  async created() {
    this.team = this.currentTeam ? this.currentTeam.id : "";
		await this.fetchData();
	},
  watch: {
    users: [
      {
        handler: "getUserOptions",
      },
    ],
    txtAll: [
      {
        handler: "getUserOptions",
      },
    ],
    start_date: [
      {
        handler: "fetchData",
      },
    ],
    end_date: [
      {
        handler: "fetchData",
      },
    ],
    user_id: [
      {
        handler: "fetchData",
      },
    ],
    deptSelects: [
      {
        handler: "fetchData",
      },
    ],
    typeSelects: [
      {
        handler: "fetchData",
      },
    ],
    projectSelects: [
      {
        handler: "fetchData",
      },
    ],
    issue: [
      {
        handler: "fetchData",
      },
    ],
    team: [
      {
        handler: function (value) {
          if ( value != this.currentTeam.id ){
            this.setCurrentTeam(value);
            this.fetchData();
          }
        },
      },
    ],
  },
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
  > span {
    display: block;
    height: 38px;
    position: relative;
    overflow: hidden;
  }
  button {
    position: absolute;
    right: 0;
    top: 0;
    width: 110px;
    .less {
      display: none;
    }
  }
  &.active {
    > span {
      height: auto;
    }
    button {
      .less {
        display: inline;
      }
      .more {
        display: none;
      }
    }
  }
}
</style>
