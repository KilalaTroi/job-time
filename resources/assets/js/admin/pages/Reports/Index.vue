<template>
  <div class="content">
    <div class="container-fluid">
      <card v-show="!action.new && !action.preview && !action.edit">
        <template slot="header">
          <div class="d-flex justify-content-between">
            <h4 class="card-title">
              {{ $ml.with("VueJS").get("txtFilter") }}
            </h4>
          </div>
        </template>

        <div class="row">
          <div class="col-sm-4">
            <div class="form-group">
              <label class="">{{
                $ml.with("VueJS").get("txtReportType")
              }}</label>
              <select-2 v-model="filters.type" class="select2">
                <option value="0">All</option>
                <option value="Trouble">{{ $ml.with("VueJS").get("txtTrouble") }}</option>
                <option value="Meeting">{{ $ml.with("VueJS").get("txtMeeting") }}</option>
                <option value="Notice">{{ $ml.with("VueJS").get("txtNotice") }}</option>
              </select-2>
            </div>
          </div>

          <div class="col-sm-4">
            <div class="form-group">
              <label class>{{ $ml.with("VueJS").get("txtStartDate") }}</label>
              <datepicker
                name="startDate"
                input-class="form-control"
                placeholder="Select Date"
                v-model="filters.start_date"
                :format="customFormatter"
                :disabled-dates="disabledEndDates(filters.end_date)"
                :language="getLangCode(this.$ml)"
              ></datepicker>
            </div>
          </div>

          <div class="col-sm-4">
            <div class="form-group">
              <label class>{{ $ml.with("VueJS").get("txtEndDate") }}</label>
              <datepicker
                name="endDate"
                input-class="form-control"
                placeholder="Select Date"
                v-model="filters.end_date"
                :format=" customFormatter"
                :disabled-dates="disabledStartDates(filters.start_date)"
                :language="getLangCode(this.$ml)"
              ></datepicker>
            </div>
          </div>

          <div class="col-sm-3">
            <div class="form-group">
              <label class>{{ $ml.with("VueJS").get("txtDepts") }}</label>
              <div>
                <multiselect
                  :multiple="false"
                  v-model="filters.department"
                  :options="options.departments"
                  :clear-on-select="true"
                  :placeholder="$ml.with('VueJS').get('txtSelectOne')"
                  label="text"
                  track-by="text"
                ></multiselect>
              </div>
            </div>
          </div>

          <div class="col-sm-3">
            <div class="form-group">
              <label class="">{{ $ml.with("VueJS").get("txtTeam") }}</label>
              <div>
                <select-2
                  :options="currentTeamOption"
                  v-model="filters.team"
                  class="select2"
                />
              </div>
            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <label class>{{ $ml.with("VueJS").get("txtProjects") }}</label>
              <div>
                <multiselect
                  :multiple="false"
                  v-model="filters.project"
                  :options="options.projects"
                  :clear-on-select="true"
                  :placeholder="$ml.with('VueJS').get('txtSelectOne')"
                  label="text"
                  track-by="text"
                ></multiselect>
              </div>
            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <label class>{{ $ml.with("VueJS").get("txtYearOfIssue") }}</label>
              <div>
                <multiselect
                  :multiple="false"
                  v-model="filters.issue_year"
                  :options="options.issues_year"
                  :clear-on-select="true"
                  :placeholder="$ml.with('VueJS').get('txtSelectOne')"
                  label="text"
                  track-by="text"
                ></multiselect>
              </div>
            </div>
          </div>

          <div class="col-sm-2">
            <div class="form-group">
              <label class>{{ $ml.with("VueJS").get("txtIssue") }}</label>
              <div>
                <multiselect
                  :multiple="false"
                  v-model="filters.issue"
                  :options="options.issues"
                  :clear-on-select="true"
                  :placeholder="$ml.with('VueJS').get('txtSelectOne')"
                  label="text"
                  track-by="text"
                ></multiselect>
              </div>
            </div>
          </div>
        </div>
      </card>

      <div class="form-group" v-show="!action.new && !action.preview && !action.edit">
        <button @click="addNewReport" class="btn btn-primary">
          <i class="fa fa-plus"></i>
          {{ $ml.with("VueJS").get("txtReportCreate") }}
        </button>
      </div>

      <div class="row">
        <div class="container">
          <add-new
            v-if="action.new"
            v-on:back-to-list="backToList"
          ></add-new>

          <!-- <edit
            :projectsParent="projects"
            :issuesParent="issues"
            :issuesYearParent="issuesYear"
            :currentReport="currentReport"
            :userID="userID"
            :departmentsParent="departments"
            :userOptionsParent="userOptions"
            v-if="actionEdit"
            v-on:back-to-list="backToList"
            v-on:update-seen="updateSeen"
            v-on:delete-report="deleteReport"
          ></edit>

          <preview
            :userOptions="userOptions"
            :currentReport="currentReport"
            v-if="actionPreview"
            v-on:back-to-list="backToList"
            v-on:update-seen="updateSeen"
            v-on:send-report="sendReport"
          ></preview> -->
        </div>
      </div>

      <card
        class="strpied-tabled-with-hover"
        v-show="!action.new && !action.preview && !action.edit"
      >
        <template slot="header">
          <div class="d-flex justify-content-between">
            <h4 class="card-title">
              {{ $ml.with("VueJS").get("txtReportList") }}
            </h4>
          </div>
        </template>

        <div class="table-responsive" :class="{ 'path-team': filters.team == 2 || filters.team == 3 }">
          <table-report
            :userID="userID"
            class="table-hover table-striped"
            :columns="columns"
            dataPath="reports"
            :data="reportData"
            v-on:view-report="viewReport"
            v-on:edit-report="editReport"
          ></table-report>
        </div>

        <pagination
          :data="reportData"
          :show-disabled="true"
          :limit="2"
          align="right"
          size="small"
          @pagination-change-page="getAll"
        ></pagination>
      </card>
    </div>
  </div>
</template>

<script>
import AddNew from "./AddNew";
import Edit from "./Edit";
import Preview from "./Preview";
import Card from "../../components/Cards/Card";
import Multiselect from "vue-multiselect";
import Datepicker from "vuejs-datepicker";
import { vi, ja, en } from "vuejs-datepicker/dist/locale";
import moment from "moment";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import TableReport from "../../components/TableReport";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    AddNew,
    Edit,
    Preview,
    Card,
    Datepicker,
    Multiselect,
    Select2,
    TableReport,
  },
  computed: {
    ...mapGetters({
      currentTeamOption: "currentTeamOption",
      currentTeam: "currentTeam",
      getObjectByID: "getObjectByID",
      getTeamText: "getTeamText",
      getLangCode: "getLangCode",
      customFormatter: "customFormatter",
      disabledStartDates: "disabledStartDates",
      disabledEndDates: "disabledEndDates"
    }),

    ...mapGetters('reports',{
      columns: "columns",
      filters: "filters",
      options: "options",
      reportData: "data",
      action: "action",
    }),

  },
  data() {
    return {
      userID: document.querySelector("meta[name='user-id']").getAttribute("content"),
      currentReport: {},
    };
  },

  methods: {
    ...mapActions({
      setCurrentTeam: "setCurrentTeam",
      updateReportNotify: "updateReportNotify",
      setReportNotify: "setReportNotify",
    }),
    ...mapActions('reports',{
      setColumns: "setColumns",
      getAll: "getAll",
      resetFilters: "resetFilters",
    }),

    addNewReport() {
      this.action.new = true;
    },
    viewReport(id, seen) {
      this.action.preview = true;
      this.currentReport = this.getObjectByID(this.reports.data, id);
      this.currentReport.isSeen = seen;
    },
    editReport(id, seen) {
      this.action.edit = true;
      this.currentReport = this.getObjectByID(this.reports.data, id);
      this.currentReport.isSeen = seen;
    },
    backToList(newData = false) {
      this.action.new = this.action.preview = this.action.edit = false;
      this.currentReport = {};

      if (newData) this.fetchDataFilter();
    },


    deleteReport(id) {
      if (confirm(this.$ml.with("VueJS").get("msgConfirmDelete"))) {
        let uri = "/data/reports-action/" + id;
        axios
          .delete(uri)
          .then((res) => {
            this.action.new = this.action.preview = this.action.edit = false;
            this.currentReport = {};
            this.fetchDataFilter();
          })
          .catch((err) => console.log(err));
      }
    },
    sendReport() {
      if (confirm("Send members about this update?")) {
        let uri = "/data/send-report";
        axios
          .post(uri, {
            userID: this.userID,
          })
          .then((res) => {
            console.log("Email was sent!");
          })
          .catch((err) => {
            console.log(err);
            alert("Could not send email!");
          });
      }
    },
  },
  async created() {
    const _this = this;
    _this.filters.team = _this.currentTeam.id;
    _this.setColumns(_this.$ml.current);
    $(document).on("click", ".languages button", function () {
      _this.setColumns(_this.$ml.current);
    })
	},
  watch: {
    filters: [
      {
        handler: function (value) {
          const _this = this;
          this.getAll(this.filters.page ? this.filters.page : 1);
          if (value.team != this.currentTeam.id) {
            this.setCurrentTeam(value.team);
            this.setReportNotify(value.team);
          }
          setTimeout(function(){ _this.resetFilters() }, 200)
        },
        deep: true
      },
    ],
  },
};
</script>

<style lang="scss">
@import "~vue-multiselect/dist/vue-multiselect.min.css";

.multiselect__single {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.table-responsive.path-team {
    .year-of-issue {
        display: none;
    }
}
</style>