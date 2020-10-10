<template>
  <div class="content" >
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-12 col-lg-3 col-xl-2">
          <card>
            <template slot="header">
              <h4 class="card-title">
                {{ $ml.with("VueJS").get("txtScheduleTitle") }}
              </h4>
            </template>
            <div class="form-group">
              <input
                v-model="search"
                :placeholder="$ml.with('VueJS').get('txtScheduleSearch')"
                type="text"
                class="form-control"
                v-on:keyup="searchItem"
              />
            </div>
            <div id="external-events">
              <div id="external-events-list">
                <div
                  class="alert alert-success fc-event"
                  v-for="(item, index) in scheduleData.projects"
                  :data-issue="item.issue_id"
                  :key="index"
                  :start="item.start_date"
                  :end="item.end_date"
                  :color="item.value"
                  :style="{
                    backgroundColor: item.value,
                    borderColor: item.value,
                  }"
                >
                  <span>{{ item.p_name }} {{ item.issue_id }}</span>
                </div>
              </div>
            </div>
          </card>
        </div>
        <div class="col-sm-12 col-lg-9 col-xl-10">
          <div class="filter_search">
            <div class="form-group d-flex align-items-center">
              <label class="mb-0" :style="{paddingRight: '10px'}">Team</label>
              <div class="w-100">
                <select-2
                  :options="currentTeamOption"
                  v-model="filters.team"
                  class="select2"
                ></select-2>
              </div>
            </div>
          </div>
          <FullCalendar
            defaultView="timeGridWeek"
            scroll-time="8:00:00"
            :plugins="calendarPlugins"
            :header="calendarHeader"
            :business-hours="businessHours"
            :editable="true"
            :droppable="true"
            :events="scheduleData.schedules"
            :event-overlap="true"
            :all-day-slot="false"
            min-time="08:00:00"
            max-time="17:00:00"
            height="auto"
            :hidden-days="hiddenDays"
            @eventReceive="addEvent"
            @datesRender="handleMonthChange"
            @eventDrop="dropEvent"
            @eventResize="resizeEvent"
            @eventClick="clickEvent"
            :locale="getLanguage(this.$ml)"
          />
        </div>
      </div>
    </div>

    <EditEvent
      :currentEvent="currentEvent"
      :errors="validationErrors"
      :success="validationSuccess"
      v-on:update-event="updateEvent"
      v-on:delete-event="deleteEvent"
      v-on:reset-validation="resetValidation"
    ></EditEvent>
  </div>
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
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    Select2,
    FullCalendar, // make the <FullCalendar> tag available
    Card,
    EditEvent,
  },
  data() {
    return {
      memo: "",
      schedules: [],
      currentEvent: {},

      calendarPlugins: [
        dayGridPlugin,
        listPlugin,
        interactionPlugin,
        timeGridPlugin,
      ],
      calendarHeader: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,timeGridDay",
      },
      businessHours: [
        {
          // days of week. an array of zero-based day of week integers (0=Sunday)
          daysOfWeek: [1, 2, 3, 4, 5], // Monday - Thursday

          startTime: "08:00", // a start time (10am in this example)
          endTime: "17:00", // an end time (6pm in this example)
        },
        {
          // days of week. an array of zero-based day of week integers (0=Sunday)
          daysOfWeek: [6], // Monday - Thursday

          startTime: "08:00", // a start time (10am in this example)
          endTime: "12:00", // an end time (6pm in this example)
        },
      ],
      hiddenDays: [0],
    };
  },

  computed: {
    ...mapGetters("schedules", {
      scheduleData: "data",
      filters: "filters",
      search: "search",
      searchResults: "searchResults",
      validationErrors: "validationErrors",
      validationSuccess: "validationSuccess",
    }),
    ...mapGetters({
      currentTeamOption: "currentTeamOption",
      getLangCode: "getLangCode",
      currentTeam: "currentTeam",
      typeOptions: "types/options",
      deptOptions: "departments/options",
    }),
  },

  methods: {
    ...mapActions("schedules", {
      handleMonthChange: "handleMonthChange",
      resetValidate: "resetValidate",
      searchItem: "searchItem",
    }),

    ...mapActions("types", {
      getOptionType: "getOptions",
    }),

    ...mapActions("departments", {
      getOptionDept: "getOptions",
    }),

    getLanguage(data) {
      return data.current;
    },

    resetValidation() {
      this.resetValidate();
    },

    makeDraggable() {
      let draggableEl = document.getElementById("external-events-list");

      new Draggable(draggableEl, {
        itemSelector: ".fc-event",
        eventData: (eventEl) => {
          return {
            title: eventEl.innerText.trim(),
            id: eventEl.getAttribute("data-issue"),
            borderColor: eventEl.getAttribute("color"),
            backgroundColor: eventEl.getAttribute("color"),
            constraint: {
              start: moment(
                eventEl.getAttribute("start") + " " + "08:00"
              ).format(),
              end: moment(eventEl.getAttribute("end") + " " + "17:00").format(),
            },
            overlap: true,
            duration: "1:00:00",
          };
        },
      });
    },

    customFormatter(date) {
      return moment(date).format("DD-MM-YYYY") !== "Invalid date"
        ? moment(date).format("YYYY-MM-DD")
        : "--";
    },
    hourFormatter(date) {
      return moment(date).format("HH:mm");
    },
    deleteEvent(event) {
      $("#editEvent").modal("hide");

      if (confirm(this.$ml.with("VueJS").get("msgConfirmDelete"))) {
        let uri = "/data/schedules/" + event.id;
        axios
          .delete(uri)
          .then((res) => {
            this.schedules = this.schedules.filter((elem) => {
              if (elem.id != event.id) return elem;
            });
          })
          .catch((err) => console.log(err));
      }
    },
    updateEvent(event) {
      // Reset validate

      let uri = "/data/schedules/" + event.id;
      let newItem = {
        memo: event.memo,
      };
      axios
        .patch(uri, newItem)
        .then((res) => {
          let foundIndex = this.schedules.findIndex((x) => x.id == event.id);
          this.schedules[foundIndex].title =
            this.schedules[foundIndex].title_not_memo + "\n" + event.memo;
          this.schedules = [...this.schedules];
        })
        .catch((err) => {});
    },
    clickEvent(info) {
      this.currentEvent = info.event;
      let titleArray = this.currentEvent.title.split("\n");
      this.currentEvent.title_not_memo = titleArray[0];
      this.currentEvent.memo = titleArray[1];
      $("#editEvent").modal("show");
    },
    resizeEvent(info) {
      this.editable = false;
      this.droppable = false;

      if (!confirm(this.$ml.with("VueJS").get("msgConfirmChange"))) {
        info.revert();
        this.editable = true;
        this.droppable = true;
      } else {
        let { event } = info;
        let { id, start, end } = event;
        let uri = "/data/schedules/" + id;
        let newItem = {
          start_time: this.hourFormatter(start),
          end_time: this.hourFormatter(end),
        };
        axios
          .patch(uri, newItem)
          .then((res) => {
            let foundIndex = this.schedules.findIndex((x) => x.id == id);
            this.schedules[foundIndex].start = moment(start).format();
            this.schedules[foundIndex].end = moment(end).format();
            this.schedules = [...this.schedules];

            this.editable = true;
            this.droppable = true;
          })
          .catch((err) => {
            console.log(err);
            this.editable = true;
            this.droppable = true;
          });
      }
    },
    dropEvent(info) {
      this.editable = false;
      this.droppable = false;

      if (!confirm(this.$ml.with("VueJS").get("msgConfirmChange"))) {
        info.revert();
        this.editable = true;
        this.droppable = true;
      } else {
        let { event } = info;
        let { id, start, end } = event;
        let uri = "/data/schedules/" + id;
        let newItem = {
          date: this.customFormatter(start),
          start_time: this.hourFormatter(start),
          end_time: this.hourFormatter(end),
        };
        axios
          .patch(uri, newItem)
          .then((res) => {
            let foundIndex = this.schedules.findIndex((x) => x.id == id);
            this.schedules[foundIndex].start = moment(start).format();
            this.schedules[foundIndex].end = moment(end).format();
            this.schedules = [...this.schedules];

            this.editable = true;
            this.droppable = true;
          })
          .catch((err) => {
            console.log(err);
            this.editable = true;
            this.droppable = true;
          });
      }
    },
    addEvent(info) {
      this.editable = false;
      this.droppable = false;

      let { event } = info;
      let { id, start, end, title, borderColor, backgroundColor } = event;
      let uri = "/data/schedules";
      let newItem = {
        issue_id: id,
        title: title,
        borderColor: borderColor,
        backgroundColor: backgroundColor,
        date: this.customFormatter(start),
        start_time: this.hourFormatter(start),
        end_time: this.hourFormatter(end),
      };
      axios
        .post(uri, newItem)
        .then((res) => {
          this.schedules = [...this.schedules, res.data.event];
          info.event.remove();
          this.editable = true;
          this.droppable = true;
        })
        .catch((err) => {
          console.log(err);
          this.editable = true;
          this.droppable = true;
        });
    },
  },

  async created() {
    let _this = this;
    if (!_this.deptOptions.length) await _this.getOptionDept();
    if (!_this.typeOptions.length) await _this.getOptionType();
    _this.filters.team = await _this.currentTeam.id;
  },

  mounted() {
    let _this = this;
    _this.makeDraggable();
  },

  watch: {
    search: [
      {
        handler: "searchItem",
      },
    ],
    filters: [
      {
        handler: "handleMonthChange",
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

.filter_search {
  position: absolute;
  width: 200px;
  right: 15%;
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
</style>