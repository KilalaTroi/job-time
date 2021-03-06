import moment from 'moment'
export default {
  namespaced: true,

  state: {
    columns: [],
    data: {
      timetable: {},
      stimetable: {}
    },
    selectedItem: {
      time_table_id: 0,
      start_date: new Date(moment('2000-01-01', 'YYYY/MM/DD')),
      end_date: new Date(moment('2099-12-31', 'YYYY/MM/DD')),
    },
    selectedItemt: {
      name: '',
      monfri: {
        check_in: { 'HH': '08', 'mm': '00' },
        check_out: { 'HH': '17', 'mm': '00' },
        check_in_start: { 'HH': '04', 'mm': '00' },
        check_in_end: { 'HH': '10', 'mm': '00' },
        check_out_start: { 'HH': '16', 'mm': '00' },
        check_out_end: { 'HH': '22', 'mm': '00' }
      },
      satsun: {
        check_in: { 'HH': '08', 'mm': '00' },
        check_out: { 'HH': '12', 'mm': '00' },
        check_in_start: { 'HH': '04', 'mm': '00' },
        check_in_end: { 'HH': '10', 'mm': '00' },
        check_out_start: { 'HH': '11', 'mm': '00' },
        check_out_end: { 'HH': '22', 'mm': '00' }
      },
    },
    options: {
      users: [],
      teams: [],
      timetabels: []
    },
    validationErrors: '',
    validationSuccess: '',
    filters: {
      start_date: new Date(moment().subtract(1, "years").format("YYYY/MM/DD")),
      end_date: new Date(moment().format("YYYY/MM/DD")),
      user_id: [],
      team_id: ''
    }
  },

  getters: {
    columns: state => state.columns,
    data: state => state.data,
    selectedItemt: state => state.selectedItemt,
    selectedItem: state => state.selectedItem,
    filters: state => state.filters,
    options: state => state.options,
    validationErrors: state => state.validationErrors,
    validationSuccess: state => state.validationSuccess,
  },

  mutations: {
    SET_DATA: (state, data) => {
      state.data.timetable = Object.assign({}, data)
    },

    SET_DATA_SCHEDULES: (state, data) => {
      state.data.stimetable = Object.assign({}, data)
    },

    SET_OPTIONS: (state, options) => {
      state.options.users = options.users
      state.options.timetabels = options.timetabels
    },

    SET_SELECTED_ITEMT: (state, selectedItemT) => {
      state.selectedItemt = Object.assign({}, selectedItemT)
    },

    SET_SELECTED_ITEM: (state, selectedItem) => {
      state.selectedItem = Object.assign({}, selectedItem)
    },

    SET_VALIDATE: (state, data) => {
      state.validationErrors = data.error
      state.validationSuccess = data.success
    },

    SET_COLUMNS: (state, columns) => {
      state.columns = columns
    }
  },

  actions: {
    getAll({ commit }, page = 1) {
      const uri = '/data/checkinout/get-timetable?page=' + page;

      axios.get(uri).then(response => {
        commit('SET_DATA', response.data)
      })
    },

    getAllSchedules({ commit }, page = 1) {
      const uri = '/data/checkinout/get-schedules-timetable?page=' + page;
      axios.get(uri).then(response => {
        commit('SET_DATA_SCHEDULES', response.data)
      })
    },

    getOptions({ commit, rootGetters }) {
      const uri = '/data/checkinout/get-options'
      axios.get(uri).then(response => {
        const dataOptions = [{ id: 0, text: rootGetters['getTranslate']('txtSelectOne') }];
        response.data.timetabels = [...dataOptions, ...response.data.timetabels]
        commit('SET_OPTIONS', response.data)
      })
    },

    getItemt({ state, commit, rootGetters }, id) {
      const item = rootGetters['getObjectByID'](state.data.timetable.data, id);
      commit('SET_SELECTED_ITEMT', item)
    },

    getItem({ state, commit, rootGetters }, id) {
      const item = rootGetters['getObjectByID'](state.data.stimetable.data, id);
      commit('SET_SELECTED_ITEM', item)
    },

    addTimetable({ commit, dispatch }, item) {
      commit('SET_VALIDATE', { error: '', success: '' })
      const data = Object.assign({}, item)
      const uri = '/data/checkinout/timetable';
      axios.post(uri, data)
        .then(res => {
          commit('SET_VALIDATE', { error: '', success: res.data.message })
          dispatch('getOptions');
        })
        .catch(err => {
          if (err.response.status === 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
        });
    },

    updateTimetable({ commit, dispatch }, item) {
      commit('SET_VALIDATE', { error: '', success: '' })
      const data = Object.assign({}, item)
      const uri = '/data/checkinout/timetable/' + data.id;
      axios.patch(uri, data).then((res) => {
        commit('SET_VALIDATE', { error: '', success: res.data.message })
        dispatch('getOptions');
      })
        .catch(err => {
          if (err.response.status === 422) {
            commit('SET_VALIDATE', { error: err.response.data, success: '' })
          }
        });
    },

    deleteItemt({ dispatch }, data) {
      if (confirm(data.msgText)) {
        const uri = '/data/checkinout/timetable/' + data.id

        axios.delete(uri)
          .then(res => {
            dispatch('getAll');
            dispatch('getOptions');
          })
          .catch(err => {
            if (err.response.status === 422) alert(err.response.data.errors)
          });
      }
    },

    addSchedulesTimetable({ commit }, item) {
      commit('SET_VALIDATE', { error: '', success: '' })
      const data = Object.assign({}, item)
      const uri = '/data/checkinout/schedules-timetable';
      axios.post(uri, data)
        .then(res => {
          commit('SET_VALIDATE', { error: '', success: res.data.message })
        })
        .catch(err => {
          if (err.response.status === 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
        });
    },

    updateSchedulesTimetable({ commit }, item) {
      commit('SET_VALIDATE', { error: '', success: '' })
      const data = Object.assign({}, item)
      const uri = '/data/checkinout/schedules-timetable/' + data.id;
      axios.patch(uri, data).then((res) => {
        commit('SET_VALIDATE', { error: '', success: res.data.message })
      })
        .catch(err => {
          if (err.response.status === 422) {
            commit('SET_VALIDATE', { error: err.response.data, success: '' })
          }
        });
    },

    deleteItem({ dispatch }, data) {
      if (confirm(data.msgText)) {
        const uri = '/data/checkinout/schedules-timetable/' + data.id

        axios.delete(uri)
          .then(res => {
            dispatch('getAllSchedules')
          })
          .catch(err => console.log(err))
      }
    },

    resetSelectedItem({ commit }) {
      commit('SET_SELECTED_ITEMT', {
        name: '',
        monfri: {
          check_in: { 'HH': '08', 'mm': '00' },
          check_out: { 'HH': '17', 'mm': '00' },
          check_in_start: { 'HH': '04', 'mm': '00' },
          check_in_end: { 'HH': '10', 'mm': '00' },
          check_out_start: { 'HH': '16', 'mm': '00' },
          check_out_end: { 'HH': '22', 'mm': '00' }
        },
        satsun: {
          check_in: { 'HH': '08', 'mm': '00' },
          check_out: { 'HH': '12', 'mm': '00' },
          check_in_start: { 'HH': '04', 'mm': '00' },
          check_in_end: { 'HH': '10', 'mm': '00' },
          check_out_start: { 'HH': '11', 'mm': '00' },
          check_out_end: { 'HH': '22', 'mm': '00' }
        },
      })
      commit('SET_SELECTED_ITEM', {
        time_table_id: 0,
        start_date: new Date(moment('2000-01-01', 'YYYY/MM/DD')),
        end_date: new Date(moment('2099-12-31', 'YYYY/MM/DD')),
      })
    },

    resetValidate({ commit, dispatch }) {
      commit('SET_VALIDATE', { error: '', success: '' })
      dispatch('getAll');
      dispatch('getAllSchedules');
    },

    setColumns({ commit, rootGetters }) {
      const columns = {
        'timetable': [
          { id: "name", value: rootGetters['getTranslate']('txtName'), width: "", class: "" },
        ],
        'stimetable': [
          { id: "timetable", value: rootGetters['getTranslate']('txtTimetable'), width: "", class: "" },
          { id: "user", value: rootGetters['getTranslate']('txtUser'), width: "", class: "" },
          { id: "team", value: rootGetters['getTranslate']('txtTeam'), width: "", class: "" },
          { id: "start_date", value: rootGetters['getTranslate']('txtStartDate'), width: "", class: "" },
          { id: "end_date", value: rootGetters['getTranslate']('txtEndDate'), width: "", class: "" },
        ]
      }

      commit('SET_COLUMNS', columns)
    }
  }
}
