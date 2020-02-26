<template>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12 col-lg-3 col-xl-2">
                    <card>
                        <template slot="header">
                            <h4 class="card-title">Project Schedule</h4>
                        </template>
                        <div class="form-group">
                            <input v-model="search" placeholder="Search..." type="text" class="form-control" v-on:keyup="searchItem">
                        </div>
                        <div id='external-events'>
                            <div id='external-events-list'>
                                <div class="alert alert-success fc-event" v-for="(item, index) in searchResults" :data-issue="item.issue_id" :key="index" :start="item.start_date" :end="item.end_date" :color="item.value" :style="setStyles(item.value)">
                                    <span>{{ item.project }} {{ item.issue }}</span>
                                </div>
                            </div>
                        </div>
                    </card>
                </div>
                <div class="col-sm-12 col-lg-9 col-xl-10">
                    <FullCalendar defaultView="timeGridWeek" :scroll-time="scrollTime" :plugins="calendarPlugins" :header="calendarHeader" :business-hours="businessHours" :editable="editable" :droppable="droppable" :events="schedules" :event-overlap="true" :all-day-slot="allDaySlot" :min-time="minTime" :max-time="maxTime" :height="height" :hidden-days="hiddenDays" @eventReceive="addEvent" @eventDrop="dropEvent" @eventResize="resizeEvent" @eventClick="clickEvent" />
                </div>
            </div>
        </div>

        <EditEvent :currentEvent="currentEvent" :errors="validationErrors" :success="validationSuccess" v-on:update-event="updateEvent" v-on:delete-event="deleteEvent" v-on:reset-validation="resetValidate"></EditEvent>
    </div>
</template>


<script>
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timeGrid'
import interactionPlugin, { Draggable } from '@fullcalendar/interaction'
import listPlugin from '@fullcalendar/list'
import Card from '../../components/Cards/Card'
import EditEvent from './Edit'
import moment from 'moment'

export default {
    components: {
        FullCalendar, // make the <FullCalendar> tag available
        Card,
        EditEvent
    },
    data() {
        return {
            memo:"",
            types: [],
            projects: [],
            projectData: [],
            schedules: [],
            scheduleData: [],
            currentEvent: {},

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
            hiddenDays: [0],

            validationErrors: '',
            validationSuccess: '',

            search: '',
            searchResults: []
        }
    },
    mounted() {
        this.fetchItems();
        this.makeDraggable();
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
            let obj = data.filter((elem) => {
                if (elem.id === id) return elem;
            });

            if (obj.length > 0)
                return obj[0];
        },
        getDataProjects(data) {
            if (data.length) {
                let dataProjects = data.map((item, index) => {
                    let checkTR = item.type.includes("_tr") ? " (TR)" : "";
                    return {
                        id: item.id,
                        project: item.p_name + checkTR,
                        issue: item.i_name,
                        issue_id: item.issue_id,
                        value: this.getObjectValue(this.types, item.type_id).value,
                        start_date: this.customFormatter(item.start_date),
                        end_date: this.customFormatter(item.end_date)
                    }
                });
                this.projects = this.searchResults = dataProjects;
            }
        },
        getDataSchedules(data) {
            if (data.length) {
                this.schedules = data.map((item, index) => {
                    let checkTR = item.type.includes("_tr") ? " (TR)" : "";
                    return {
                        id: item.id,
                        title: (item.i_name ? item.p_name + checkTR + ' ' + item.i_name : item.p_name + checkTR) + '\n' + (item.memo ? item.memo : ''),
                        borderColor: this.getObjectValue(this.types, item.type_id).value,
                        backgroundColor: this.getObjectValue(this.types, item.type_id).value,
                        start: moment(item.date + ' ' + item.start_time).format(),
                        end: moment(item.date + ' ' + item.end_time).format(),
                        memo: item.memo,
                        title_not_memo: item.i_name ? item.p_name + checkTR + ' ' + item.i_name : item.p_name + checkTR
                    };
                });
            }
        },
        makeDraggable() {
            let draggableEl = document.getElementById('external-events-list');

            new Draggable(draggableEl, {
                itemSelector: '.fc-event',
                eventData: (eventEl) => {
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
        deleteEvent(event) {
            $('#editEvent').modal('hide');

            if (confirm("Are you sure you want to delete this event?")) {
                let uri = '/data/schedules/' + event.id;
                axios.delete(uri).then((res) => {
                    this.schedules = this.schedules.filter((elem) => {
                        if (elem.id != event.id) return elem;
                    });
                    
                }).catch(err => console.log(err));
            }
        },
        updateEvent(event) {
            // Reset validate
            this.validationErrors = '';
            this.validationSuccess = '';

            let uri = '/data/schedules/' + event.id;
            let newItem = {
                memo: event.memo
            };
            axios.patch(uri, newItem)
                .then(res => {
                    let foundIndex = this.schedules.findIndex(x => x.id == event.id);
                    this.schedules[foundIndex].title = this.schedules[foundIndex].title_not_memo  + '\n' + event.memo ;
                    this.schedules = [...this.schedules];
                    this.validationSuccess = res.data.message;
                })
                .catch(err => {
                    this.validationErrors = err.response.data;
                    this.validationSuccess = '';
                });

        },
        clickEvent(info) {
            this.currentEvent = info.event;
            let titleArray = this.currentEvent.title.split('\n');
            this.currentEvent.title_not_memo = titleArray[0];
            this.currentEvent.memo = titleArray[1];
            $('#editEvent').modal('show');
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
                        let foundIndex = this.schedules.findIndex(x => x.id == id);
                        this.schedules[foundIndex].start = moment(start).format();
                        this.schedules[foundIndex].end = moment(end).format();
                        this.schedules = [...this.schedules];

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
                        let foundIndex = this.schedules.findIndex(x => x.id == id);
                        this.schedules[foundIndex].start = moment(start).format();
                        this.schedules[foundIndex].end = moment(end).format();
                        this.schedules = [...this.schedules];

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
        },
        searchItem() {
            let value = this.search;
            if ( value ) {
                this.searchResults = this.projects.filter(item => {
                    let title = item.project + " " + item.issue;
                    return title.toLowerCase().includes(value.toLowerCase());
                });
            } else {
                this.searchResults = this.projects;
            }
        },
        resetValidate() {
            this.validationSuccess = '';
            this.validationErrors = '';
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
    color: rgba(0,0,0,0.8);
}

.fc-time-grid-event .fc-time,
.fc-time-grid-event .fc-title {
    color: rgba(0,0,0,0.8);
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

.fc-unthemed th, .fc-unthemed td, .fc-unthemed thead, .fc-unthemed tbody, .fc-unthemed .fc-divider, .fc-unthemed .fc-row, .fc-unthemed .fc-content, .fc-unthemed .fc-popover, .fc-unthemed .fc-list-view, .fc-unthemed .fc-list-heading td {
    border-color: #b3aeae;
}
</style>