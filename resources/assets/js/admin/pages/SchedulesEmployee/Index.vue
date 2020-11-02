<template>
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-12 col-lg-3 col-xl-2">
          <card>
            <template slot="header">
              <h4 class="card-title">{{$ml.with('VueJS').get('txtScheduleTitle')}}</h4>
            </template>
            <div id="external-events">
              <div id="external-events-list">
                <div
                  class="alert alert-success fc-event"
                  v-for="(item, index) in projects"
                  :data-issue="item.issue_id"
                  :key="index"
                  :start="item.start_date"
                  :end="item.end_date"
                  :color="item.value"
                  :style="setStyles(item.value)"
                >
                  <span>{{ item.project }} {{ item.issue }}</span>
                </div>
              </div>
            </div>
          </card>
        </div>
        <div class="col-sm-12 col-lg-9 col-xl-10">
          <div class="filter_search">
            <div class="form-group d-flex align-items-center">
              <label class="mb-0" :style="{ paddingRight: '10px', whiteSpace:'nowrap' }">{{$ml.with('VueJS').get('txtTeam')}}</label>
              <div class="w-100">
                <select-2
                  :options="currentTeamOption"
                  v-model="selectTeam"
                  class="select2"
                ></select-2>
              </div>
            </div>
          </div>
          <FullCalendar
            defaultView="timeGridWeek"
            :scroll-time="scrollTime"
            :plugins="calendarPlugins"
            :header="calendarHeader"
            :business-hours="businessHours"
            :editable="editable"
            :droppable="droppable"
            :events="schedules"
            :event-overlap="true"
            :all-day-slot="allDaySlot"
            :min-time="minTime"
            :max-time="maxTime"
            :height="height"
            :hidden-days="hiddenDays"
            :locale="getLanguage(this.$ml)"
            @datesRender="handleMonthChange"
          />
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import FullCalendar from "@fullcalendar/vue";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timeGrid";
import interactionPlugin, { Draggable } from "@fullcalendar/interaction";
import listPlugin from "@fullcalendar/list";
import Card from "../../components/Cards/Card";
import Select2 from "../../components/SelectTwo/SelectTwo.vue"
import { mapGetters, mapActions } from "vuex"
import moment from "moment";

export default {
  components: {
    FullCalendar, // make the <FullCalendar> tag available
    Card,
    Select2
  },
  computed: { 
    ...mapGetters({
      getArrObjectByID: "getArrObjectByID",
      currentTeamOption: "currentTeamOption",
      currentTeam: "currentTeam",
      typeOptions: "types/options",
    }),
  },
  data() {
    return {
      types: [],
      projects: [],
      projectData: [],
      schedules: [],
      scheduleData: [],
      schedulesDetail: [],

      scrollTime: "7:00:00",
      calendarPlugins: [
        dayGridPlugin,
        listPlugin,
        interactionPlugin,
        timeGridPlugin
      ],
      calendarHeader: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,timeGridDay"
      },
      businessHours: [
        {
          // days of week. an array of zero-based day of week integers (0=Sunday)
          daysOfWeek: [1, 2, 3, 4, 5], // Monday - Thursday

          startTime: "07:00", // a start time (10am in this example)
          endTime: "19:00" // an end time (6pm in this example)
        },
        {
          // days of week. an array of zero-based day of week integers (0=Sunday)
          daysOfWeek: [6], // Monday - Thursday

          startTime: "07:00", // a start time (10am in this example)
          endTime: "12:00" // an end time (6pm in this example)
        }
      ],
      editable: false,
      droppable: false,
      minTime: "07:00:00",
      maxTime: "19:00:00",
      allDaySlot: false,
      height: "auto",
      hiddenDays: [0],

      currentStart: '',
      currentEnd: '',
      selectTeam: ''
    };
  },
  created() {
    const _this = this;
    _this.getOptionType()
  },
  mounted() {
    this.selectTeam = this.currentTeam.id
  },
  methods: {
    ...mapActions("types", {
      getOptionType: "getOptions",
    }),
    fetchItems() {
      let uri = '/data/schedules?startDate=' + moment(this.currentStart).format('YYYY-MM-DD') + '&endDate=' + moment(this.currentEnd).format('YYYY-MM-DD') + '&team_id=' + this.selectTeam + '&only_event=false';
      axios
        .get(uri)
        .then(res => {
          this.types = this.typeOptions;
          this.projectData = res.data.projects;
          this.scheduleData = res.data.schedules;
          this.schedulesDetail = res.data.schedulesDetail;
        })
        .catch(err => {
          console.log(err);
          alert("Could not load projects");
        });
    },
    getObjectValue(data, id) {
      let obj = data.filter(elem => {
        if (elem.id === id) return elem;
      });

      if (obj.length > 0) return obj[0];
    },
    getDataProjects(data) {
      if (data.length) {
        this.projects = data.map((item, index) => {
          let checkTR = item.type.includes("_tr") ? " (TR)" : "";
          return {
            id: item.id,
            project: item.p_name + checkTR,
            issue: item.i_name,
            issue_id: item.issue_id,
            value: this.getObjectValue(this.types, item.type_id).value,
            start_date: this.customFormatter(item.start_date),
            end_date: this.customFormatter(item.end_date)
          };
        });
      }
    },
    getDataSchedules(data) { 
      if (data.length) { 
        this.schedules = data.map((item, index) => {
          let checkTR = item.type.includes("_tr") ? " (TR)" : "";
          const type =  this.getObjectValue(this.types, item.type_id);
          let sDetail = [];
          let description = '';

          if ( this.schedulesDetail.length ) {
            sDetail = this.getArrObjectByID(this.schedulesDetail, item.id);
          }

          const codition = sDetail.length && this.selectTeam == 2 && type.slug != 'yuidea_image';
          const textTime = codition ? sDetail[0].start_time + ' - ' + sDetail[sDetail.length - 1].end_time + '\n' : '';
          const startTime = codition ? sDetail[0].start_time : item.start_time;
          const endTime = codition ? sDetail[0].end_time : item.end_time;

          if ( sDetail.length ) {
            sDetail.map((item, index) => function() {
              description += index > 0 ? '\n' : '';
              description += (item.start_time + ' - ' + item.end_time)
            })
          }

          return {
            id: item.id,
            title:
              textTime +
              (item.i_name
                ? item.p_name + checkTR + " " + item.i_name
                : item.p_name + checkTR) +
              "\n" +
              (item.memo ? item.memo : ""),
            description: description,
            borderColor: type.value,
            backgroundColor: type.value,
            start: moment(item.date + " " + startTime).format(),
            end: moment(item.date + " " + endTime).format()
          };
        });
      }
    },
    setStyles(color) {
      return {
        backgroundColor: color,
        borderColor: color
      };
    },
    customFormatter(date) {
      return moment(date).format("DD-MM-YYYY") !== "Invalid date"
        ? moment(date).format("YYYY-MM-DD")
        : "--";
    },
    getLanguage(data) {
      return data.current;
    },
    handleMonthChange(arg) {
        this.currentStart = arg.view.currentStart;
        this.currentEnd = arg.view.currentEnd;
        if ( this.selectTeam ) this.fetchItems();
    }
  },
  watch: {
    projectData: [
      {
        handler: "getDataProjects"
      }
    ],
    scheduleData: [
      {
        handler: "getDataSchedules"
      }
    ],
    selectTeam: [
      {
        handler: function(value, oldValue) {
          if ( value != oldValue ) this.fetchItems();
        }
      }
    ]
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
    color: rgba(0,0,0,0.8);
    min-height: 38px;
}

.fc-time-grid-event .fc-time,
.fc-time-grid-event .fc-title {
  color: rgba(0,0,0,0.8);
}

.fc-time-grid .fc-slats td {
  height: 3em;
}

.fc-unthemed td.fc-today {
  background-color: transparent;
}

.fc .fc-view-container .fc-head .fc-today {
  background-color: #ffd05b;
}

.filter_search {
  @media screen and (min-width: 1550px) {
    position: absolute;
    width: 200px;
    right: 210px;
  }

  &:after {
    content: "";
    clear: both;
    display: block;
  }

  .form-group {
    float: right;
  }
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