<template>
  <card>
    <h4 slot="header" class="card-title">
      {{ $ml.with("VueJS").get("txtMyOffDay") }}
    </h4>
    <div class="row">
      <div class="col-sm-12 col-lg-2">
        <card>
          <template slot="header">
            <h4 class="card-title">{{ $ml.with("VueJS").get("txtType") }}</h4>
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
          :events="offDays"
          :all-day-slot="false"
          height="auto"
          :hidden-days="[0]"
          @eventReceive="addEvent"
          @eventClick="clickEvent"
          :locale="getLanguage(this.$ml)"
          :datesRender="handleMonthChange"
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
import EditEvent from "./Edit";
import moment from "moment";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "off-days",
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
    };
  },

  components: {
    FullCalendar, // make the <FullCalendar> tag available
    Card,
    EditEvent,
  },

  computed: {
    ...mapGetters({
      offDayTypes: "offdays/offDayTypes",
      offDays: "offdays/offDays",
      currentEvent: "offdays/currentEvent",
      setBackground: "setBackground",
    }),
  },

  methods: {
    ...mapActions({
      handleMonthChange: "offdays/handleMonthChange",
      addEvent: "offdays/addEvent",
      clickEvent: "offdays/clickEvent",
      getOffDays: "offdays/getOffDays",
      deleteEvent: "offdays/deleteEvent",
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

    getLanguage(data) {
      const language = data.current == 'vi' ? 'en' : 'ja';
      return language;
    },
  },

  mounted() {
    this.makeDraggable();
  }
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

.card{
  &:not(.all){
    .fc-dayGrid-view .fc-body .fc-row{
      height: 70px !important;
    }
  }
  &.all{
    .fc-dayGrid-view .fc-body .fc-row{
      height: 120px !important;
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