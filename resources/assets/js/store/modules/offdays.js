import moment from 'moment'


function customFormatter(date) {
    return moment(date).format('DD-MM-YYYY') !== 'Invalid date' ? moment(date).format('YYYY-MM-DD') : '--';
}

function getObjectValue(data, id) {
    let obj = data.filter((elem) => {
        if (elem.id === id) return elem;
    });

    if (obj.length > 0)
        return obj[0];
}

export default {
    namespaced: true,

    state: {
        offDayTypes: [
            {
                id: "morning",
                name: "Half-day (8:00 - 12:00)",
                color: "#00AEEF",
            },
            {
                id: "afternoon",
                name: "Half-day (13:00 - 17:00)",
                color: "#FFDD00",
            },
            {
                id: "all_day",
                name: "Full-day (8:00 - 17:00)",
                color: "#F55555",
            },
        ],
        offDays: [],
        offDaysData: [],
        currentEvent: {},
        currentStart: '',
        currentEnd: ''
    },

    getters: {
        offDayTypes: state => state.offDayTypes,
        offDays: state => state.offDays,
        offDaysData: state => state.offDaysData,
        currentEvent: state => state.currentEvent,
        currentStart: state => state.currentStart,
        currentEnd: state => state.currentEnd
    },

    mutations: {
        // GET_OFF_DAYS: (state, data) => {
        //     state.offDaysData = data;
        //     console.log(data)
        // },

        GET_CURRENT_START: (state, data) => {
            state.currentStart = data;
        },

        GET_CURRENT_END: (state, data) => {
            state.currentEnd = data;
        },

        GET_OFF_DAYS: (state, data) => {
            state.offDays = data;
        },

        ADD_EVENT: (state, data) => {
            // state.offDays = data;
        },

        UPDATE_CURRENT_EVENT: (state, data) => {
            state.currentEvent = data;
        },

        DELETE_EVENT: (state, event_id) => {
            let _offDays = state.offDays;
            _offDays = _offDays.filter((elem) => {
                if (elem.id != event_id) return elem;
            });
            //chua xong ne
        },
    },

    actions: {
        fetchItems({ commit }, user_id) {
            let uri = '/data/offdays?user_id=' + user_id + '&startDate=' + moment(this.currentStart).format('YYYY-MM-DD') + '&endDate=' + moment(this.currentEnd).format('YYYY-MM-DD');
            axios.get(uri)
                .then(res => {
                    commit('GET_OFF_DAYS', res.data.offDays)
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not load Off days");
                });
        },

        getDataOffDays(data) {
            if (data.length) {
                this.offDays = data.map((item, index) => {
                    return {
                        id: item.id,
                        title: getObjectValue(this.offDayTypes, item.type).name,
                        borderColor: getObjectValue(this.offDayTypes, item.type).color,
                        backgroundColor: getObjectValue(this.offDayTypes, item.type).color,
                        start: moment(item.date).format(),
                        end: moment(item.date).format()
                    };
                });
            }
        },
        deleteEvent({ commit }, event) {
            let uri = '/data/offdays/' + event.id;
            axios.delete(uri).then((res) => {
                commit('DELETE_EVENT', event.id);
                $('#editEvent').modal('hide');
            }).catch(err => console.log(err));
        },
        clickEvent({ commit }, info) {
            commit('UPDATE_CURRENT_EVENT', info.event);
            $('#editEvent').modal('show');
        },
        addEvent({ commit }, info) {
            let { event } = info;
            let { id, start, end, borderColor, backgroundColor, title } = event;
            let uri = '/data/offdays';
            let newItem = {
                user_id: this.userID,
                type: id,
                date: customFormatter(start),
                start: start,
                end: end,
                borderColor: borderColor,
                backgroundColor: backgroundColor,
                title: title
            };
            axios.post(uri, newItem)
                .then(res => {
                    if (res.data.oldEvent) {
                        this.offDays = this.offDays.filter((elem) => {
                            if (res.data.oldEvent.indexOf(elem.id) === -1) {
                                return elem;
                            }
                        });
                    }
                    this.offDays = [...this.offDays, res.data.event];
                    info.event.remove();
                    commit('ADD_EVENT', ABC)

                })
                .catch(err => {
                    console.log(err);
                });
        },
        handleMonthChange({ commit }, arg) {
            commit('GET_CURRENT_START', arg.view.currentStart)
            commit('GET_CURRENT_END', arg.view.currentEnd)
        }
    }
}
