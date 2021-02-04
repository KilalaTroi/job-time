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
               <template v-for="(item, index) in offDayTypes">
                <div
                  class="alert alert-success fc-event"
                  v-if="!(item.permission && loginUser.role && -1 == item.permission.user_id.indexOf(loginUser.id) && -1 == item.permission.role.indexOf(loginUser.role.name))"
                  :data-type="item.id"
                  :key="index"
                  :color="item.color"
                  :style="setBackground(item.color)"
                >
                  <span><b>{{ item.name }}</b></span>
                </div>
               </template>
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
      loginUser: "loginUser",
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
    _this.options.team = [{ id: "", text: "ALL" }].concat(
      _this.currentTeamOption
    );
    _this.filters.team = _this.currentTeam.id;
  },

  watch: {
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
    loginUser: [
      {
        handler: function (value) {
          if('admin' == value.role.name) this.filters.team = '';
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
  &.printed{
    position: relative;
    padding-right: 25px;
    &::after{
      content: "\f046";
      display: inline-block;
      font: normal normal normal 14px/1 FontAwesome;
      font-size: inherit;
      text-rendering: auto;
      -webkit-font-smoothing: antialiased;
      position: absolute;
      right: 5px;
      top: 50%;
      transform: translateY(-50%);
      margin-top: 1px;
    }
  }
  &.holiday, &.offday{
    pointer-events: none;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 53px;
    font-weight: 600;
    text-transform: uppercase;
    &:after{
      content: "";
      position: absolute;
      top: -31px;
      z-index: -1;
      left: -3px;
      height: 120px;
      width: calc(100% + 6px);
    }
  }
  &.offday{
    &:after{
      background-color: #eaeaea;
    }
  }
  &.holiday{
    &:after{
      background-color: #ffd6fb;
    }
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
</style>