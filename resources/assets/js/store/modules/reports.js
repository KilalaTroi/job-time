import moment from 'moment'
export default {
  namespaced: true,

  state: {
    columns: [],
    filters: {
      type: 0,
      start_date: new Date(moment().subtract(1, "years").startOf("month").format("YYYY/MM/DD")),
      end_date: new Date(moment().add(1, "days").format("YYYY/MM/DD")),
      department: null,
      project: null,
      issue: null,
      issue_year: null,
      team: "",
    },
    data: {},
    options: {
      projects: [],
      departments: [],
      types: [],
      issues: [],
      issues_year: [],
      users: [],
    },

    action: {
      new: false,
      edit: false,
      preview: false,
    },

    selectedItem: {},
    // validationErrors: '',
    // validationSuccess: ''
  },

  getters: {
    columns: state => state.columns,
    filters: state => state.filters,
    data: state => state.data,
    options: state => state.options,
    action: state => state.action,
    selectedItem: state => state.selectedItem,
    // validationErrors: state => state.validationErrors,
    // validationSuccess: state => state.validationSuccess
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
        issues: options.issues,
        issues_year: options.issuesYear,
        users: options.users
      };
    },

    SET_SELECTED_ITEM: (state, selectedItem) => {
			state.selectedItem = Object.assign({}, selectedItem)
		},

    UPDATE_SEEN: () => {},

    SET_COLUMNS: (state, columns) => {
      state.columns = columns
    }
  },

  actions: {
    getAll({ state, commit, rootGetters }, page = 1) {
      const uri = "/data/reports";
      const dataSend = {
        page: page,
        start_date: rootGetters['dateFormat'](state.filters.start_date, 'YYYY-MM-DD'),
        end_date: rootGetters['dateFormat'](state.filters.end_date, 'YYYY-MM-DD'),
        team: state.filters.team,
        type: state.filters.type,
        department: state.filters.department,
        project: state.filters.project,
        issue: state.filters.issue,
        issue_year: state.filters.issue_year,
      }
      axios
        .post(uri, dataSend)
        .then((res) => {
          commit('SET_DATA', res.data.reports)
          commit('SET_OPTIONS', res.data)
        })
        .catch((err) => {
          console.log(err);
          alert("Could not load data");
        });
    },

    getItem({ state, commit, getters}, id) {
			const item = getters['getProjectByIssueID'](state.data.data, id)
			// if (item.team) {
			// 	const arrTeam = item.team.split(',')
			// 	item.team = arrTeam.map((item, index) => {
			// 		return rootGetters['getObjectByID'](rootState.currentTeamOption, +item)
			// 	})
			// }
			commit('SET_SELECTED_ITEM', item)
		},

    resetFilters({ state }) {
      let flagProject = false;
      let flagIssue = false;
      let flagIssueYear = false;

      const checkProject = state.filters.project ? state.filters.project.id : "";
      (state.options.projects).forEach(function (item) {
        if (item.id == checkProject) {
          flagProject = true;
          return;
        }
      });

      const checkIssue = state.filters.issue ? state.filters.issue.id : "";
      (state.options.issues).forEach(function (item) {
        if (item.id == checkIssue) {
          flagIssue = true;
          return;
        }
      });

      const checkIssueYear = state.filters.issue_year ? state.filters.issue_year.id : "";
      (state.options.issues_year).forEach(function (item) {
        if (item.id == checkIssueYear) {
          flagIssueYear = true;
          return;
        }
      });
      if (!flagProject) state.filters.project = null;
      if (!flagIssue) state.filters.issue = null;
      if (!flagIssueYear) state.filters.issue_year = null;
    },

    updateSeen({ dispatch, commit }, item) {
      let uri = "/data/update-seen";
      axios
        .post(uri, {
          reportID: item.id,
        })
        .then((res) => {
          dispatch('getAll');
          dispatch('/updateReportNotify');
          commit(UPDATE_SEEN);
        })
        .catch((err) => {
          console.log(err);
          alert("Could not load data");
        });
    },

    setColumns({ commit, rootGetters }, language) {
      const columns = [
        { id: "type", value: rootGetters['getTranslate']("txtReportType"), width: "120", class: "" },
        { id: "date_time", value: rootGetters['getTranslate']("txtReportDate"), width: "100", class: "" },
        { id: "update_date", value: rootGetters['getTranslate']("txtUpdateDate"), width: "", class: "no-wrap" },
        { id: "dept_name", value: rootGetters['getTranslate']("txtDepartment"), width: "100", class: "" },
        { id: "team_name", value: rootGetters['getTranslate']("txtTeam"), width: "100", class: "" },
        { id: "project_name", value: rootGetters['getTranslate']("txtProject"), width: "", class: "no-wrap" },
        { id: "issue_year", value: rootGetters['getTranslate']("txtYearOfIssue"), width: "100", class: "year-of-issue" },
        { id: "issue_name", value: rootGetters['getTranslate']("txtIssue"), width: "", class: "no-wrap" },
        { id: language == "vi" ? "title" : "title_ja", value: rootGetters['getTranslate']("txtTitle"), width: "", class: "", },
      ]

      commit('SET_COLUMNS', columns)
    }
  }
}
