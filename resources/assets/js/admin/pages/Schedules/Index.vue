<template>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-4 col-lg-9 col-xl-2">
                    <card>
                        <template slot="header">
                            <h4 class="card-title">Project Schedule</h4>
                        </template>

                        <div id='external-events'>
                            <div id='external-events-list'>
                                <div class="alert alert-success fc-event" v-for="(item, index) in projectData"
                                     :data-issue="item.issue_id" :key="index" :start="item.start_date"
                                     :end="item.end_date" :color="item.value"
                                     :style="setStyles(item.value)">
                                    <button type="button" aria-hidden="true" class="close" data-dismiss="alert">
                                        <i class="nc-icon nc-simple-remove"></i>
                                    </button>
                                    <span>{{ item.project }} {{ item.issue }}</span>
                                </div>


                            </div>
                        </div>
                    </card>
                </div>
                <div class="col-sm-8 col-lg-9 col-xl-10">
                    <FullCalendar
                            defaultView="timeGridWeek"
                            :scroll-time="scrollTime"
                            :plugins="calendarPlugins"
                            :header="calendarHeader"
                            :business-hours="businessHours"
                            :editable="true"
                            :droppable="true"
                            :events="schedules"
                            :event-overlap="true"
                            @eventDragStop="showEvent"
                            @eventReceive="addEvent"
                    />
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

                scrollTime: '8:00:00',
                calendarPlugins: [dayGridPlugin, listPlugin, interactionPlugin, timeGridPlugin],
                calendarHeader: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                businessHours: [
                    {
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

                newEvent: {
                    title: "",
                    dataIssue: ""
                },
            }
        },
        mounted() {
            this.fetchItems();
            this.makeDraggable();
            // console.log(this.schedules);
        },
        methods: {
            fetchItems() {
                let uri = '/api/v1/schedules';
                axios.get(uri)
                    .then(res => {
                        this.types = res.data.types;
                        this.projects = res.data.projects;
                    })
                    .catch(err => {
                        console.log(err);
                        alert("Could not load projects");
                    });
            },
            getObjectValue(data, id) {
                let obj = data.filter(function (elem) {
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
                    this.projectData = dataProjects;
                }
            },
            makeDraggable() {
                let draggableEl = document.getElementById('external-events-list');

                new Draggable(draggableEl, {
                    itemSelector: '.fc-event',
                    eventData: function (eventEl) {
                        return {
                            title: eventEl.innerText.trim(),
                            id: eventEl.getAttribute("data-issue"),
                            borderColor: eventEl.getAttribute("color"),
                            backgroundColor: eventEl.getAttribute("color"),
                            constraint: {
                                start: eventEl.getAttribute("start"),
                                end: eventEl.getAttribute("end"),
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
            showEvent(arg) {
                // console.log(arg);
            },
            addEvent(info) {
                let { id } = info.event;
                console.log(id);
            }
        },
        watch: {
            projects: [{
                handler: 'getDataProjects'
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

    .fc-time-grid-event .fc-time, .fc-time-grid-event .fc-title {
        color: #ffffff;
    }

    tr:first-child > td > .fc-day-grid-event {
        padding: 5px;
        color: #ffffff;
    }
</style>
