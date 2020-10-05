<template>
    <card>
        <h4 slot="header" class="card-title">{{$ml.with('VueJS').get('txtMyOffDay')}}</h4>
        <div class="row">
            <div class="col-sm-12 col-lg-3">
                <card>
                    <template slot="header">
                        <h4 class="card-title">{{$ml.with('VueJS').get('txtType')}}</h4>
                    </template>
                    <div id='external-events'>
                        <div id='external-events-list'>
                            <div class="alert alert-success fc-event" v-for="(item, index) in offDayTypes" :data-type="item.id" :key="index" :color="item.color" :style="setStyles(item.color)">
                                <span><b>{{ item.name }}</b></span>
                            </div>
                        </div>
                    </div>
                </card>
            </div>
            <div class="col-sm-12 col-lg-9">
                <FullCalendar class="off-days" defaultView="dayGridMonth" :plugins="calendarPlugins" :header="calendarHeader" :business-hours="businessHours" :editable="editable" :droppable="droppable" :events="offDays" :all-day-slot="allDaySlot" :height="height" :hidden-days="hiddenDays" @eventReceive="addEvent" @eventClick="clickEvent" :locale="getLanguage(this.$ml)" :datesRender="handleMonthChange" />
            </div>
        </div>

        <EditEvent :currentEvent="currentEvent" v-on:delete-event="deleteEvent"></EditEvent>
    </card>
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
            userID: document.querySelector("meta[name='user-id']").getAttribute('content'),
            offDayTypes: [
                {
                    id: 'morning',
                    name: 'Half-day (8:00 - 12:00)',
                    color: '#00AEEF'
                },
                {
                    id: 'afternoon',
                    name: 'Half-day (13:00 - 17:00)',
                    color: '#FFDD00'
                },
                {
                    id: 'all_day',
                    name: 'Full-day (8:00 - 17:00)',
                    color: '#F55555'
                }
            ],
            offDays: [],
            offDaysData: [],
            currentEvent: {},

            calendarPlugins: [listPlugin, interactionPlugin, dayGridPlugin, timeGridPlugin],
            calendarHeader: {
                left: 'prev',
                center: 'title',
                right: 'next'
            },
            businessHours: [{
                    // days of week. an array of zero-based day of week integers (0=Sunday)
                    daysOfWeek: [1, 2, 3, 4, 5], // Monday - Thursday
                },
                {
                    // days of week. an array of zero-based day of week integers (0=Sunday)
                    daysOfWeek: [6], // Monday - Thursday
                }
            ],
            editable: false,
            droppable: false,
            allDaySlot: false,
            height: 'auto',
            hiddenDays: [0],

            currentStart: '',
            currentEnd: ''
        }
    },
    mounted() {
        this.makeDraggable();
    },
    methods: {
        fetchItems() {
            let uri = '/data/offdays?user_id=' + this.userID + '&startDate=' + moment(this.currentStart).format('YYYY-MM-DD') + '&endDate=' + moment(this.currentEnd).format('YYYY-MM-DD');
            axios.get(uri)
                .then(res => {
                    this.offDaysData = res.data.offDays;
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not load Off days");
                });
        },
        getObjectValue(data, id) {
            let obj = data.filter((elem) => {
                if (elem.id === id) return elem;
            });

            if (obj.length > 0)
                return obj[0];
        },
        getDataOffDays(data) {
            if (data.length) {
                this.offDays = data.map((item, index) => {
                    return {
                        id: item.id,
                        title: this.getObjectValue(this.offDayTypes, item.type).name,
                        borderColor: this.getObjectValue(this.offDayTypes, item.type).color,
                        backgroundColor: this.getObjectValue(this.offDayTypes, item.type).color,
                        start: moment(item.date).format(),
                        end: moment(item.date).format()
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
                        id: eventEl.getAttribute("data-type"),
                        borderColor: eventEl.getAttribute("color"),
                        backgroundColor: eventEl.getAttribute("color")
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
        deleteEvent(event) {
            $('#editEvent').modal('hide');

            let uri = '/data/offdays/' + event.id;
            axios.delete(uri).then((res) => {
                this.offDays = this.offDays.filter((elem) => {
                    if (elem.id != event.id) return elem;
                });
                
            }).catch(err => console.log(err));
        },
        clickEvent(info) {
            this.currentEvent = info.event;
            $('#editEvent').modal('show');
        },
        addEvent(info) {

            let { event } = info;
            let { id, start, end, borderColor, backgroundColor, title } = event;
            let uri = '/data/offdays';
            let newItem = {
                user_id: this.userID,
                type: id,
                date: this.customFormatter(start),
                start: start,
                end: end,
                borderColor: borderColor, 
                backgroundColor: backgroundColor,
                title: title
            };
            axios.post(uri, newItem)
                .then(res => {
                    if ( res.data.oldEvent ) {
                        this.offDays = this.offDays.filter((elem) => {
                            if ( res.data.oldEvent.indexOf(elem.id) === -1 ) {
                                return elem;
                            }
                        });
                    }
                    this.offDays = [...this.offDays, res.data.event];
                    info.event.remove();
                })
                .catch(err => {
                    console.log(err);
                });
        },
        getLanguage(data) {
            return data.current
        },
        handleMonthChange(arg) {
            this.currentStart = arg.view.currentStart;
            this.currentEnd = arg.view.currentEnd;
            this.fetchItems();
        }
    },
    watch: {
        offDaysData: [{
            handler: 'getDataOffDays'
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

.off-days .fc-day-grid-event .fc-time {
    display: none;
}
</style>