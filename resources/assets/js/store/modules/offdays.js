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
        offDaysRaw: [],
        offDaysData: [],
        currentEvent: {},
        currentStart: '',
        currentEnd: ''
    },

    getters: {
        offDayTypes: state => state.offDayTypes,
        offDaysRaw: state => state.offDaysRaw,
        offDaysData: state => state.offDaysData,
        currentEvent: state => state.currentEvent,
        currentStart: state => state.currentStart,
        currentEnd: state => state.currentEnd
    },

    mutations: {
        GET_OFF_DAYS_RAW: (state, data) => {
            state.offDaysRaw = data
        },

        GET_OFF_DAYS_DATA: (state, data) => {
            state.offDaysData = data
        },

        GET_CURRENT_START: (state, data) => {
            state.currentStart = data
        },

        GET_CURRENT_END: (state, data) => {
            state.currentEnd = data
        },

        UPDATE_CURRENT_EVENT: (state, data) => {
            state.currentEvent = data
        },

        DELETE_EVENT: (state, id) => {
            state.offDaysData = state.offDaysData.filter((elem) => {
                if (elem.id != id) return elem
            });
        },

        ADD_OFF_DAY: (state, data) => {
            if (data.oldOffDays) {
                state.offDaysData = state.offDaysData.filter((elem) => {
                    if (data.oldOffDays.indexOf(elem.id) === -1) {
                        return elem
                    }
                })
            }
            state.offDaysData = [...state.offDaysData, data.newOffDay]
        }
    },

    actions: {
        getOffDaysRaw({ commit, state, rootState, rootGetters, dispatch }) {
            const uri = '/data/offdays?user_id=' + rootState.loginUser.id + '&startDate=' + rootGetters['dateFormat'](state.currentStart, 'YYYY-MM-DD') + '&endDate=' + rootGetters['dateFormat'](state.currentEnd, 'YYYY-MM-DD');
            const uriWithTeam = rootState.queryTeam ? uri + '&' + rootState.queryTeam : uri

            axios.get(uriWithTeam)
                .then(res => {
                    commit('GET_OFF_DAYS_RAW', res.data.offDays)
                    dispatch('getDataOffDays', res.data.offDays)
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not load Off days");
                });
        },

        getDataOffDays({ commit, rootGetters, state }, data) {
            if (data.length) {
                const offDays = data.map((item, index) => {
                    return {
                        id: item.id,
                        title: rootGetters['getObjectByID'](state.offDayTypes, item.type).name,
                        borderColor: rootGetters['getObjectByID'](state.offDayTypes, item.type).color,
                        backgroundColor: rootGetters['getObjectByID'](state.offDayTypes, item.type).color,
                        start: rootGetters['dateFormat'](item.date),
                        end: rootGetters['dateFormat'](item.date)
                    };
                });

                commit('GET_OFF_DAYS_DATA', offDays)
            }
        },

        deleteEvent({ commit, rootState }, event) {
            const uri = '/data/offdays/' + event.id;
            const uriWithTeam = rootState.queryTeam ? uri + '?' + rootState.queryTeam : uri

            axios.delete(uriWithTeam).then((res) => {
                commit('DELETE_EVENT', event.id);
                $('#editEvent').modal('hide');
            }).catch(err => console.log(err));
        },

        clickEvent({ commit }, info) {
            commit('UPDATE_CURRENT_EVENT', info.event);
            $('#editEvent').modal('show');
        },

        addEvent({ commit, rootState, rootGetters }, info) {
            const { event } = info;
            const { id, start, end, borderColor, backgroundColor, title } = event;
            const uri = '/data/offdays?user_id=' + rootState.loginUser.id;
            const uriWithTeam = rootState.queryTeam ? uri + '&' + rootState.queryTeam : uri

            const newItem = {
                user_id: this.userID,
                type: id,
                date: rootGetters['dateFormat'](start, 'YYYY-MM-DD'),
                start: start,
                end: end,
                borderColor: borderColor,
                backgroundColor: backgroundColor,
                title: title
            };

            axios.post(uriWithTeam, newItem)
                .then(res => {
                    const data = {
                        oldOffDays: res.data.oldEvent,
                        newOffDay: res.data.event
                    }
                    commit('ADD_OFF_DAY', data)
                    info.event.remove();
                })
                .catch(err => {
                    console.log(err);
                });
        },

        handleMonthChange({ commit, dispatch }, arg) {
            commit('GET_CURRENT_START', arg.view.currentStart)
            commit('GET_CURRENT_END', arg.view.currentEnd)
            dispatch('getOffDaysRaw')
        }
    }
}
