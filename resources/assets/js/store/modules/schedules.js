export default {
  namespaced: true,

  state: {
    columns: [],
    data: {},
    filters: {
      currentStart: '',
      currentEnd: '',
      team: '',
    },
    options: [],
    selectedItem: {},
    validationErrors: '',
    validationSuccess: ''
  },

  getters: {
    columns: state => state.columns,
    data: state => state.data,
    filters: state => state.filters,
    options: state => state.options,
    selectedItem: state => state.selectedItem,
    validationErrors: state => state.validationErrors,
    validationSuccess: state => state.validationSuccess
  },

  mutations: {
    SET_DATA: (state, data) => {
      state.data = {
        projects : data.projects,
        schedules : data.schedules,
        types : data.types
      }
    },

    SET_FILTER: (state, data) => {
      state.filters = {
        currentStart : data.currentStart,
        currentEnd : data.currentEnd,
        // team : data.currentTeam
      }
    },

    GET_CURRENT_END: (state, data) => {
      state.filters.currentEnd = data
    },
  },

  actions: {
    getAll({ commit, state, rootGetters, rootState }) {
      const uri = '/data/schedules?startDate=' + rootGetters['dateFormat'](state.filters.currentStart, 'YYYY-MM-DD') + '&endDate=' + rootGetters['dateFormat'](state.filters.currentEnd, 'YYYY-MM-DD')
      axios.get(uri).then(response => {
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

        if (response.data.projects.length) {
          response.data.projects = response.data.projects.map((item, index) => {
            const checkTR = item.type.includes("_tr") ? " (TR)" : "";

            return Object.assign({}, item, {
              project: item.p_name + checkTR,
              issue: item.i_name,
              value:  rootGetters['getObjectByID'](rootState.types.options, item.type_id).value,
              start_date: rootGetters['dateFormat'](item.start_date),
              end_date: rootGetters['dateFormat'](item.end_date),
            })
					});
        }

        commit('SET_DATA', response.data)
      })
    },
    async handleMonthChange({ commit, dispatch, rootState, rootGetters }, data) {
      var currentTeam = rootState.teams.currentTeam;
      console.log(currentTeam)
      commit('SET_FILTER', data.view)
      await dispatch('getAll')
    }
  }
}
