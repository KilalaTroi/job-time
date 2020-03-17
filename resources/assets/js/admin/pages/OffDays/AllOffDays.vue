<template>
    <card>
        <h4 slot="header" class="card-title">{{$ml.with('VueJS').get('txtStaffOffDay')}}</h4>
        
        <FullCalendar class="off-days" defaultView="dayGridMonth" :plugins="calendarPlugins" :header="calendarHeader" :business-hours="businessHours" :editable="editable" :droppable="droppable" :events="offDays" :all-day-slot="allDaySlot" :height="height" :hidden-days="hiddenDays" :locale="getLanguage(this.$ml)" />
    </card>
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
                    color: '#FF0000'
                }
            ],
            offDays: [],
            offDaysData: [],

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
            hiddenDays: [0]
        }
    },
    mounted() {
        this.fetchItems();
    },
    methods: {
        fetchItems() {
            let uri = '/data/all-off-days';
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
                        title: item.name,
                        borderColor: this.getObjectValue(this.offDayTypes, item.type).color,
                        backgroundColor: this.getObjectValue(this.offDayTypes, item.type).color,
                        start: moment(item.date).format(),
                        end: moment(item.date).format()
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
            return moment(date).format('DD-MM-YYYY') !== 'Invalid date' ? moment(date).format('YYYY-MM-DD') : '--';
        },
        getLanguage(data) {
            return data.current
        },
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