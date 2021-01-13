<template>
  <card>
    <template slot="header">
      <div class="d-flex justify-content-between">
        <h4 class="card-title">
          {{ $ml.with("VueJS").get("txtStaffOffDay") }}
        </h4>
        <div
          class="form-group mb-0 d-flex justify-content-between"
          style="min-width: 100px"
        >
          <select-2
            :options="options.team"
            v-model="filters.team"
            class="select2"
          />
        </div>
      </div>
    </template>
    <div class="row">
      <div class="col-sm-12 row--left">
        <card>
          <template slot="header">
            <h4 class="card-title">
              {{ $ml.with("VueJS").get("txtMyOffDay") }}
            </h4>
          </template>
          <div class="mb-3" id="external-events">
            <div id="external-events-list">
              <div
                class="alert alert-success fc-event"
                v-for="(item, index) in offDayTypes"
                :data-type="item.id"
                :key="index"
                :color="item.color"
                :style="setBackground(item.color)"
              >
                <span><b>{{ item.name }}</b></span>
              </div>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label class>{{ $ml.with("VueJS").get("txtStartDate") }}</label>
                <datepicker
                  name="startDate"
                  input-class="form-control"
                  placeholder="Select Date"
                  v-model="selectedItem.start_date"
                  :format="dateFormat(selectedItem.start_date, 'YYYY-MM-DD')"
                  :language="getLangCode(this.$ml)"
                ></datepicker>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label class>{{ $ml.with("VueJS").get("txtEndDate") }}</label>
                <datepicker
                  name="endDate"
                  input-class="form-control"
                  placeholder="Select Date"
                  v-model="selectedItem.end_date"
                  :format="dateFormat(selectedItem.end_date, 'YYYY-MM-DD')"
                  :disabled-dates="disabledStartDates(selectedItem.start_date)"
                  :language="getLangCode(this.$ml)"
                ></datepicker>
              </div>
            </div>
            <div class="col-12 col-sm-auto ml-auto">
               <button
                @click="printEvents(selectedItem)"
                type="button"
                class="btn btn-primary"
              >
                {{ $ml.with("VueJS").get("txtPrint") }}
              </button>
            </div>
          </div>
        </card>
      </div>
      <div class="col-sm-12 row--right">
        <FullCalendar
          class="off-days"
          defaultView="dayGridMonth"
          :plugins="calendarPlugins"
          :header="calendarHeader"
          :business-hours="businessHours"
          :editable="false"
          :droppable="false"
          :events="allOffDays"
          :all-day-slot="false"
          height="auto"
          :hidden-days="[0]"
          @eventReceive="addEvent"
          @eventClick="clickEvent"
          :locale="getLanguage(this.$ml)"
          :datesRender="handleMonthChangeAll"
        />
      </div>
    </div>
    <edit-event />
  </card>
</template>

<script>
import FullCalendar from "@fullcalendar/vue";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timeGrid";
import interactionPlugin, { Draggable } from "@fullcalendar/interaction";
import listPlugin from "@fullcalendar/list";
import Datepicker from "vuejs-datepicker";
import Card from "../../components/Cards/Card";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import EditEvent from "./Edit";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "all-in-one-off-days",
  components: {
    FullCalendar, // make the <FullCalendar> tag available
    Card,
    Select2,
    EditEvent,
    Datepicker
  },

  computed: {
    ...mapGetters({
      setBackground: "setBackground",
      getLanguage: "getLanguage",
      currentTeamOption: "currentTeamOption",
      currentTeam: "currentTeam",
      getLangCode: "getLangCode",
      dateFormat: "dateFormat"
    }),

    ...mapGetters("offdays", {
      allOffDays: "allOffDays",
      offDayTypes: "offDayTypes",
      currentEvent: "currentEvent",
      filters: "filters",
      selectedItem: "selectedItem"
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

      selectedItemOld: {
        'start_date': new Date
      },

      options: {
        team: [],
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

    ...mapActions("offdays", {
      handleMonthChangeAll: "handleMonthChangeAll",
      getAllOffDays: "getAllOffDays",
      printEvents: "printEvents",
      addEvent: "addEvent",
      clickEvent: "clickEvent",
      deleteEvent: "deleteEvent",
    }),

    disabledStartDates(date) {
      if ( date ) return { to: new Date(date) };
      return { from: new Date() };
    },

    makeDraggable() {
      let draggableEl = document.getElementById("external-events-list");

      new Draggable(draggableEl, {
        itemSelector: ".fc-event",
        eventData: (eventEl) => {
          return {
            title: eventEl.innerText.trim(),
            id: eventEl.getAttribute("data-type"),
            borderColor: eventEl.getAttribute("color"),
            backgroundColor: eventEl.getAttribute("color"),
          };
        },
      });
    },
  },

  mounted() {
    this.makeDraggable();
  },

  async created() {
    const _this = this;
    _this.filters.team = _this.currentTeam.id;
    _this.options.team = [{ id: "", text: "ALL" }].concat(
      _this.currentTeamOption
    );
  },

  watch: {
    selectedItem: [
      {
        handler: function (value) {
          if(value.start_date !== this.selectedItemOld.start_date && value.start_date > this.selectedItem.end_date){
            this.selectedItem.end_date = '';
            this.selectedItemOld.start_date = value.start_date
          }
        },
        deep: true,
      },
    ],
    filters: [
      {
        handler: function (value) {
          if (value.team != this.currentTeam.id) {
            this.setCurrentTeam(value.team);
          }
          this.getAllOffDays();
        },
        deep: true,
      },
    ],
  },
};
</script>

<style lang="scss">
@import "~@fullcalendar/core/main.css";
@import "~@fullcalendar/daygrid/main.css";
@import "~@fullcalendar/timegrid/main.css";
@import "~@fullcalendar/list/main.css";

.fc-time-grid .fc-event {
  padding: 5px;
}

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

.fc-event {
  cursor: move;
  color: rgba(0, 0, 0, 0.8);
  min-height: 0;
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
</style>