<template>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-4 col-lg-3 col-xl-2">
                    <card>
                        <template slot="header">
                            <h4 class="card-title">Project Schedule</h4>
                        </template>
                        <div id='external-events'>
                            <div id='external-events-list'>
                                <div class="alert alert-success fc-event" v-for="(item, index) in projects" :data-issue="item.issue_id" :key="index" :start="item.start_date" :end="item.end_date" :color="item.value" :style="setStyles(item.value)">
                                    <!-- <button type="button" aria-hidden="true" class="close" data-dismiss="alert">
                                        <i class="nc-icon nc-simple-remove"></i>
                                    </button> -->
                                    <span>{{ item.project }} {{ item.issue }}</span>
                                </div>
                            </div>
                        </div>
                    </card>
                </div>
                <div class="col-sm-8 col-lg-9 col-xl-10">
                    <FullCalendar defaultView="timeGridWeek" :scroll-time="scrollTime" :plugins="calendarPlugins" :header="calendarHeader" :business-hours="businessHours" :editable="editable" :droppable="droppable" :events="schedules" :event-overlap="true" :all-day-slot="allDaySlot" :min-time="minTime" :max-time="maxTime" :height="height" :hidden-days="hiddenDays" @eventReceive="addEvent" @eventDrop="dropEvent" @eventResize="resizeEvent" @eventClick="deleteEvent" />
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timeGrid'
import interactionPlugin, { Draggable } from '@fullcalendar/interaction'
import listPlugin from '@fullcalendar/list'
import Card from '../../components/Cards/Card'
import moment from 'moment'

export default {
    components: {
        FullCalendar, // make the <FullCalendar> tag available
        Card
    },
    data() {
        return {
            types: [],
            projects: [],
            projectData: [],
            schedules: [],
            scheduleData: [],

            scrollTime: '8:00:00',
            calendarPlugins: [dayGridPlugin, listPlugin, interactionPlugin, timeGridPlugin],
            calendarHeader: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            businessHours: [{
                    // days of week. an array of zero-based day of week integers (0=Sunday)
                    daysOfWeek: [1, 2, 3, 4, 5], // Monday - Thursday

                    startTime: '08:00', // a start time (10am in this example)
                    endTime: '17:00', // an end time (6pm in this example)
                },
                {
                    // days of week. an array of zero-based day of week integers (0=Sunday)
                    daysOfWeek: [6], // Monday - Thursday

                    startTime: '08:00', // a start time (10am in this example)
                    endTime: '12:00', // an end time (6pm in this example)
                }
            ],
            editable: true,
            droppable: true,
            minTime: '08:00:00',
            maxTime: '17:00:00',
            allDaySlot: false,
            height: 'auto',
            hiddenDays: [0]
        }
    },
    mounted() {
        this.fetchItems();
        this.makeDraggable();
        // console.log(this.schedules);
    },
    methods: {
        fetchItems() {
            let uri = '/data/schedules';
            axios.get(uri)
                .then(res => {
                    this.types = res.data.types;
                    this.projectData = res.data.projects;
                    this.scheduleData = res.data.schedules;
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not load projects");
                });
        },
        getObjectValue(data, id) {
            let obj = data.filter(function(elem) {
                if (elem.id === id) return elem;
            });

            if (obj.length > 0)
                return obj[0];
        },
        getDataProjects(data) {
            if (data.length) {
                let dataProjects = [];

                for (let i = 0; i < data.length; i++) {
                    let obj = {
                        id: data[i].id,
                        project: data[i].p_name,
                        issue: data[i].i_name,
                        issue_id: data[i].issue_id,
                        value: this.getObjectValue(this.types, data[i].type_id).value,
                        start_date: this.customFormatter(data[i].start_date),
                        end_date: this.customFormatter(data[i].end_date)
                    };
                    dataProjects.push(obj);
                }
                this.projects = dataProjects;
            }
        },
        getDataSchedules(data) {
            if (data.length) {
                let dataSchedules = [];

                for (let i = 0; i < data.length; i++) {
                    let obj = {
                        id: data[i].id,
                        title: data[i].i_name ? data[i].p_name + ' ' + data[i].i_name : data[i].p_name,
                        borderColor: this.getObjectValue(this.types, data[i].type_id).value,
                        backgroundColor: this.getObjectValue(this.types, data[i].type_id).value,
                        start: moment(data[i].date + ' ' + data[i].start_time).format(),
                        end: moment(data[i].date + ' ' + data[i].end_time).format()
                    };
                    dataSchedules.push(obj);
                }
                this.schedules = dataSchedules;
            }
        },
        makeDraggable() {
            let draggableEl = document.getElementById('external-events-list');

            new Draggable(draggableEl, {
                itemSelector: '.fc-event',
                eventData: function(eventEl) {
                    return {
                        title: eventEl.innerText.trim(),
                        id: eventEl.getAttribute("data-issue"),
                        borderColor: eventEl.getAttribute("color"),
                        backgroundColor: eventEl.getAttribute("color"),
                        constraint: {
                            start: moment(eventEl.getAttribute("start") + ' ' + '08:00').format(),
                            end: moment(eventEl.getAttribute("end") + ' ' + '17:00').format()
                        },
                        overlap: true,
                        duration: '1:00:00',
                    }
                }
            });
        },
        setStyles(color) {
            return {
                backgroundColor: color,
                borderColor: color
            };
        },
        customFormatter(date) {
            return moment(date).format('DD-MM-YYYY') !== 'Invalid date' ? moment(date).format('YYYY-MM-DD') : '--';
        },
        hourFormatter(date) {
            return moment(date).format('HH:mm');
        },
        deleteEvent(info) {
            if (confirm("Are you sure delete this event?")) {
                let { id } = info.event;
                console.log(id);
                let uri = '/data/schedules/' + id;
                axios.delete(uri).then((res) => {
                    this.schedules = this.schedules.filter(function(elem) {
                        if (elem.id != id) return elem;
                    });
                    console.log(res.data.message);
                }).catch(err => console.log(err));
            }
        },
        resizeEvent(info) {
            this.editable = false;
            this.droppable = false;

            if (!confirm("Are you sure about this change?")) {
                info.revert();
                this.editable = true;
                this.droppable = true;
            } else {
                let { event } = info;
                let { id, start, end } = event;
                let uri = '/data/schedules/' + id;
                let newItem = {
                    start_time: this.hourFormatter(start),
                    end_time: this.hourFormatter(end)
                };
                axios.patch(uri, newItem)
                    .then(res => {
                        this.editable = true;
                        this.droppable = true;
                    })
                    .catch(err => {
                        console.log(err);
                        this.editable = true;
                        this.droppable = true;
                    });
            }


        },
        dropEvent(info) {
            this.editable = false;
            this.droppable = false;

            if (!confirm("Are you sure about this change?")) {
                info.revert();
                this.editable = true;
                this.droppable = true;
            } else {
                let { event } = info;
                let { id, start, end } = event;
                let uri = '/data/schedules/' + id;
                let newItem = {
                    date: this.customFormatter(start),
                    start_time: this.hourFormatter(start),
                    end_time: this.hourFormatter(end)
                };
                axios.patch(uri, newItem)
                    .then(res => {
                        this.editable = true;
                        this.droppable = true;
                    })
                    .catch(err => {
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
            let uri = '/data/schedules';
            let newItem = {
                issue_id: id,
                title: title,
                borderColor: borderColor,
                backgroundColor: backgroundColor,
                date: this.customFormatter(start),
                start_time: this.hourFormatter(start),
                end_time: this.hourFormatter(end)
            };
            axios.post(uri, newItem)
                .then(res => {
                    this.schedules = [...this.schedules, res.data.event];
                    info.event.remove();
                    this.editable = true;
                    this.droppable = true;
                })
                .catch(err => {
                    console.log(err);
                    this.editable = true;
                    this.droppable = true;
                });
        }
    },
    watch: {
        projectData: [{
            handler: 'getDataProjects'
        }],
        scheduleData: [{
            handler: 'getDataSchedules'
        }]
    }
}
</script>
<style lang="scss">
@import '~@fullcalendar/core/main.css';
@import '~@fullcalendar/daygrid/main.css';
@import '~@fullcalendar/timegrid/main.css';
@import '~@fullcalendar/list/main.css';

.fc-time-grid .fc-event {
    padding: 5px;
}

.fc-event {
    cursor: move;
}

.fc-time-grid-event .fc-time,
.fc-time-grid-event .fc-title {
    color: #ffffff;
}

// tr:first-child>td>.fc-day-grid-event {
//     padding: 5px;
//     color: #ffffff;
// }

.fc-time-grid .fc-slats td {
    height: 2em;
}

.fc-unthemed td.fc-today {
    background-color: transparent;
}

.fc .fc-view-container .fc-head .fc-today {
    background-color: #ffd05b;
}

.fc-unthemed th, .fc-unthemed td, .fc-unthemed thead, .fc-unthemed tbody, .fc-unthemed .fc-divider, .fc-unthemed .fc-row, .fc-unthemed .fc-content, .fc-unthemed .fc-popover, .fc-unthemed .fc-list-view, .fc-unthemed .fc-list-heading td {
    border-color: #b3aeae;
}
</style>