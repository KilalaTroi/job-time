<template>
  <div class="content">
    <div class="container-fluid">
      <card>
        <div class="row">
          <div class="col-sm-3">
            <div class="form-group">
              <label class>{{$ml.with('VueJS').get('txtUsers')}}</label>
              <div>
                <select2
                  :options="userOptions"
                  v-model="user_id"
                  class="select2 no-disable-first-value"
                >
                  <option disabled value="0">All</option>
                </select2>
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
                  placeholder="Pick some"
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
                  placeholder="Pick some"
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
                  placeholder="Pick some"
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
          <no-action-table class="table-hover table-striped" :columns="columns" :data="logTime"></no-action-table>
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
import NoActionTable from "../../components/TableNoAction";
import Card from "../../components/Cards/Card";
import CreateButton from "../../components/Buttons/Create";
import Select2Type from "../../components/SelectTwo/SelectTwoType.vue";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import Multiselect from "vue-multiselect";
import Datepicker from "vuejs-datepicker";
import { vi, ja } from "vuejs-datepicker/dist/locale";
import moment from "moment";

const tableColumns = [
  { id: "username", value: "Username", width: "", class: "" },
  { id: "date", value: "Date", width: "120", class: "" },
  { id: "start_time", value: "Start Time", width: "120", class: "" },
  { id: "end_time", value: "End Time", width: "120", class: "" },
  { id: "total", value: "Time", width: "120", class: "" },
  { id: "d_name", value: "Department", width: "120", class: "" },
  { id: "p_name", value: "Project", width: "", class: "" },
  { id: "i_name", value: "Issue", width: "120", class: "" },
  { id: "t_name", value: "Job Type", width: "120", class: "" }
];

export default {
  components: {
    NoActionTable,
    Card,
    CreateButton,
    Select2,
    Select2Type,
    Datepicker,
    Multiselect
  },

  data() {
    return {
      user_id: 0,
      columns: [...tableColumns],
      users: [],
      userOptions: [],
      start_date: moment(new Date()).format("YYYY/MM") + "/01",
      end_date: new Date(),
      deptSelects: [],
      typeSelects: [],
      projectSelects: [],
      issue: "",

      departments: [],
      types: [],
      projects: [],

      logTimeData: {},
      logTime: [],
      jLimit: 2,
      jShowDisabled: true,
      jAlign: "right",
      jSize: "small"
    };
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    fetchData() {
      let uri = "/data/statistic/totaling/";
      axios
        .post(uri, {
          user_id: this.user_id,
          start_date: this.dateFormatter(this.start_date),
          end_date: this.dateFormatter(this.end_date),
          deptSelects: this.deptSelects,
          typeSelects: this.typeSelects,
          projectSelects: this.projectSelects,
          issueFilter: this.issue
        })
        .then(res => {
          this.users = res.data.users;
          this.logTimeData = res.data.dataLogTime;
          this.departments = res.data.departments;
          this.types = res.data.types;
          this.projects = res.data.projects;
        })
        .catch(err => {
          console.log(err);
          alert("Could not load users");
        });
    },
    optionStyle(color) {
      return {
        backgroundColor: color
      };
    },
    fetchDataFilter() {
      let uri = "/data/statistic/totaling/";
      axios
        .post(uri, {
          user_id: this.user_id,
          start_date: this.dateFormatter(this.start_date),
          end_date: this.dateFormatter(this.end_date),
          deptSelects: this.deptSelects,
          typeSelects: this.typeSelects,
          projectSelects: this.projectSelects,
          issueFilter: this.issue
        })
        .then(res => {
          this.logTimeData = res.data.dataLogTime;
        })
        .catch(err => {
          console.log(err);
          alert("Could not load data");
        });
    },
    getResults(page = 1) {
      axios
        .post("/data/statistic/totaling?page=" + page, {
          user_id: this.user_id,
          start_date: this.dateFormatter(this.start_date),
          end_date: this.dateFormatter(this.end_date),
          deptSelects: this.deptSelects,
          typeSelects: this.typeSelects,
          projectSelects: this.projectSelects,
          issueFilter: this.issue
        })
        .then(res => {
          this.logTimeData = res.data.dataLogTime;
        });
    },
    exportExcel() {
      let uri = "/data/export-report-time-user/";
      axios
        .post(uri, {
          user_id: this.user_id,
          start_date: this.dateFormatter(this.start_date),
          end_date: this.dateFormatter(this.end_date),
          deptSelects: this.deptSelects,
          typeSelects: this.typeSelects,
          projectSelects: this.projectSelects,
          issueFilter: this.issue
        })
        .then(res => {
          window.open(res.data, "_blank");
        })
        .catch(err => {
          alert("Error!");
        });
    },
    getUserOptions(data) {
      if (data.length) {
        let obj = {
          id: 0,
          text: "All"
        };
        this.userOptions = [obj].concat(data);
      }
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
            username: item.username,
            date: this.customFormatter2(item.date),
            start_time: item.start_time,
            end_time: item.end_time,
            total: this.hourFormatter(item.total),
            d_name: item.department === "All" ? "" : item.department,
            p_name: item.project,
            i_name: item.issue,
            t_name: item.job_type
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
      return data.current === "vi" ? vi : ja;
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
    ]
  }
};
</script>
<style lang="scss">
@import "~vue-multiselect/dist/vue-multiselect.min.css";
.type-color {
  width: 60px;
  height: 20px;
  margin-right: 5px;
  display: inline-block;
  vertical-align: middle;
}
.select2-container--default
  .select2-selection--multiple
  .select2-selection__choice {
  display: flex;
  padding: 0 5px 3px;
}
</style>
