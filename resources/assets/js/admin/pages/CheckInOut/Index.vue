<template>
  <div class="content inout">
    <div class="container-fluid">
      <card class="mb-0">
        <template slot="header">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h4 class="card-title my-0 mb-1">
                {{ $ml.with("VueJS").get("sbAttendance") }}
              </h4>
              <nav>
                <div class="nav" id="nav-tab" role="tablist">
                  <a
                    class="nav-item nav-link active"
                    id="table-tab-2"
                    data-toggle="tab"
                    href="#table-tab-calendar"
                    role="tab"
                    aria-controls="table-tab-calendar"
                    aria-selected="false"
                    @click="changeTab(2)"
                    >{{ $ml.with("VueJS").get("txtCalendar") }}</a
                  >
                  <a
                    class="nav-item nav-link"
                    id="table-tab-1"
                    data-toggle="tab"
                    href="#table-tab-table"
                    role="tab"
                    aria-controls="table-tab-table"
                    aria-selected="true"
                    @click="changeTab(1)"
                    >{{ $ml.with("VueJS").get("txtCalculation") }}</a
                  >
                  <router-link
                    class=""
                    v-if="permission"
                    to="/checkinout/timetable"
                    >{{
                      $ml.with("VueJS").get("txtShiftsManagement")
                    }}</router-link
                  >
                </div>
              </nav>
            </div>
            <div
              class="form-group mb-0 d-flex justify-content-end align-items-end"
              style="width: width: 800px"
            >
              <div v-show="2 === tab" style="width: 150px" class="mr-2">
                <div class="">
                  <div>
                    <select-2
                      :options="options.lates"
                      v-model="filters.late"
                      class="select2"
                    />
                  </div>
                </div>
              </div>
              <div v-show="1 === tab" class="mr-2 filter-time">
                <div class="row">
                  <div class="col-sm-6">
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
                  <div class="col-sm-6">
                    <datepicker
                      name="endDate"
                      input-class="form-control"
                      placeholder="Select Date"
                      v-model="filters.end_date"
                      :format="customFormatter"
                      :disabled-dates="disabledStartDates(filters.start_date)"
                      :language="getLangCode(this.$ml)"
                    ></datepicker>
                  </div>
                </div>
              </div>
              <div class="mr-2" style="width: 100px">
                <div class="">
                  <div>
                    <select-2
                      :options="options.teams"
                      v-model="filters.team_id"
                      class="select2"
                    />
                  </div>
                </div>
              </div>
              <div style="width: 200px">
                <div class="">
                  <div>
                    <select-2
                      :options="options.users"
                      v-model="filters.user_id"
                      class="select2"
                    />
                  </div>
                </div>
              </div>
            </div>
            <!-- <div class="row justify-content-end" style="width: 800px"></div> -->
          </div>
        </template>
        <div class="row">
          <div class="col-sm-12 row--left">
            <card>
              <div
                color="#00AEEF"
                class="alert alert-success"
                style="
                  background-color: #f3a2a2;
                  border-color: #f3a2a2;
                  color: #333;
                "
              >
                <span
                  ><b>{{ $ml.with("VueJS").get("txtLate") }}</b></span
                >
              </div>
              <div
                color="#00AEEF"
                class="alert alert-success"
                style="
                  background-color: #93ccb7;
                  border-color: #93ccb7;
                  color: #333;
                "
              >
                <span
                  ><b>{{ $ml.with("VueJS").get("txtEarly") }}</b></span
                >
              </div>
              <div
                color="#00AEEF"
                class="alert alert-success"
                style="
                  background-color: #d23c3c;
                  border-color: #d23c3c;
                  color: #333;
                "
              >
                <span
                  ><b>{{ $ml.with("VueJS").get("txtTotalLate") }}</b></span
                >
              </div>
              <div
                color="#00AEEF"
                class="alert alert-success"
                style="
                  background-color: #29b166;
                  border-color: #29b166;
                  color: #333;
                "
              >
                <span
                  ><b>{{ $ml.with("VueJS").get("txtTotalEarly") }}</b></span
                >
              </div>
            </card>
          </div>
          <div class="col-sm-12 row--right">
            <div class="tab-content" id="nav-tabContent">
              <div
                class="tab-pane fade show active"
                id="table-tab-calendar"
                role="tabpanel"
                aria-labelledby="table-tab-table-tab"
              >
                <div class="strpied-tabled-with-hover">
                  <FullCalendar
                    class="checkinoutcalendar"
                    defaultView="dayGridMonth"
                    :plugins="calendarPlugins"
                    :header="calendarHeader"
                    :business-hours="businessHours"
                    :editable="false"
                    :droppable="false"
                    :all-day-slot="false"
                    :events="cdata.data"
                    height="auto"
                    :hidden-days="[0]"
                    :locale="getLanguage(this.$ml)"
                    :datesRender="handleMonthChangeAll"
                  />
                </div>
              </div>
              <div
                class="tab-pane fade"
                id="table-tab-table"
                role="tabpanel"
                aria-labelledby="table-tab-table-tab"
              >
                <div class="strpied-tabled-with-hover">
                  <tbl-check-in-out
                    :dataItems="data"
                    :dataCols="columns"
                    dataAction="reason"
                    dataPath="checkinout"
                  >
                    <template v-if="data.data" v-slot:tr>
                      <tr class="tr-foot">
                        <td
                          v-for="(column, index) in columns"
                          :key="index"
                          :class="column.class"
                          :data-filter="column.id"
                        >
                          <span
                            class="cl-checkout text-right d-block"
                            v-if="'checkout' == column.id"
                            >{{ $ml.with("VueJS").get("txtTotal") }}:</span
                          >
                          <span
                            class="cl-late"
                            :data-time="data.total.late"
                            v-else-if="'late' == column.id"
                            v-html="data.total.strlate"
                          ></span>
                          <span
                            class="cl-early"
                            :data-time="data.total.early"
                            v-else-if="'early' == column.id"
                            v-html="data.total.strearly"
                          ></span>
                          <span v-else></span>
                        </td>
                      </tr>
                    </template>
                  </tbl-check-in-out>
                  <div v-if="!data.data" class="text-center mt-3">
                    <img src="https://i.imgur.com/JfPpwOA.gif" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </card>
    </div>
    <update-reason />
  </div>
</template>

<script>
import TblCheckInOut from "../../components/TableCheckInOut";
import Datepicker from "vuejs-datepicker";
import Multiselect from "vue-multiselect";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import FullCalendar from "@fullcalendar/vue";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timeGrid";
import interactionPlugin, { Draggable } from "@fullcalendar/interaction";
import listPlugin from "@fullcalendar/list";
import UpdateReason from "./UpdateReason";

import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    TblCheckInOut,
    Datepicker,
    Multiselect,
    Select2,
    FullCalendar,
    UpdateReason,
  },

  computed: {
    ...mapGetters("checkinout", {
      columns: "columns",
      data: "data",
      cdata: "cdata",
      filters: "filters",
      options: "options",
    }),

    ...mapGetters({
      getLanguage: "getLanguage",
      getLangCode: "getLangCode",
      loginUser: "loginUser",
      customFormatter: "customFormatter",
      disabledStartDates: "disabledStartDates",
      disabledEndDates: "disabledEndDates",
      currentFullTeamOption: "currentFullTeamOption",
      currentTeam: "currentTeam",
    }),
  },

  data() {
    return {
      calendarPlugins: [
        listPlugin,
        interactionPlugin,
        dayGridPlugin,
        timeGridPlugin,
      ],
      permission: false,
      tab: 2,
      filtersTable: {
        start_date: "",
        end_date: "",
      },

      filtersCalendar: {
        late: "",
      },

      filtersAll: {
        user_id: "",
        team_id: "",
      },

      calendarHeader: {
        left: "prev",
        center: "title",
        right: "next",
      },

      businessHours: [
        {
          // days of week. an array of zero-based day of week integers (0=Sunday)
          daysOfWeek: [1, 2, 3, 4, 5], // Monday - Thursday
        },
        {
          // days of week. an array of zero-based day of week integers (0=Sunday)
          daysOfWeek: [6], // Monday - Thursday
        },
      ],
    };
  },

  methods: {
    ...mapActions({
      setCurrentTeam: "setCurrentTeam",
    }),
    ...mapActions("checkinout", {
      setColumns: "setColumns",
      getAll: "getAll",
      getItemReason: "getItemReason",
      getOptions: "getOptions",
      handleMonthChangeAll: "handleMonthChangeAll",
      resetFilter: "resetFilter",
    }),
    changeTab(elm) {
      this.tab = elm;
    },
    setTeam(value) {
      const _this = this;
      if ("undefined" == typeof _this.filters.team_id && value.team)
        _this.filters.team_id = _this.filtersAll.team_id = value.team.id;
    },
    checkPermission(value) {
      const _this = this;
      if (
        (value.role && 1 == value.role.id) ||
        -1 !== "1,49,".indexOf(value.id + ",")
      )
        _this.permission = true;
    },
  },

  async created() {
    const _this = this;
    _this.resetFilter();
    _this.setColumns();
    _this.options.teams = [{ id: "", text: "ALL" }].concat(
      _this.currentFullTeamOption
    );
    _this.filters.team_id = _this.filtersAll.team_id = _this.currentTeam.id;
    _this.setTeam(_this.loginUser);
    _this.checkPermission(_this.loginUser);
    _this.getOptions();
    $(document).on("click", ".languages button", function () {
      _this.setColumns();
    });
  },

  watch: {
    loginUser: [
      {
        handler: function (value) {
          const _this = this;
          _this.setTeam(value);
          _this.checkPermission(value);
        },
        deep: true,
      },
    ],
    filters: [
      {
        handler: function (value) {
          const _this = this;
          if ("undefined" != typeof value.team_id) {
            if (value.team_id != _this.filtersAll.team_id) {
              _this.getOptions();
              this.setCurrentTeam(value.team_id);
              _this.filters.user_id = _this.filtersAll.user_id = "";
            }
            if (
              _this.filtersAll.team_id != value.team_id ||
              _this.filtersAll.user_id != value.user_id
            ) {
              _this.filtersAll.team_id = value.team_id;
              _this.filtersAll.user_id = value.user_id;
              _this.getAll();
              _this.getAll(1);
            }
            if (_this.filtersCalendar.late != value.late) {
              _this.filtersCalendar.late = value.late;
              _this.getAll(1);
            }
            if (
              _this.filtersTable.start_date != value.start_date ||
              _this.filtersTable.end_date != value.end_date
            ) {
              _this.data.data = null;
              _this.filtersTable.start_date = value.start_date;
              _this.filtersTable.end_date = value.end_date;
              _this.getAll();
            }
          }
        },
        deep: true,
      },
    ],
  },
};
</script>
<style lang="scss">
@import "~vue-multiselect/dist/vue-multiselect.min.css";
@import "~@fullcalendar/core/main.css";
@import "~@fullcalendar/daygrid/main.css";
@import "~@fullcalendar/timegrid/main.css";
@import "~@fullcalendar/list/main.css";
.inout {
  @media (min-width: 992px) {
    .row {
      &--left {
        flex: 0 0 20%;
        max-width: 20%;
      }
      &--right {
        flex: 0 0 80%;
        max-width: 80%;
      }
    }
  }
  #nav-tab {
    a {
      padding: {
        top: 0 !important;
        left: 0 !important;
        bottom: 0 !important;
      }
      &:not(:last-child) {
        margin-right: 10px;
        padding-right: 10px;
        &::after {
          content: "";
          height: 100%;
          width: 1px;
          background-color: #006b82;
          display: block;
          position: absolute;
          top: 0;
          right: 0;
        }
      }
      &.active {
        color: #006b82;
      }
    }
  }
  .filter-time .row > .col-sm-6 {
    &:first-child {
      &:after {
        content: "-";
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        right: -2px;
      }
    }
  }
  .table-responsive {
    overflow-y: auto;
    max-height: calc(100vh - 303px);
    #checkinout {
      margin-bottom: 0;
    }
    thead td,
    thead th,
    tbody tr.tr-foot td,
    tbody tr.tr-foot th {
      position: sticky;
      z-index: 2;
      background-color: #ffd05b;
    }
    thead td,
    thead th {
      top: 0;
    }

    tbody tr.tr-foot {
      &.bg-late,
      &.bg-all {
        td {
          &[data-filter="late"],
          &[data-filter="checkin"] {
            background-color: #d23c3c;
          }
          &[data-filter="late"] {
            border: 1px solid;
          }
        }
      }
      &.bg-early,
      &.bg-all {
        td {
          &[data-filter="early"],
          &[data-filter="checkout"] {
            background-color: #29b166;
            border: 1px;
          }
          &[data-filter="early"] {
            border: 1px solid;
          }
        }
      }
      td,
      th {
        bottom: 0;
        background-color: #b1acac;
      }
    }

    tbody tr {
      &.tr-foot {
        td {
          &[data-filter="checkin"],
          &[data-filter="checkout"] {
            background-color: #b1acac !important;
          }
        }
      }
      &.bg-late,
      &.bg-all {
        td {
          &[data-filter="late"],
          &[data-filter="checkin"] {
            background-color: #f3a2a2;
          }
        }
      }
      &.bg-early,
      &.bg-all {
        td {
          &[data-filter="early"],
          &[data-filter="checkout"] {
            background-color: #93ccb7;
          }
        }
      }
      td {
        &[data-filter="reason"] {
          text-overflow: ellipsis;
          overflow: hidden;
          white-space: nowrap;
          max-width: 320px;
        }
      }
    }

    tbody > tr > td,
    tbody > tr > th,
    tfoot > tr > td,
    tfoot > tr > th,
    thead > tr > td,
    thead > tr > th {
      padding: 2px 8px;
      font-size: 14px;
    }
  }
  .fc-event {
    cursor: default;
  }
  .fc-dayGrid-view .fc-body .fc-row {
    min-height: 9em;
    .fc-title {
      color: red;
    }
  }
}
</style>