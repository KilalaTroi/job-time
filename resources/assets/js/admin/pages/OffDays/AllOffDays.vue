<template>
  <card>
    <h4 slot="header" class="card-title">
      {{ $ml.with("VueJS").get("txtStaffOffDay") }}
    </h4>
    <FullCalendar
      class="off-days"
      defaultView="dayGridMonth"
      :plugins="calendarPlugins"
      :header="calendarHeader"
      :business-hours="businessHours"
      :editable="editable"
      :droppable="droppable"
      :events="allOffDays"
      :all-day-slot="allDaySlot"
      :height="height"
      :hidden-days="hiddenDays"
      :locale="getLanguage(this.$ml)"
      :datesRender="handleMonthChangeAll"
    />
  </card>
</template>

<script>
import FullCalendar from "@fullcalendar/vue";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timeGrid";
import interactionPlugin, { Draggable } from "@fullcalendar/interaction";
import listPlugin from "@fullcalendar/list";
import Card from "../../components/Cards/Card";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "all-off-days",

  props: ["team"],

  components: {
    FullCalendar, // make the <FullCalendar> tag available
    Card,
  },

  computed: {
    ...mapGetters({
      allOffDays: "offdays/allOffDays",
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

      editable: false,
      droppable: false,
      allDaySlot: false,
      height: "auto",
      hiddenDays: [0],
    };
  },

  methods: {
    ...mapActions({
      setTeam: "offdays/setTeam",
      handleMonthChangeAll: "offdays/handleMonthChangeAll",
      getAllOffDays: "offdays/getAllOffDays"
    }),

    getLanguage(data) {
      return data.current;
    },
  },

  created() {
    this.setTeam(this.team)
  },

  watch: {
    team: [
      {
        handler: function() {
          this.setTeam(this.team)
          this.getAllOffDays();
        }
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