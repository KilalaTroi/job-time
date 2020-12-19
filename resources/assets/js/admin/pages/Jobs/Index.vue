<template>
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col col-sm-auto">
          <card>
            <template slot="header">
              <h4 class="card-title text-center">
                <!-- {{ this.customFormatter(start_date) }} -->
              </h4>
            </template>

            <datepicker
              :format="customFormatter"
              :disabled-dates="disabledEndDates()"
              v-model="filters.currentDate"
              :inline="true"
              :language="getLanguage(this.$ml)"
            >
            </datepicker>
          </card>
        </div>
        <div class="col">
          <card>
            <template slot="header">
              <div class="d-flex justify-content-between">
                <h4 class="card-title">
                  {{ $ml.with("VueJS").get("txtJobsList") }}
                </h4>
                <div
                  class="form-group mb-0 d-flex justify-content-between"
                  style="min-width: 160px"
                >
                  <select-2
                    :options="currentTeamOption"
                    v-model="filters.team"
                    class="select2"
                  ></select-2>
                  <div class="ml-3"></div>
                  <select-2
                    :options="options"
                    v-model="filters.show"
                    class="select2"
                  ></select-2>
                </div>
              </div>
            </template>
            <div class="table-responsive" v-if="data.jobs">
              <tbl-default
                :dataItems="data.jobs"
                :dataCols="columns.jobs"
                dataAction="addTime"
                dataPath="jobs"
              >
                <template v-slot:action="slotAction">
                  <button
                    @click="getItemJob(slotAction.item.id)"
                    type="button"
                    class="btn btn-xs d-inline-flex btn-primary"
                    data-toggle="modal"
                    data-target="#addTime"
                    data-backdrop="static"
                    data-keyboard="false"
                  >
                    <i class="fa fa-plus" aria-hidden="true"></i>
                  </button>
                </template>
              </tbl-default>
            </div>
            <pagination
              :data="data.jobs"
              :show-disabled="true"
              :limit="2"
              align="right"
              size="small"
              @pagination-change-page="getAll"
            />
          </card>
          <card>
            <template slot="header">
              <h4 class="card-title">{{ $ml.with("VueJS").get("txtTimeRecord") }}</h4>
            </template>
            <tbl-default
              :class="{ 'path-team': filters.team == 2 || filters.team == 3 }"
              :dataItems="data.totaling"
              :dataCols="columns.totaling"
              dataAction="all"
              dataPath="jobs"
            >
              <template v-if="0 < data.totaling.data.length" v-slot:tr>
                <tr>
                  <td
                    v-for="(column, index) in columns.totaling"
                    :key="index"
                    :class="column.class"
                  >
                    <span v-if="'end_time' == column.id">Total:</span>
                    <span
                      v-else-if="'total' == column.id"
                      v-html="data.totaling.total.text"
                    ></span>
                    <span v-else>--</span>
                  </td>
                  <td></td>
                </tr>
              </template>
            </tbl-default>

            <div
              class="alert alert-danger"
              v-if="data.totaling.total.value > 28800"
            >
              <span>{{ $ml.with("VueJS").get("msgOverTime") }}</span>
            </div>
          </card>
        </div>
      </div>

      <add-time />
      <edit-time />
    </div>
  </div>
</template>
<script>
import Card from "../../components/Cards/Card";
import Datepicker from "vuejs-datepicker";
import { vi, ja, en } from "vuejs-datepicker/dist/locale";
import moment from "moment";
import TblDefault from "../../components/Table";
import AddTime from "./AddTime";
import EditTime from "./EditTime";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    Card,
    datepicker: Datepicker,
    TblDefault,
    AddTime,
    EditTime,
    Select2,
  },
  computed: {
    ...mapGetters("jobs", {
      columns: "columns",
      data: "data",
      options: "options",
      filters: "filters",
    }),
    ...mapGetters({
      currentTeamOption: "currentTeamOption",
      currentTeam: "currentTeam",
    }),
  },
  data() {
    return {
      userCreatedAt: document
        .querySelector("meta[name='user-created-at']")
        .getAttribute("content"),

      logColumns: [
        {
          id: "project",
          value: this.$ml.with("VueJS").get("txtProject"),
          width: "",
          class: "",
        },
        {
          id: "issue_year",
          value: this.$ml.with("VueJS").get("txtYearOfIssue"),
          width: "60",
          class: "text-center",
        },
        {
          id: "issue",
          value: this.$ml.with("VueJS").get("txtIssue"),
          width: "60",
          class: "text-center",
        },
        {
          id: "note",
          value: this.$ml.with("VueJS").get("txtWork"),
          width: "",
          class: "note",
        },
        {
          id: "phase",
          value: this.$ml.with("VueJS").get("txtPhase"),
          width: "",
          class: "text-center",
        },
        {
          id: "start_time",
          value: this.$ml.with("VueJS").get("lblStartTime"),
          width: "110",
          class: "text-center",
        },
        {
          id: "end_time",
          value: this.$ml.with("VueJS").get("lblEndTime"),
          width: "110",
          class: "text-center",
        },
        {
          id: "total",
          value: this.$ml.with("VueJS").get("lblTime"),
          width: "110",
          class: "text-center",
        },
      ],
      departments: [],
      jobs: [],
      allJobs: [],
      jobData: {},
      logTime: [],
      logTimeData: [],
      timeTotal: 0,
      jobsTime: [],
      optionsFilter: [],
      // schedules: [],
      currentJob: null,
      currentTimeLog: null,

      start_date: new Date(),

      validationErrors: "",
      validationSuccess: "",

      jLimit: 2,
      jShowDisabled: true,
      jAlign: "right",
      jSize: "small",

      showFilter: "showSchedule",
      dataLang: {
        vi: vi,
        ja: ja,
        en: en,
      },
      selectTeam: "",
    };
  },
  methods: {
    ...mapActions({
      setCurrentTeam: "setCurrentTeam",
    }),

    ...mapActions("jobs", {
      setColumns: "setColumns",
      getAll: "getAll",
      getOptions: "getOptions",
      setFilter: "setFilter",
      getItem : "getItem",
      getItemJob: "getItemJob"
    }),
    
    disabledEndDates() {
      let obj = {
        to: new Date(this.userCreatedAt),
        from: new Date(), // Disable all dates after specific date
        // days: [0], // Disable Saturday's and Sunday's
      };
      return obj;
    },
    customFormatter(date) {
      return moment(date).format("DD-MM-YYYY") !== "Invalid date"
        ? moment(date).format("DD MMM YYYY")
        : "";
    },
    
    getLanguage(data) {
      return this.dataLang[data.current];
    },
  },
  async created() {
    const _this = this;
    _this.filters.team = _this.currentTeam.id;
    _this.setFilter(_this.filters);
  },
  mounted() {
    let _this = this;
    _this.setColumns();
    _this.getAll();
    _this.getOptions();

    $(document).on("click", ".languages button", function () {
      _this.getAll();
      _this.getOptions();
      _this.setColumns();
    });
  },
  watch: {
    filters: [
      {
        handler: function (value) {
          if (value.team != this.currentTeam.id) {
            this.setCurrentTeam(value.team);
            this.setFilter(this.filters);
          }
          this.getAll();
        },
        deep: true,
      },
    ],
  },
};
</script>
<style lang="scss">
.time-record tr:last-child button {
  display: none;
}
.table-responsive:not(.path-team) {
  .note {
    display: none;
  }
}
</style>