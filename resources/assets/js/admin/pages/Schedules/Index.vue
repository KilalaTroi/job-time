<template>
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-4">
          <div id='external-events'>
            <h4>Draggable Events</h4>

            <div id='external-events-list'>
              <div class='fc-event' id="tsu-140" start="2019-09-15" end="2019-09-21T12:00:00" color="red"
                   style="background: red; border: 1px solid red;">TSUCHIONE 140</div>
              <div class='fc-event' id="kilala" start="2019-09-15" end="2019-09-21" color="orange"
                   style="background: orange; border: 1px solid orange;">KILALA</div>
              <div class='fc-event' id="quvalie" start="2019-09-15" end="2019-09-21" color="darkblue"
                   style="background: darkblue; border: 1px solid darkblue;">Quvalie 10/4</div>
              <div class='fc-event' id="kilala" start="2019-09-15" end="2019-09-21" color="green"
                   style="background: green; border: 1px solid green;">Quvalie TR</div>
              <div class='fc-event' id="tsu-tr" start="2019-09-01" end="2019-09-22" color="brown"
                   style="background: brown; border: 1px solid brown;">TSUCHIONE TR</div>
            </div>
            <!-- <p>
              <input type='checkbox' id='drop-remove' />
              <label for='drop-remove'>remove after drop</label>
            </p> -->
          </div>
        </div>
        <div class="col-sm-8">
          <FullCalendar
              defaultView="timeGridWeek"
              :scroll-time="scrollTime"
              :plugins="calendarPlugins"
              :header="calendarHeader"
              :business-hours="businessHours"
              :editable="true"
              :droppable="true"
              :event-overlap="true"
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
    import interactionPlugin from '@fullcalendar/interaction'
    import listPlugin from '@fullcalendar/list'

    export default {
        components: {
            FullCalendar // make the <FullCalendar> tag available
        },
        data() {
            return {
                scrollTime : '8:00:00',
                calendarPlugins: [ dayGridPlugin, listPlugin, interactionPlugin, timeGridPlugin ],
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
                ]
            }
        },
        mounted() {
            this.makeDraggable();
        },
        methods: {
            makeDraggable() {
                let { Draggable } = interactionPlugin.ExternalDraggable;
                let containerEl = $('#external-events-list');

                new Draggable(containerEl, {
                    itemSelector: '.fc-event',
                    eventData: function (eventEl, aaaa) {
                        console.log("event ~~~~ Data", eventEl, aaaa);
                        return {
                            title: eventEl.innerText.trim(),
                            id: eventEl.getAttribute("id"),
                            borderColor: eventEl.getAttribute("color"),
                            backgroundColor: eventEl.getAttribute("color"),
                            constraint: {
                                start: eventEl.getAttribute("start"),
                                end: eventEl.getAttribute("end")
                            },
                            overlap: true,
                            duration: '1:00:00'
                        }
                    }
                });
            }
        }
    }
</script>
<style lang="scss">
  @import '~@fullcalendar/core/main.css';
  @import '~@fullcalendar/daygrid/main.css';
  @import '~@fullcalendar/timegrid/main.css';
  @import '~@fullcalendar/list/main.css';
</style>
