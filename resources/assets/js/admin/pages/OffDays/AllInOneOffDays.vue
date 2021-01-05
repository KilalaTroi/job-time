<template>
  <card>
    <template slot="header">
      <div class="d-flex justify-content-between">
          <h4 class="card-title">{{ $ml.with("VueJS").get("txtStaffOffDay") }}</h4>
          <div class="form-group mb-0 d-flex justify-content-between" style="min-width: 100px">
            <select-2 :options="options.team" v-model="filters.team" class="select2" />
          </div>
      </div>
    </template>
    <div class="row">
      <div class="col-sm-12 col-lg-2">
        <card>
          <template slot="header">
            <h4 class="card-title">{{ $ml.with("VueJS").get("txtMyOffDay") }}</h4>
          </template>
          <div id="external-events">
            <div id="external-events-list">
              <div
                class="alert alert-success fc-event"
                v-for="(item, index) in offDayTypes"
                :data-type="item.id"
                :key="index"
                :color="item.color"
                :style="setBackground(item.color)"
              >
                <span
                  ><b>{{ item.name }}</b></span
                >
              </div>
            </div>
          </div>
        </card>
      </div>
      <div class="col-sm-12 col-lg-10">

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
    EditEvent
  },

  computed: {
    ...mapGetters({
      setBackground: "setBackground",
      getLanguage: "getLanguage",
      currentTeamOption: "currentTeamOption",
      currentTeam: "currentTeam",
    }),

    ...mapGetters('offdays',{
      allOffDays: "allOffDays",
      offDayTypes: "offDayTypes",
      currentEvent: "currentEvent",
      filters: "filters",
    })

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

    ...mapActions('offdays',{
      handleMonthChangeAll: "handleMonthChangeAll",
      getAllOffDays: "getAllOffDays",
      addEvent: "addEvent",
      clickEvent: "clickEvent",
      deleteEvent: "deleteEvent",
    }),

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
    _this.options.team = [{id: '', text: 'ALL'}].concat(_this.currentTeamOption);
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