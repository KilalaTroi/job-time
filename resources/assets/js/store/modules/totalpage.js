export default {
  namespaced: true,

  state: {
    filters: {
      date: new Date()
    },
    selectedItem: {},
    options: {
      types: {},
    },
    validationErrors: '',
    validationSuccess: ''
  },

  getters: {
    selectedItem: state => state.selectedItem,
    filters: state => state.filters,
    options: state => state.options,
    validationErrors: state => state.validationErrors,
    validationSuccess: state => state.validationSuccess
  },

  mutations: {
    SET_SELECTED_ITEM: (state, selectedItem) => {
      state.selectedItem = Object.assign({}, selectedItem)
    },
    SET_OPTIONS: (state, options) => {
      state.options.types = options.types
    },
    SET_VALIDATE: (state, data) => {
      state.validationErrors = data.error
      state.validationSuccess = data.success
    },
  },

  actions: {
    getAll({ state, commit }) {
      const uri = '/data/totalpage/';
      axios.get(uri).then(response => {
        response.data.types.forEach(function (element) {
          if (!response.data[element.id]) {
            state.selectedItem[element.id] = {
              page: '',
              team_id: 2,
              type_id: element.id
            }
          }
        })
        commit('SET_OPTIONS', response.data)
      })
    },
    getItem({ state, commit, rootGetters }, date) {
      const uri = '/data/totalpage/' + rootGetters['dateFormat'](date, "YYYYMM");
      axios.get(uri).then(response => {
        response.data.date = rootGetters['dateFormat'](date, "YYYYMM");
        state.options.types.forEach(function (element) {
          if (!response.data[element.id]) {
            response.data[element.id] = {
              page: '',
              team_id: 2,
              type_id: element.id
            }
          }
        })
        commit('SET_SELECTED_ITEM', response.data)
      })
    },

    updateItem({ commit, dispatch }, item) {
      commit('SET_VALIDATE', { error: '', success: '' })
      const uri = '/data/totalpage/' + item.date
      axios
        .patch(uri, item)
        .then(res => {
          dispatch('getItem',item.date);
          commit('SET_VALIDATE', { error: '', success: res.data.message })
        })
        .catch(err => {
          console.log(err);
          if (err.response.status == 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
        });
    },
  }
}
