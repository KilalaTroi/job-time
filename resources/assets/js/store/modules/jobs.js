export default {
	namespaced: true,

	state: {
		columns: [],
		overlap: true,
		data: {
			jobs: {},
			totaling: {
				data: {},
				total: {
					text: "00:00",
					value: 0
				}
			}
		},
		options: [],
		filters: {
			currentDate: '',
			show: 'showSchedule'
		},
		selectedItemJob: {},
		selectedItem: {},
		restTime: {
			start_time: { 'HH': '', 'mm': '' },
			end_time: { 'HH': '', 'mm': '' }
		},
		validationErrors: '',
		validationSuccess: ''
	},

	getters: {
		columns: state => state.columns,
		data: state => state.data,
		options: state => state.options,
		overlap: state => state.overlap,
		filters: state => state.filters,
		selectedItemJob: state => state.selectedItemJob,
		selectedItem: state => state.selectedItem,
		validationErrors: state => state.validationErrors,
		validationSuccess: state => state.validationSuccess
	},

	mutations: {
		SET_COLUMNS: (state, columns) => {
			state.columns = columns
		},

		SET_DATA: (state, data) => {
			state.data = Object.assign({}, data);
		},

		GET_ALL_JOBS: (state, data) => {
			state.data.time_record = data.logTime;
		},

		SET_OPTIONS: (state, dataOptions) => {
			state.options = dataOptions
		},

		SET_FILTER: (state, data) => {
			state.filters = Object.assign({}, data);
		},

		SET_SELECTED_ITEM_JOB: (state, selectedItemJob) => {
			state.selectedItemJob = Object.assign({}, selectedItemJob)
		},

		SET_SELECTED_ITEM: (state, selectedItem) => {
			state.selectedItem = Object.assign({}, selectedItem)
		},

		SET_OVERLAP: (state, flag) => {
			state.overlap = flag
		},

		SET_VALIDATE: (state, data) => {
			state.validationErrors = data.error
			state.validationSuccess = data.success
		}
	},

	actions: {
		setColumns({ commit, rootGetters }) {
			const columns = {
				'jobs': [
					{ id: "department", value: rootGetters['getTranslate']('txtDepartment'), width: "", class: "" },
					{ id: "project", value: rootGetters['getTranslate']('txtProject'), width: "", class: "" },
					{ id: "issue_year", value: rootGetters['getTranslate']('txtYearOfIssue'), width: "120", class: "text-center year-of-issue" },
					{ id: "issue", value: rootGetters['getTranslate']('txtIssue'), width: "", class: "text-center" },
					{ id: 'phase', value: rootGetters['getTranslate']('txtPhase'), width: '', class: 'text-center' },
					{ id: "time", value: rootGetters['getTranslate']('lblTime'), width: "", class: "text-center" }
				],
				'totaling': [
					{ id: "project", value: rootGetters['getTranslate']('txtProject'), width: "", class: "" },
					{ id: "issue_year", value: rootGetters['getTranslate']('txtYearOfIssue'), width: "100", class: "text-center year-of-issue" },
					{ id: "issue", value: rootGetters['getTranslate']('txtIssue'), width: "", class: "text-center" },
					{ id: 'quantity', value: rootGetters['getTranslate']('txtQuantity'), width: '80', class: 'quantity' },
					{ id: 'note', value: rootGetters['getTranslate']('txtWork'), width: '', class: 'note' },
					{ id: 'phase', value: rootGetters['getTranslate']('txtPhase'), width: '', class: 'text-center' },
					{ id: "start_time_string", value: rootGetters['getTranslate']('lblStartTime'), width: "100", class: "text-center" },
					{ id: "end_time_string", value: rootGetters['getTranslate']('lblEndTime'), width: "100", class: "text-center" },
					{ id: "total", value: rootGetters['getTranslate']('lblTime'), width: "100", class: "text-center" }
				]
			}
			commit('SET_COLUMNS', columns)
		},

		setFilter({ commit, rootGetters }, data) {
			if (!data.currentDate) data.currentDate = rootGetters['dateFormat'](new Date(), 'YYYY-MM-DD')
			commit('SET_FILTER', data)
		},

		getOptions({ commit, rootGetters }) {
			const dataOptions = [
				{ id: "showSchedule", text: rootGetters['getTranslate']("txtShowBySchedule"), },
				{ id: "all", text: rootGetters['getTranslate']("txtShowAll") },
			];
			commit('SET_OPTIONS', dataOptions)
		},

		async getAll({ state, commit, rootGetters }, page = 1) {
			const currentDate = rootGetters['dateFormat'](state.filters.currentDate, 'YYYY-MM-DD')
			const uri = '/data/jobs?page=' + page + '&date=' + currentDate + '&team_id=' + state.filters.team + '&show=' + state.filters.show;

			await axios.get(uri).then(response => {
				commit('SET_DATA', response.data)
			})
		},

		async getItemJob({ state, commit, rootGetters }, id) {
			let item = rootGetters['getObjectByID'](state.data.jobs.data, id);
			item.date = state.filters.currentDate;
			item.start_time = item.end_time = { 'HH': '', 'mm': '' }
			item.exceptLunchBreak = true;
			item.showLunchBreak = false;

			if ( state.filters.team == 2 ) {
				await axios
					.get("/data/finish-page?issue_id=" + item.id)
					.then((res) => {
						if ( res.data.length ) item.quantity = res.data[0];
					})
					.catch(err => console.log(err))
			}

			commit('SET_SELECTED_ITEM_JOB', item)
		},

		getItem({ state, commit, rootGetters }, id) {
			let item = rootGetters['getObjectByID'](state.data.totaling.data, id);
			item.date = state.filters.currentDate;
			commit('SET_SELECTED_ITEM', item)
		},

		resetSelectedItem({ state, commit }) {
			commit('SET_SELECTED_ITEM', state.restTime)
			commit('SET_SELECTED_ITEM_JOB', state.restTime)
		},

		deleteItem({ dispatch }, item) {
			if (confirm(item.msgText)) {
				const uri = '/data/jobs/' + item.id

				axios.delete(uri)
					.then(res => {
						dispatch('getAll')
					})
					.catch(err => console.log(err))
			}
		},

		updateTime({ dispatch, commit }, item) {

			commit('SET_VALIDATE', { error: '', success: '' })

			const uri = '/data/jobs/' + item.id

			axios
				.patch(uri, item)
				.then(res => {
					dispatch('getAll')
					commit('SET_VALIDATE', { error: '', success: res.data.message })
				})
				.catch(err => {
					if (err.response.status == 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
				});

			commit('SET_VALIDATE', { error: '', success: '' })
		},

		resetValidate({ dispatch, commit }) {
			dispatch('getAll')
			commit('SET_VALIDATE', { error: '', success: '' })
		},

		addTime({ commit, state, rootGetters, dispatch }, item) {
			commit('SET_VALIDATE', { error: '', success: '' })
			let uri = "/data/jobs";

			item.team_id = state.filters.team;
			item.date = rootGetters['dateFormat'](item.date, 'YYYY-MM-DD');

			axios
				.post(uri, item)
				.then((res) => {
					dispatch('getAll')
					item.start_time = item.end_time = { 'HH': '', 'mm': '' };
					commit('SET_SELECTED_ITEM_JOB', item)
					commit('SET_VALIDATE', { error: '', success: res.data.message })

				})
				.catch(err => console.log(err))
		},

		checkTimeOverlap({ state, commit }, data) {
			if (data.run) {
				const slItem = data.slItem;
				let flag = false, msg = '', msgSu = '';

				if (slItem.end_time["HH"] && slItem.end_time["mm"] && slItem.start_time["HH"] && slItem.start_time["mm"]) {
					state.data.totaling.data.some(function (value) {
						const aStartTimeSecond = slItem.start_time['HH'] * 1 * 3600 + slItem.start_time['mm'] * 1 * 60;
						const aEndTimeSecond = slItem.end_time['HH'] * 1 * 3600 + slItem.end_time['mm'] * 1 * 60;
						const bStartTimeSecond = value.start_time['HH'] * 1 * 3600 + value.start_time['mm'] * 1 * 60;
						const bEndTimeSecond = value.end_time['HH'] * 1 * 3600 + value.end_time['mm'] * 1 * 60;
						if (2 == data.run) {
							if (slItem.id != value.id) {
								if (!((aEndTimeSecond <= bStartTimeSecond) || (aStartTimeSecond >= bEndTimeSecond))) return flag = true
							}
						} else if (1 == data.run) {
							if (!((aEndTimeSecond <= bStartTimeSecond) || (aStartTimeSecond >= bEndTimeSecond))) return flag = true
						}

					})
					if (flag) msg = [['Overlap time!']];
				} else flag = true;

				if (!msg && state.validationSuccess) msgSu = state.validationSuccess;
				commit('SET_VALIDATE', { error: msg, success: msgSu })
				commit('SET_OVERLAP', flag);
			}
		}
	}
}
