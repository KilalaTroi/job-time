export default {
  namespaced: true,

  state: {
    data: {},
    filters: {
      currentStart: '',
      currentEnd: '',
      team: '',
    },
    fullCalendar: {
      editable: true,
      droppable: true
    },
    selectedItem: {},
    validationErrors: '',
    validationSuccess: ''
  },

  getters: {
    data: state => state.data,
    search: state => state.search,
    fullCalendar: state => state.fullCalendar,
    filters: state => state.filters,
    selectedItem: state => state.selectedItem,
    validationErrors: state => state.validationErrors,
    validationSuccess: state => state.validationSuccess
  },

  mutations: {
    SET_DATA: (state, data) => {
      state.data = {
        projects: data.projects,
        schedules: data.schedules,
        schedulesDetail: data.schedulesDetail,
        projectsFilter: data.projects,
        issuesNoSC: data.issues
      }
    },

    SET_DATA_SCHEDULE: (state, data) => {
      state.data.schedules = data.schedules;
      state.data.schedulesDetail = data.schedulesDetail;
      state.data.issuesNoSC = data.issues;
    },

    SET_DATA_CALENDAR: (state, data) => {
      state.fullCalendar = {
        editable: data.editable,
        droppable: data.droppable
      }
    },

    SET_SELECTED_ITEM: (state, selectedItem) => {
      state.selectedItem = Object.assign({}, selectedItem)
    },

    SET_FILTER: (state, data) => {
      if (data.view) {
        state.filters.currentStart = data.view.currentStart
        state.filters.currentEnd = data.view.currentEnd
      }
      if (data.team_id) state.filters.team_id = data.team_id
    },

    SET_DATA_SEARCH: (state, data) => {
      state.data.projectsFilter = data
    },

    SET_VALIDATE: (state, data) => {
      state.validationErrors = data.error
      state.validationSuccess = data.success
    }
  },

  actions: {
    async getAll({ commit, state, rootGetters, rootState }, onlyEvent = false) {
      const currentStart = rootGetters['dateFormat'](state.filters.currentStart, 'YYYY-MM-DD')
      const currentEnd = rootGetters['dateFormat'](state.filters.currentEnd, 'YYYY-MM-DD')
      const uri = '/data/schedules?startDate=' + currentStart + '&endDate=' + currentEnd + '&team_id=' + state.filters.team + '&only_event=' + onlyEvent

      await axios.get(uri).then(response => {
        $('.tooltip').remove();
        if (response.data.schedules.length) {
          let schedulesVariation = [];
          response.data.schedules = response.data.schedules.map((item, index) => {
            const arrProjects = [58, 59]; // Project show description and hide fc-time.
            const arrProjectsHT = [58]; // Project hide fc-time.
            const arrProjectsPV = [58];  // Project don't have Variation.
            let sDetail = [];
            let description = '';

            // Get log time detail for schedule
            if ( response.data.schedulesDetail.length && !item.all_date ) sDetail = rootGetters['getLogTime'](response.data.schedulesDetail, item.issue_id, item.date);
            const codition = sDetail.length && (state.filters.team == 2) && ! arrProjectsPV.includes(item.p_id)
            const textTime = sDetail.length && (state.filters.team == 2) && arrProjectsHT.includes(item.p_id) ? '<span>' + sDetail[0].start_time + ' - ' + sDetail[sDetail.length - 1].end_time + '</span><br>' : '';
            const startTime = codition ? sDetail[0].start_time : item.start_time;
            const endTime = codition ? sDetail[sDetail.length - 1].end_time : item.end_time;
            const classHideTime = textTime ? ' hide-fc-time' : '';

            // Get description for schedule
            if ( sDetail.length && (state.filters.team == 2) && arrProjects.includes(item.p_id) ) {
              description = sDetail.map((item) => {
                const note = item.note ? ' (' + item.note + ')' : '';
                return (item.start_time + ' - ' + item.end_time + note)
              }).join('<br>')
            }

            // Function return schedule
            let getSchedule = (_item, _value, _codition) => {
              const memo = _item.memo ? _item.memo : "";

              return Object.assign({}, _item, {
                id: _item.id,
                title: _item.title + textTime + _item.name + '<br>' + memo,
                description: description,
                className: textTime ? 'has-log-time' + classHideTime : '' + classHideTime,
                start: rootGetters['dateFormat'](_item.date + " " + _value.start_time),
                end: _item.s_end_date ? rootGetters['dateFormat'](_item.s_end_date + " " + _value.end_time) : rootGetters['dateFormat'](_item.date + " " + _value.end_time),
                allDay: _item.all_date,
                memo: _item.memo,
              })
            }

            // Get schedule variation
            if ( codition && sDetail.length > 1 ) {
              let scVariation = [];

              sDetail.forEach((value, index) => {
                if ( index ) {
                  if ( sDetail[index].start_time.replace(':', '') * 1 > sDetail[index - 1].end_time.replace(':', '') * 1 ) {
                    scVariation.push( getSchedule(item, value, codition) );
                  } else {
                    if ( sDetail[index].end_time.replace(':', '') * 1 > sDetail[index - 1].end_time.replace(':', '') * 1 ) {
                      scVariation[scVariation.length - 1].end_time = sDetail[index].end_time;
                    }
                  }
                } else {
                  scVariation.push( getSchedule(item, value, codition) );
                }
              })

              // concat schedules variation
              schedulesVariation = [...scVariation, ...schedulesVariation];

            } else { // don't have schedule variation

              return getSchedule(item, {start_time: startTime, end_time: endTime}, codition);

            }

          });

          // concat schedules and schedules variation
          response.data.schedules = [...response.data.schedules.filter(x => x), ...schedulesVariation]
        }

        if (!onlyEvent && response.data.projects.length) {
          response.data.projects = response.data.projects.map((item, index) => {
            return Object.assign({}, item, {
              start_date: rootGetters['dateFormat'](item.start_date),
              end_date: rootGetters['dateFormat'](item.end_date),
            })
          });
        }

        if ( !onlyEvent ) commit('SET_DATA', response.data)
        if ( onlyEvent ) commit('SET_DATA_SCHEDULE', response.data)
      })
    },

    handleMonthChange({ commit }, data) {
      $('.tooltip').remove();
      commit('SET_FILTER', data);
      setTimeout(function() {
        $('.fc-event.fc-short').removeClass('fc-short');
      }, 3000);
    },

    resetValidate({ dispatch, commit }) {
      dispatch('getAll', true)
      commit('SET_VALIDATE', { error: '', success: '' })
    },

    searchItem({ commit, state }, value) {
      let dataSearchResults = state.data.projects
      dataSearchResults = (state.data.projects).filter((item) => {
        return item.fullname.toLowerCase().includes(value.toLowerCase());
      });
      commit('SET_DATA_SEARCH', dataSearchResults)
    },

    addSchedule({ commit, state, rootGetters, dispatch }, data) {
      commit('SET_VALIDATE', { error: '', success: '' })
      commit('SET_DATA_CALENDAR', {editable: false, droppable: false})

      const { event } = data;
      const request = {
        method: "post",
        uri: "/data/schedules",
        data: {
          issue_id: event.id,
          title: event.title,
          borderColor: event.borderColor,
          backgroundColor: event.backgroundColor,
          date: rootGetters['dateFormat'](event.start, "YYYY-MM-DD"),
          end_date: rootGetters['dateFormat'](event.end, "YYYY-MM-DD"),
          all_date: event.allDay,
          start_time: rootGetters['dateFormat'](event.start, 'HH:mm'),
          end_time: rootGetters['dateFormat'](event.end, 'HH:mm'),
          team_id: state.filters.team
        },
        event: event,
      }
      dispatch('functionFullCalendar', request)
    },

    dropSchedule({ commit, rootGetters, dispatch }, data) {
      commit('SET_VALIDATE', { error: '', success: '' })
      commit('SET_DATA_CALENDAR', {editable: false, droppable: false})
        const { event } = data;
        const request = {
          method: "patch",
          uri: "/data/schedules/" + event.id,
          data: {
            date: rootGetters['dateFormat'](event.start, "YYYY-MM-DD"),
            end_date: rootGetters['dateFormat'](event.end, "YYYY-MM-DD"),
            start_time: rootGetters['dateFormat'](event.start, 'HH:mm'),
            end_time: rootGetters['dateFormat'](event.end, 'HH:mm'),
            all_date: event.allDay,
            booking: event._def.extendedProps.booking,
            memo: event.extendedProps.memo,
            daten: event.start
          }
        }
        dispatch('functionFullCalendar', request)
      // }
    },

    resetSelectedItem({ commit }) {
      commit('SET_SELECTED_ITEM', {})
    },

    resizeSchedule({ commit, rootGetters, dispatch }, data) {
      commit('SET_VALIDATE', { error: '', success: '' })
      commit('SET_DATA_CALENDAR', {editable: false, droppable: false})
        const { event } = data;
        const request = {
          method: "patch",
          uri: "/data/schedules/" + event.id,
          data: {
            date: rootGetters['dateFormat'](event.start, "YYYY-MM-DD"),
            end_date: rootGetters['dateFormat'](event.end, "YYYY-MM-DD"),
            start_time: rootGetters['dateFormat'](event.start, 'HH:mm'),
            end_time: rootGetters['dateFormat'](event.end, 'HH:mm'),
            booking: event.extendedProps.booking,
            memo: event.extendedProps.memo,
            daten: event.start
          }
        }
        dispatch('functionFullCalendar', request)
      // }
    },

    functionFullCalendar({ commit, dispatch }, request) {
      axios({
        method: request.method,
        url: request.uri,
        data: request.data
      }).then((res) => {
        commit('SET_DATA_CALENDAR', {editable: true, droppable: true})
        commit('SET_VALIDATE', { error: '', success: res.data.message })
        if ( request.event ) request.event.remove()
      }).catch((err) => {
        console.log(err);
        commit('SET_DATA_CALENDAR', {editable: true, droppable: true})
        if (err.response) {
          if (err.response.status == 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
        }
      });
      dispatch('getAll', true)
    },

    getItem({ commit, rootGetters }, data) {
      commit('SET_VALIDATE', { error: '', success: '' })
      const { event } = data;
      const titleArray = (event).title.split("<br>");
      let datew = event.start.setDate(event.start.getDate()+1);
      datew = new Date(datew)
      const item = {
        id: event.id,
        title_not_memo: titleArray.length > 2 ? titleArray[1] : titleArray[0],
        memo: titleArray.length > 2 ? titleArray[2] : titleArray[1],
        p_id: event.extendedProps.p_id,
        booking: event.extendedProps.booking,
        datew: datew,
        daten: event.start,
        start_time: rootGetters['dateFormat'](event.start, 'HH:mm'),
        end_time: rootGetters['dateFormat'](event.end, 'HH:mm'),
      }
      $("#itemDetail").modal("show");
      commit('SET_SELECTED_ITEM', item)
    },

    updateItem({ state, commit }, item) {
      commit('SET_VALIDATE', { error: '', success: '' })
      const uri = "/data/schedules/" + state.selectedItem.id;
      const newItem = {
        memo: item.memo,
        booking: item.booking,
        weekendcheck: item.weekendcheck,
        weekend: item.weekend,
        daten: item.daten
      };
      axios
        .patch(uri, newItem)
        .then((res) => {
          commit('SET_VALIDATE', { error: '', success: res.data.message })
        })
        .catch((err) => {
          console.log(err);
          if (err.response.status == 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
        });
    },

    deleteItem({ dispatch, state }, msgText) {
      $("#itemDetail").modal("hide");
      if (confirm(msgText)) {
        const uri = "/data/schedules/" + state.selectedItem.id
        axios.delete(uri)
          .then(res => {
            dispatch('getAll', true)
          })
          .catch(err => console.log(err))
      }
    },
  }
}
