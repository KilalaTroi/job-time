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
                $ml.with("VueJS").get("txtType")
              }}</label>
              <select-2 v-model="filters.type" class="select2">
                <option value="0">All</option>
                <option value="Notice">{{ $ml.with("VueJS").get("txtNotice") }}</option>
                <option value="Trouble">{{ $ml.with("VueJS").get("txtTrouble") }}</option>
                <option value="Meeting">{{ $ml.with("VueJS").get("txtMeeting") }}</option>
              </select-2>
            </div>
          </div>

          <div class="col-sm-4">
            <div class="form-group">
              <label class>{{ $ml.with("VueJS").get("txtStartDate") }}</label>
              <datepicker
                name="startDate"
                input-class="form-control"
                :placeholder="$ml.with('VueJS').get('txtSelectDate')"
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
                :placeholder="$ml.with('VueJS').get('txtSelectDate')"
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
          <add-new v-if="action.new" />
          <edit v-if="action.edit" />
          <preview v-if="action.preview" />
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
    }),

    ...mapActions('reports',{
      setColumns: "setColumns",
      getAll: "getAll",
      resetFilters: "resetFilters",
      editReport: "editReport",
      viewReport: "viewReport",
    }),

    addNewReport() {
      this.action.new = true;
    },
  },

  async created() {
    const _this = this;
    _this.filters.team = _this.currentTeam.id;

    if ( _this.action.preview || _this.action.new || _this.action.edit || _this.action.reset ) {
      _this.action.preview = _this.action.new = _this.action.edit = _this.action.reset = false;
      _this.resetFilters('all');
    }

    _this.setColumns(_this.$ml.current);
    $(document).on("click", ".languages button", function () {
      _this.setColumns(_this.$ml.current);
    })
  },

  watch: {
    filters: [
      {
        handler: async function (value, oldValue) {
          const _this = this;

          if ( !_this.action.preview && !_this.action.new && !_this.action.edit && !_this.action.reset ) {
            let data = await _this.getAll();

            if (value.team != _this.currentTeam.id) {
              _this.filters.department = _this.filters.project = _this.filters.issue = _this.filters.issue_year = null;
              _this.setCurrentTeam(value.team);

            } else {
              _this.action.reset = true;
              _this.resetFilters();
            }
          }
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