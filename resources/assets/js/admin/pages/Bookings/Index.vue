<template>
  <div class="content mtr">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-12 col-lg-3 col-xl-2">
          <card>
            <div id="external-events">
              <div id="external-events-list">
                <div
                  class="alert alert-success fc-event"
                  v-for="(item, index) in scheduleData.projectsFilter"
                  :data-issue="item.issue_id"
                  :key="index"
                  :start="item.start_date"
                  :end="item.end_date"
                  :color="item.value"
                  :style="setBackground(item.value)"
                >
                  <span>{{ item.fullname }}</span>
                </div>
              </div>
            </div>
          </card>
        </div>
        <div class="col-sm-12 col-lg-9 col-xl-10">
          <div class="filter_search d-none">
            <div class="form-group d-flex align-items-center">
              <label class="mb-0" :style="{ paddingRight: '10px', whiteSpace:'nowrap' }">{{
                $ml.with("VueJS").get("txtTeam")
              }}</label>
              <div :style="{minWidth: '60px'}">
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
            :slotLabelFormat="{ 'hour12': false, 'hour': '2-digit', 'minute': '2-digit'}"
            :eventTimeFormat="{ 'hour12': false, 'hour': '2-digit', 'minute': '2-digit'}"
            timeFormat= 'H(:mm)'
            :header="calendarHeader"
            :business-hours="businessHours"
            :editable="fullCalendar.editable"
            :droppable="fullCalendar.droppable"
            :events="scheduleData.schedules"
            :event-overlap="true"
            min-time="08:00:00"
            max-time="19:00:00"
            height="auto"
            :all-day-slot="false"
            :hidden-days="[0]"
            @eventReceive="addSchedule"
            @datesRender="handleMonthChange"
            @eventDrop="dropSchedule"
            @eventResize="resizeSchedule"
            @eventClick="getItem"
            @eventRender="tooltipFunc"
            :locale="getLanguage(this.$ml)"
          />
        </div>
      </div>
    </div>
    <edit-item />
  </div>
</template>

<script>
import FullCalendar from "@fullcalendar/vue"
import dayGridPlugin from "@fullcalendar/daygrid"
import timeGridPlugin from "@fullcalendar/timeGrid"
import interactionPlugin, { Draggable } from "@fullcalendar/interaction"
import listPlugin from "@fullcalendar/list"
import Card from "../../components/Cards/Card"
import EditItem from "./Edit"
import Select2 from "../../components/SelectTwo/SelectTwo.vue"
import { mapGetters, mapActions } from "vuex"

export default {
  components: {
    Select2,
    FullCalendar, // make the <FullCalendar> tag available
    Card,
    EditItem,
  },
  data() {
    return {
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

          startTime: "07:00", // a start time (10am in this example)
          endTime: "19:00", // an end time (6pm in this example)
        },
        {
          // days of week. an array of zero-based day of week integers (0=Sunday)
          daysOfWeek: [6], // Monday - Thursday

          startTime: "07:00", // a start time (10am in this example)
          endTime: "12:00", // an end time (6pm in this example)
        },
      ],
      search: "",
    };
  },

  computed: {
    ...mapGetters("bookings", {
      scheduleData: "data",
      filters: "filters",
      fullCalendar: "fullCalendar",
    }),
    ...mapGetters({
      dateFormat: "dateFormat",
      setBackground: "setBackground",
      currentTeamOption: "currentTeamOption",
      currentTeam: "currentTeam",
      typeOptions: "types/options",
      getLanguage: "getLanguage"
    }),
  },

  methods: {
    ...mapActions("bookings", {
      handleMonthChange: "handleMonthChange",
      resetValidate: "resetValidate",
      searchItem: "searchItem",
      addSchedule: "addSchedule",
      dropSchedule: "dropSchedule",
      resizeSchedule: "resizeSchedule",
      getItem: "getItem",
      getAll: "getAll"
    }),

    ...mapActions({
      getOptionType: "types/getOptions",
      setCurrentTeam: "setCurrentTeam"
    }),

    makeDraggable() {
      let draggableEl = document.getElementById("external-events-list")
      new Draggable(draggableEl, {
        itemSelector: ".fc-event",
        eventData: (eventEl) => {
          return {
            title: eventEl.innerText.trim(),
            id: eventEl.getAttribute("data-issue"),
            borderColor: eventEl.getAttribute("color"),
            backgroundColor: eventEl.getAttribute("color"),
            constraint: {
              start: this.dateFormat(eventEl.getAttribute("start"), 'YYYY-MM-DD') + "T" + "00:00:00",
              end: this.dateFormat(eventEl.getAttribute("end"), 'YYYY-MM-DD') + "T" + "23:59:59",
            },
            overlap: true,
            duration: "00:30:00",
          };
        },
      });
    },

    tooltipFunc(info) {
      info.el.querySelector('.fc-title').innerHTML = info.event.title;
    },
  },

  async created() {
    const _this = this;
    _this.filters.team = _this.currentTeam.id
    if (!_this.typeOptions.length) await _this.getOptionType()
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
        handler: function(value) {
          this.search = '';
          this.getAll();

          if ( value.team != this.currentTeam.id ) {
            $('.tooltip').remove();
            this.setCurrentTeam(value.team);
          }
        },
        deep: true,
      },
    ],
  },
};
</script>

<style lang="scss" scope>
.mtr{
  @import "custom.scss";
}
</style>
