export default {
  namespaced: true,

  state: {
    data: {},
    filters: {
      currentStart: '',
      currentEnd: '',
      team: '',
    },
    search: '',
    searchResults: [],
    selectedItem: {},
    validationErrors: '',
    validationSuccess: ''
  },

  getters: {
    data: state => state.data,
    search: state => state.search,
    searchResults: state => state.searchResults,
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
        types: data.types
      }
    },

    SET_FILTER: (state, data) => {
      if (data.view) state.filters.currentStart = data.view.currentStart
      if (data.view) state.filters.currentEnd = data.view.currentEnd
      if (data.team_id) state.filters.team_id = data.team_id
    },

    SET_DATA_SEARCH: (state, data) => {
      state.searchResults = data
    },

    SET_VALIDATE: (state, data) => {
      state.validationErrors = data.error
      state.validationSuccess = data.success
    }
  },

  actions: {
    async getAll({ commit, state, rootGetters, rootState }) {
      const uri = '/data/schedules?startDate=' + rootGetters['dateFormat'](state.filters.currentStart, 'YYYY-MM-DD') + '&endDate=' + rootGetters['dateFormat'](state.filters.currentEnd, 'YYYY-MM-DD') + '&team_id=' + state.filters.team
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

        if (response.data.projects.length) {
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

        commit('SET_DATA', response.data)
      })
    },
    handleMonthChange({ commit, dispatch, state }, data) {
      commit('SET_FILTER', data)
      if (state.filters.currentStart && state.filters.currentEnd && state.filters.team) dispatch('getAll')
    },
    resetValidate({ dispatch, commit }) {
      dispatch('getAll')
      commit('SET_VALIDATE', { error: '', success: '' })
    },
    searchItem(commit, state) {
      // let value = state.search;
      // if (value) {
      //   dataSearchResults = (state.data.projects).filter((item) => {
      //     let title = item.project + " " + item.issue;
      //     return title.toLowerCase().includes(value.toLowerCase());
      //   });
      // } else dataSearchResults = state.data.projects;
      // commit('SET_DATA_SEARCH', dataSearchResults)
    }
  }
}
