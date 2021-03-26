<template>
  <div class="content">
    <div class="container-fluid">
      <card>
        <template slot="header">
          <div class="d-flex justify-content-between">
            <h4 class="card-title">
              {{ $ml.with("VueJS").get("txtFilter") }}
            </h4>
          </div>
        </template>

        <div class="row">
          <div class="col-sm-3">
            <div class="form-group">
              <label class="">{{ $ml.with("VueJS").get("txtTeam") }}</label>
              <div>
                <select-2
                  :options="options.teams"
                  v-model="filters.team_id"
                  class="select2"
                />
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="form-group">
              <label class>{{ $ml.with("VueJS").get("txtUsers") }}</label>
              <div>
                <multiselect
                  :multiple="true"
                  v-model="filters.user_id"
                  :options="options.users"
                  :clear-on-select="false"
                  :preserve-search="true"
                  :placeholder="$ml.with('VueJS').get('txtPickSome')"
                  label="text"
                  track-by="text"
                ></multiselect>
              </div>
            </div>
          </div>
          <div v-show="1 === tab" class="col-sm-3">
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

          <div v-show="1 === tab" class="col-sm-3">
            <div class="form-group">
              <label class>{{ $ml.with("VueJS").get("txtEndDate") }}</label>
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

          <div v-show="2 === tab" class="col-sm-3">
            <div class="form-group">
              <label class="">Late time</label>
              <div>
                <select-2
                  :options="options.lates"
                  v-model="filters.late"
                  class="select2"
                />
              </div>
            </div>
          </div>
        </div>
      </card>

      <card class="strpied-tabled-with-hover inout">
        <nav>
          <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a
              class="nav-item nav-link active"
              id="table-tab-1"
              data-toggle="tab"
              href="#table-tab-table"
              role="tab"
              aria-controls="table-tab-table"
              aria-selected="true"
              @click="changeTab(1)"
              >Table Check In Out List</a
            >
            <a
              class="nav-item nav-link"
              id="table-tab-2"
              data-toggle="tab"
              href="#table-tab-calendar"
              role="tab"
              aria-controls="table-tab-calendar"
              aria-selected="false"
              @click="changeTab(2)"
              >Calendar Check In Out List</a
            >
          </div>
        </nav>

        <div class="tab-content" id="nav-tabContent">
          <div
            class="tab-pane fade show active"
            id="table-tab-table"
            role="tabpanel"
            aria-labelledby="table-tab-table-tab"
          >
            <card class="strpied-tabled-with-hover">
              <tbl-default
                :dataItems="data"
                :dataCols="columns"
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
                        class="cl-late"
                        v-if="'late' == column.id"
                        v-html="data.total.late"
                      ></span>
                      <span
                        class="cl-early"
                        v-else-if="'early' == column.id"
                        v-html="data.total.early"
                      ></span>
                      <span v-else>--</span>
                    </td>
                  </tr>
                </template>
              </tbl-default>
              <div v-if="!data.data" class="text-center mt-3">
                <img src="https://i.imgur.com/JfPpwOA.gif" />
              </div>
            </card>
          </div>
          <div
            class="tab-pane fade"
            id="table-tab-calendar"
            role="tabpanel"
            aria-labelledby="table-tab-table-tab"
          >
            <card class="strpied-tabled-with-hover">
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
            </card>
          </div>
        </div>
      </card>
    </div>
  </div>
</template>

<script>
import TblDefault from "../../components/Table";
import Datepicker from "vuejs-datepicker";
import Multiselect from "vue-multiselect";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import FullCalendar from "@fullcalendar/vue";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timeGrid";
import interactionPlugin, { Draggable } from "@fullcalendar/interaction";
import listPlugin from "@fullcalendar/list";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    TblDefault,
    Datepicker,
    Multiselect,
    Select2,
    FullCalendar,
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
      customFormatter: "customFormatter",
      disabledStartDates: "disabledStartDates",
      disabledEndDates: "disabledEndDates",
      currentTeamOption: "currentTeamOption",
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

      tab: 1,
      team_id: "",

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
      getOptions: "getOptions",
      handleMonthChangeAll: "handleMonthChangeAll",
    }),
    changeTab(elm) {
      this.tab = elm;
    },
  },

  async created() {
    const _this = this;
    _this.setColumns();
    _this.options.teams = [{ id: "", text: "ALL" }]
      .concat(_this.currentTeamOption)
      .concat(
        JSON.parse(
          document
            .querySelector("meta[name='media-teams']")
            .getAttribute("content")
        )
      );
    _this.filters.team_id = _this.team_id = _this.currentTeam.id;
    _this.getOptions();
    $(document).on("click", ".languages button", function () {
      _this.setColumns();
    });
  },

  watch: {
    filters: [
      {
        handler: function (value) {
          const _this = this;
          if (value.team_id != _this.team_id) {
            _this.team_id = value.team_id;
            if (value.team_id) _this.filters.user_id = [];
            _this.getOptions();
          }
          _this.data.data = null;
          _this.getAll();
          _this.getAll(1);
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
  .table-responsive {
    overflow-y: auto;
    max-height: 500px;
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
      top: -1px;
    }

    tbody tr.tr-foot td,
    tbody tr.tr-foot th {
      bottom: -1px;
      background-color: #b1acac;
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
      &.bg-late {
        td {
          &[data-filter="late"],
          &[data-filter="checkin"] {
            background-color: #ff9e9e;
          }
        }
      }
      &.bg-early {
        td {
          &[data-filter="early"],
          &[data-filter="checkout"] {
            background-color: #7fffd4;
          }
        }
      }
      &.bg-all {
        td {
          &[data-filter="late"],
          &[data-filter="checkin"] {
            background-color: #ff9e9e;
          }

          &[data-filter="early"],
          &[data-filter="checkout"] {
            background-color: #7fffd4;
          }
        }
      }
    }

    tbody > tr > td,
    tbody > tr > th,
    tfoot > tr > td,
    tfoot > tr > th,
    thead > tr > td,
    thead > tr > th {
      padding: 0px 8px;
      font-size: 14px;
    }
  }
  .fc-time-grid-event .fc-time,
  .fc-time-grid-event .fc-title {
    color: rgba(0, 0, 0, 0.8);
  }

  .fc-time-grid .fc-slats td {
    height: 2em;
  }

  .fc-unthemed td.fc-today {
    background-color: transparent;
  }

  .fc .fc-view-container .fc-head .fc-today {
    background-color: #ffd05b;
  }

  .fc-unthemed th,
  .fc-unthemed td,
  .fc-unthemed thead,
  .fc-unthemed tbody,
  .fc-unthemed .fc-divider,
  .fc-unthemed .fc-row,
  .fc-unthemed .fc-content,
  .fc-unthemed .fc-popover,
  .fc-unthemed .fc-list-view,
  .fc-unthemed .fc-list-heading td {
    border-color: #b3aeae;
  }

  .off-days .fc-day-grid-event .fc-time {
    display: none;
  }
}
</style>