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
        projectsFilter: data.projects
      }
    },

    SET_DATA_SCHEDULE: (state, data) => {
      state.data.schedules = data.schedules
    },

    SET_DATA_CALENDAR: (state, data) => {
      state.fullCalendar = {
        editable: data.editable,
        droppable: data.droppable
      }
    },

    SET_SELECTED_ITEM: (state, selectedItem) => {
      state.selectedItem = selectedItem
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
        if (response.data.schedules.length) {
          response.data.schedules = response.data.schedules.map((item, index) => {
            const checkTR = item.type.includes("_tr") ? " (TR)" : "";
            
            return Object.assign({}, {
              title:
                (item.i_name
                  ? item.p_name + checkTR + " " + item.i_name
                  : item.p_name + checkTR) +
                "\n" +
                (item.memo ? item.memo : ""),
              borderColor: rootGetters['getObjectByID'](rootState.types.options, item.type_id).value,
              backgroundColor: rootGetters['getObjectByID'](rootState.types.options, item.type_id).value,
              start: rootGetters['dateFormat'](item.date + " " + item.start_time),
              end: rootGetters['dateFormat'](item.date + " " + item.end_time),
              memo: item.memo,
              title_not_memo: item.i_name
                ? item.p_name + checkTR + " " + item.i_name
                : item.p_name + checkTR,
            }, item)
          });
        }

        if (!onlyEvent && response.data.projects.length) {
          response.data.projects = response.data.projects.map((item, index) => {
            const checkTR = item.type.includes("_tr") ? " (TR)" : "";

            return Object.assign({}, item, {
              project: item.p_name + checkTR,
              issue: item.i_name,
              value: rootGetters['getObjectByID'](rootState.types.options, item.type_id).value,
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
      commit('SET_FILTER', data) 
    },
    
    resetValidate({ dispatch, commit }) {
      dispatch('getAll', true)
      commit('SET_VALIDATE', { error: '', success: '' })
    },

    searchItem({ commit, state }, value) {
      let dataSearchResults = state.data.projects
      dataSearchResults = (state.data.projects).filter((item) => {
        let title = item.project + " " + item.issue;
        return title.toLowerCase().includes(value.toLowerCase());
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
          start_time: rootGetters['dateFormat'](event.start, 'HH:mm'),
          end_time: rootGetters['dateFormat'](event.end, 'HH:mm'),
          team_id: state.filters.team
        },
        event: event,
      }
      dispatch('functionFullCalendar', request)
    },

    dropSchedule({ commit, state, rootGetters, dispatch }, data) {
      commit('SET_VALIDATE', { error: '', success: '' })
      commit('SET_DATA_CALENDAR', {editable: false, droppable: false})

      if (!confirm(rootGetters['getTranslate']("msgConfirmChange"))) {
        data.revert();
        commit('SET_DATA_CALENDAR', {editable: true, droppable: true})
      } else {
        const { event } = data;
        const request = {
          method: "patch",
          uri: "/data/schedules/" + event.id,
          data: {
            date: rootGetters['dateFormat'](event.start, "YYYY-MM-DD"),
            start_time: rootGetters['dateFormat'](event.start, 'HH:mm'),
            end_time: rootGetters['dateFormat'](event.end, 'HH:mm'),
          }
        }
        dispatch('functionFullCalendar', request)
      }
    },

    resetSelectedItem({ commit }) {
      commit('SET_SELECTED_ITEM', {})
    },

    resizeSchedule({ commit, rootGetters, dispatch }, data) {
      commit('SET_VALIDATE', { error: '', success: '' })
      commit('SET_DATA_CALENDAR', {editable: false, droppable: false})

      if (!confirm(rootGetters['getTranslate']("msgConfirmChange"))) {
        data.revert();
        commit('SET_DATA_CALENDAR', {editable: true, droppable: true})
      } else {
        const { event } = data;
        const request = {
          method: "patch",
          uri: "/data/schedules/" + event.id,
          data: {
            start_time: rootGetters['dateFormat'](event.start, 'HH:mm'),
            end_time: rootGetters['dateFormat'](event.end, 'HH:mm'),
          }
        }
        dispatch('functionFullCalendar', request)
      }
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
      const titleArray = (data.event).title.split("\n");
      const item = {
        id: data.event.id,
        title_not_memo: titleArray[0],
        memo: titleArray[1],
        start_time: rootGetters['dateFormat'](data.event.start, 'HH:mm'),
        end_time: rootGetters['dateFormat'](data.event.end, 'HH:mm'),
      }
      $("#itemDetail").modal("show");
      commit('SET_SELECTED_ITEM', item)
    },

    updateItem({ state, commit }, item) {
      commit('SET_VALIDATE', { error: '', success: '' })
      const uri = "/data/schedules/" + state.selectedItem.id;
      const newItem = {
        memo: item.memo,
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

    deleteItem({ dispatch, state }, data) {
      $("#itemDetail").modal("hide");
      if (confirm(data.msgText)) {
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
