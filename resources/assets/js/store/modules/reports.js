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
      user_id: null,
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
      reset: false,
    },

    selectedItem: {
      language: '',
      time: 'HH:mm',
      attendPerson: null,
      attendPersonOther: '',
      title: '',
      title_ja: '',
      content: '',
      content_ja: ''
    },

    validationErrors: '',
    validationSuccess: ''
  },

  getters: {
    columns: state => state.columns,
    filters: state => state.filters,
    data: state => state.data,
    options: state => state.options,
    action: state => state.action,
    selectedItem: state => state.selectedItem,
    validationErrors: state => state.validationErrors,
    validationSuccess: state => state.validationSuccess
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

    SET_FILTERS: (state, filtersItem) => {
      state.filters = Object.assign({}, filtersItem)
    },

    SET_TRANSLATE_CONTENT(state, data) {
      if (state.selectedItem.language == "vi") {
        state.selectedItem.title = data.contentTranslated.title;
        state.selectedItem.content = data.contentTranslated.content;
      } else {
        state.selectedItem.title_ja = data.contentTranslated.title;
        state.selectedItem.content_ja = data.contentTranslated.content;
      }
      state.selectedItem.translatable = 1;
    },

    UPDATE_SEEN: () => { },

    SET_VALIDATE: (state, data) => {
      state.validationErrors = data.error
      state.validationSuccess = data.success
    },

    SET_COLUMNS: (state, columns) => {
      state.columns = columns
    }
  },

  actions: {
    async getAll({ state, commit, rootGetters }, page = 1) {
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

      await axios
        .post(uri, dataSend)
        .then((res) => {
          commit('SET_DATA', res.data.reports)
          commit('SET_OPTIONS', res.data)
        })
        .catch((err) => {
          console.log(err);
          alert("Could not load data");
        });

      return true;
    },

    async handleGetItem({ state, commit, rootGetters, dispatch }, data) {
      let item = rootGetters['getObjectByID'](state.data.data, data.id);
      item.isSeen = data.seen;
      item.attendPerson = null;
      item.time = item.date_time ? rootGetters['dateFormat'](item.date_time, 'HH:mm') : 'HH:mm';
      commit('SET_SELECTED_ITEM', item)

      const filters = {
        start_date: new Date(moment().subtract(1, "years").startOf("month").format("YYYY/MM/DD")),
        end_date: new Date(moment().add(1, "days").format("YYYY/MM/DD")),
        type: item.type ? item.type : null,
        department: item ? { id: item.dept_id, text: item.dept_name } : null,
        project: item ? { id: item.project_id, text: item.project_name } : null,
        issue: item ? { id: item.issue_name_key, text: item.issue_name_text } : null,
        issue_year: item ? { id: item.issue_year_key, text: item.issue_year_text } : null,
        user_id: null,
        team: item.team_id,
      }

      state.action.reset = true;
      await commit('SET_FILTERS', filters)
      await dispatch('getAll', -1);
      state.action.reset = false;
    },

    editReport({ state, dispatch }, data) {
      state.action.edit = true;
      dispatch('handleGetItem', data);
    },

    viewReport({ state, dispatch }, data) {
      state.action.preview = true;
      dispatch('handleGetItem', data);
    },

    sendReport() {
      if (confirm("Send members about this update?")) {
        let uri = "/data/send-report";

        axios
          .post(uri)
          .then((res) => {
            console.log("Email was sent!");
          })
          .catch((err) => {
            console.log(err);
            alert("Could not send email!");
          });
      }
    },

    resetFilters({ state, commit }, flag = '') {
      if ('all' == flag) {
        commit('SET_FILTERS', {
          type: 0,
          start_date: new Date(moment().subtract(1, "years").startOf("month").format("YYYY/MM/DD")),
          end_date: new Date(moment().add(1, "days").format("YYYY/MM/DD")),
          department: null,
          project: null,
          issue: null,
          issue_year: null,
          team: state.filters.team,
        })
      } else if (!flag) {
        let flagProject = false;
        let flagIssue = false;
        let flagIssueYear = false;

        const checkProject = state.filters.project ? state.filters.project.id : "not-found";
        (state.options.projects).forEach(function (item) {
          if (item.id == checkProject) {
            flagProject = true;
            return;
          }
        });

        const checkIssue = state.filters.issue ? state.filters.issue.id : "not-found";
        (state.options.issues).forEach(function (item) {
          if (item.id == checkIssue) {
            flagIssue = true;
            return;
          }
        });

        const checkIssueYear = state.filters.issue_year ? state.filters.issue_year.id : "not-found";
        (state.options.issues_year).forEach(function (item) {
          if (item.id == checkIssueYear) {
            flagIssueYear = true;
            return;
          }
        });

        if (!flagProject) state.filters.project = null;
        if (!flagIssue) state.filters.issue = null;
        if (!flagIssueYear) state.filters.issue_year = null;
      }

      state.action.reset = false;
    },

    translateContent({ state, commit }) {
      const uri = "/data/translate-content";

      axios
        .post(uri, {
          lang: state.selectedItem.language,
          text: {
            title: state.selectedItem.language == "vi" ? state.selectedItem.title : state.selectedItem.title_ja,
            content: state.selectedItem.language == "vi" ? state.selectedItem.content : state.selectedItem.content_ja
          },
        })
        .then((res) => {
          commit('SET_TRANSLATE_CONTENT', res.data)
        })
        .catch((err) => {
          console.log(err);
          alert("Could not translate");
        });
    },

    resetSelectedItem({ commit }) {
      commit('SET_SELECTED_ITEM', {
        language: '',
        time: 'HH:mm',
        attendPerson: null,
        attendPersonOther: '',
        title: '',
        title_ja: '',
        content: '',
        content_ja: ''
      })
    },

    backToList({ state, dispatch }) {
      state.action.new = state.action.preview = state.action.edit = false;
      dispatch('resetSelectedItem');
      dispatch('resetValidate');
      state.action.reset = true;
      dispatch('resetFilters', 'all');
    },

    updateSeen({ state, dispatch, commit, rootState }) {
      const arrSeen = state.selectedItem.seen.split(",");
      if (-1 == arrSeen.indexOf(rootState.loginUser.id)) {
        const uri = "/data/update-seen";
        axios
          .post(uri, {
            reportID: state.selectedItem.id,
          })
          .then((res) => {
            dispatch('updateReportNotify', null, { root: true });
            commit('UPDATE_SEEN');
          })
          .catch((err) => {
            console.log(err);
            alert("Could not load data");
          });
      }
    },

    addNew({ state, dispatch, commit, rootGetters }) {
      commit('SET_VALIDATE', { error: '', success: '' });

      if (!state.selectedItem.title && !state.selectedItem.title_ja) state.validationErrors = [["Please typing the title"], ...state.validationErrors];
      if (!state.selectedItem.date) state.validationErrors = [["Please choosing the date"], ...state.validationErrors];
      if (!state.filters.user_id) state.validationErrors = [["Please choosing the user report"], ...state.validationErrors];
      if ('Meeting' == state.filters.type || 'Notice' == state.filters.type) {
        if ( !state.selectedItem.attendPerson )
        state.validationErrors = [["Please choosing the user attend or destination"], ...state.validationErrors];
      }

      if (!state.selectedItem.content && !state.selectedItem.content_ja) state.validationErrors = [["Please typing the content"], ...state.validationErrors];
      if (!state.validationErrors.length) {
        const uri = "/data/reports-action";

        let dataSend = {
          language: state.selectedItem.language,
          translatable: 0,
          type: state.filters.type,
          author: state.filters.user_id.map((item, index) => { return item.id; }).toString(),
          team_id: state.filters.team,
        }

        if (state.selectedItem.language == "vi") {
          dataSend.title = dataSend.title_ja = state.selectedItem.title;
          dataSend.content = dataSend.content_ja = state.selectedItem.content;
        } else {
          dataSend.title = dataSend.title_ja = state.selectedItem.title_ja;
          dataSend.content = dataSend.content_ja = state.selectedItem.content_ja;
        }

        if ('Meeting' == state.filters.type || 'Notice' == state.filters.type) {
          dataSend.attend_person = state.selectedItem.attendPerson.map((item, index) => { return item.id; }).toString();
          dataSend.attend_other_person = state.selectedItem.attend_other_person;
          dataSend.date_time = rootGetters['dateFormat'](state.selectedItem.date, 'YYYY-MM-DD') + " " + state.selectedItem.time;
        } else {
          dataSend.date_time = rootGetters['dateFormat'](state.selectedItem.date, 'YYYY-MM-DD HH:mm');
          dataSend.projects = dataSend.project_id = state.filters.project ? state.filters.project.id : null;
          dataSend.dept_id = state.filters.department ? state.filters.department.id : null;
          dataSend.issue = state.filters.issue ? state.filters.issue.id : null;
          dataSend.issueYear = dataSend.issue_year = state.filters.issue_year ? state.filters.issue_year.id : null;
        }

        // attach file

        dataSend.attach_file = dataSend.attach_file_ja = state.selectedItem.attach_file;

        axios
          .post(uri, dataSend)
          .then((res) => {
            dispatch('backToList');
          })
          .catch((err) => { if (err.response.status === 422) commit('SET_VALIDATE', { error: err.response.data, success: '' }) });
      }
    },

    updateReport({ state, dispatch, commit, rootGetters }) {
      commit('SET_VALIDATE', { error: '', success: '' });

      if (!state.selectedItem.title && !state.selectedItem.title_ja) state.validationErrors = [["Please typing the title"], ...state.validationErrors];
      if (!state.selectedItem.date) state.validationErrors = [["Please choosing the date"], ...state.validationErrors];
      if (!state.filters.user_id) state.validationErrors = [["Please choosing the user report"], ...state.validationErrors];
      if ('Meeting' == state.filters.type || 'Notice' == state.filters.type) {
        if (!state.selectedItem.attendPerson)
        state.validationErrors = [["Please choosing the user attend or destination"], ...state.validationErrors];
      }

      if (!state.selectedItem.content && !state.selectedItem.content_ja) state.validationErrors = [["Please typing the content"], ...state.validationErrors];
      if (!state.validationErrors.length) {
        let uri = "/data/reports-action/" + state.selectedItem.id;

        let dataSend = {
          translatable: state.selectedItem.translatable,
          type: state.filters.type,
          author: state.filters.user_id.map((item, index) => { return item.id; }).toString(),
          team_id: state.filters.team
        }

        if (state.selectedItem.language == "vi") {
          dataSend.title = state.selectedItem.title;
          dataSend.content = state.selectedItem.content;
        } else {
          dataSend.title_ja = state.selectedItem.title_ja;
          dataSend.content_ja = state.selectedItem.content_ja;
        }

        if ('Meeting' == state.filters.type || 'Notice' == state.filters.type) {
          dataSend.attend_person = state.selectedItem.attendPerson.map((item, index) => { return item.id; }).toString();
          dataSend.attend_other_person = state.selectedItem.attend_other_person;
          dataSend.date_time = rootGetters['dateFormat'](state.selectedItem.date, 'YYYY-MM-DD') + " " + state.selectedItem.time;
        } else {
          dataSend.date_time = rootGetters['dateFormat'](state.selectedItem.date, 'YYYY-MM-DD HH:mm');
          dataSend.projects = dataSend.project_id = state.filters.project ? state.filters.project.id : null;
          dataSend.dept_id = state.filters.department ? state.filters.department.id : null;
          dataSend.issue = state.filters.issue ? state.filters.issue.id : null;
          dataSend.issueYear = dataSend.issue_year = state.filters.issue_year ? state.filters.issue_year.id : null;
        }

        dataSend.attach_file = state.selectedItem.attach_file;
        dataSend.attach_file_ja = state.selectedItem.attach_file_ja;

        axios
          .patch(uri, dataSend)
          .then((res) => {
            dispatch('backToList');
          })
          .catch((err) => {
            console.log(err);
            if (err.response.status == 422) {
              this.errors = err.response.data;
            }
          });
      }
    },

    deleteReport({ state, rootGetters, dispatch }, item) {
      if (confirm(rootGetters['getTranslate']('msgConfirmDelete'))) {
        const uri = "/data/reports-action/" + item.id;

        axios
          .delete(uri)
          .then((res) => {
            state.action.new = state.action.preview = state.action.edit = false;
            dispatch('backToList');
          })
          .catch((err) => console.log(err));
      }
    },

    exportPDF({ state }) {
      const uri = "/pdf/report";

      const data = {
        is_metting: 'Meeting' == state.filters.type || 'Notice' == state.filters.type ? 1 : 0,
        title: state.selectedItem.language == "vi" ? state.selectedItem.title : state.selectedItem.title_ja,
        date_time: state.selectedItem.date_time,
        reporter: state.selectedItem.reporter,
        attend_person: 'Meeting' == state.filters.type || 'Notice' == state.filters.type ? state.selectedItem.attend_person : "",
        attend_other_person: state.selectedItem.attend_other_person,
        dept_name: state.filters.department.text,
        project_name: state.filters.project.text,
        issue_name: state.filters.issue.text,
        issue_year: state.filters.issue_year.text,
        content: state.selectedItem.language == "vi" ? state.selectedItem.content : state.selectedItem.content_ja
      };

      axios
        .post(uri, {
          data: data,
        })
        .then((res) => {
          window.open(res.data.file_name, "_blank");
        })
        .catch((err) => {
          console.log(err);
          alert("Could not load data");
        });
    },

    resetValidate({ commit }) {
      commit('SET_VALIDATE', { error: '', success: '' })
    },

    setColumns({ commit, rootGetters }, language) {
      const columns = [
        { id: "type", value: rootGetters['getTranslate']("txtType"), width: "120", class: "" },
        { id: "date_time", value: rootGetters['getTranslate']("txtDateTime"), width: "100", class: "" },
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
