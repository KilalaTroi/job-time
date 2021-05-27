import moment from 'moment'
export default {
  namespaced: true,

  state: {
    columns: [],
    filters: {
      user_id: [],
      start_date: new Date(moment().startOf("month").format("YYYY/MM/DD")),
      end_date: new Date(),
      departments: [],
      projects: [],
      issue: "",
      types: [],
      team: "",
      perfectMatch: false,
    },
    data: {},
    options: {
      projects: [],
      departments: [],
      types: [],
      users: [],
    }
  },

  getters: {
    columns: state => state.columns,
    filters: state => state.filters,
    data: state => state.data,
    options: state => state.options,
  },

  mutations: {
    SET_DATA: (state, data) => {
      state.data = Object.assign({}, data)
    },

    SET_OPTIONS: (state, options) => {
      state.options = {
        projects: options.projects,
        departments: options.departments,
        types: options.types,
        users: options.users
      };
    },

    SET_COLUMNS: (state, columns) => {
      state.columns = columns
    }
  },

  actions: {
    getAll({ state, commit, rootGetters }, page = 1) {
      const uri = '/data/statistic/datatotaling';
      const dataSend = {
        page: page,
        user_id: state.filters.user_id,
        start_date: rootGetters['dateFormat'](state.filters.start_date, 'YYYY-MM-DD'),
        end_date: rootGetters['dateFormat'](state.filters.end_date, 'YYYY-MM-DD'),
        departments: state.filters.departments,
        types: state.filters.types,
        projects: state.filters.projects,
        issue: state.filters.issue,
        team: state.filters.team,
        perfect_match: state.filters.perfectMatch,
      }
      axios.post(uri, dataSend).then(response => {
        commit('SET_DATA', response.data.totaling)
        commit('SET_OPTIONS', response.data)
      })
    },

    exportExcel({ state, rootGetters }) {
      const uri = "/data/export-report-time-user";
      const dataSend = {
        user_id: state.filters.user_id,
        start_date: rootGetters['dateFormat'](state.filters.start_date, 'YYYY-MM-DD'),
        end_date: rootGetters['dateFormat'](state.filters.end_date, 'YYYY-MM-DD'),
        deptSelects: state.filters.departments,
        typeSelects: state.filters.types,
        projectSelects: state.filters.projects,
        issueFilter: state.filters.issue,
        team: state.filters.team,
        perfect_match: state.filters.perfectMatch,
      }
      axios
        .post(uri, dataSend)
        .then((res) => {
          window.open(res.data, "_blank");
        })
        .catch((err) => {
          alert("Error!");
        });
    },

    setColumns({ commit, rootGetters }) {
      const columns = [
        { id: "username", value: rootGetters['getTranslate']('lblUser'), width: "", class: "" },
        { id: "date", value: rootGetters['getTranslate']('lblDate'), width: "120", class: "" },
        { id: "start_time", value: rootGetters['getTranslate']('lblStartTime'), width: "120", class: "" },
        { id: "end_time", value: rootGetters['getTranslate']('lblEndTime'), width: "120", class: "" },
        { id: "total", value: rootGetters['getTranslate']('lblTime'), width: "120", class: "", filter: true },
        { id: "d_name", value: rootGetters['getTranslate']('txtDepartment'), width: "", class: "", filter: true },
        { id: "p_name", value: rootGetters['getTranslate']('txtProject'), width: "", class: "", filter: true },
        { id: "i_year", value: rootGetters['getTranslate']('txtYearOfIssue'), width: "120", filter: true },
        { id: "i_name", value: rootGetters['getTranslate']('txtIssue'), width: "", class: "", filter: true },
        { id: "memo", value: rootGetters['getTranslate']('txtPhase'), width: "", class: "memo", filter: true },
        { id: "note", value: rootGetters['getTranslate']('txtWork'), width: "", class: "note", filter: true },
        { id: "image", value: rootGetters['getTranslate']('txtImage'), width: "120", class: "image text-center" },
        { id: "t_name", value: rootGetters['getTranslate']('txtJobType'), width: "120", class: "", filter: true },
        { id: "t_value", value: rootGetters['getTranslate']('txtColor'), width: "110", class: "text-center", filter: true },
        { id: 'html_team', value: rootGetters['getTranslate']('txtTeam'), width: '', class: 'text-center', filter: true },
      ]

      commit('SET_COLUMNS', columns)
    }
  }
}
