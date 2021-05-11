import moment from 'moment'
export default {
	namespaced: true,

	state: {
		columns: [],
		data: {},
		cdata: {},
		options: {
			users: [],
			teams: [],
			lates: [
				{
					id: "00:01",
					text: 'Late'
				},
				{
					id: "00:15",
					text: 'Later 15 Minutes'
				},
				{
					id: "00:30",
					text: 'Later 30 Minutes'
				}
			]
		},
		selectedItemReason: {},
		validationErrors: '',
		validationSuccess: '',
		filters: {
			start_date: new Date(moment().startOf('month').format("YYYY/MM/DD")),
			end_date: new Date(moment().format("YYYY/MM/DD")),
			user_id: '',
			team_id: '',
			late: "00:15",
		},
		currentStart: new Date(moment().startOf('month').format("YYYY/MM/DD")),
		currentEnd: new Date(moment().startOf('end').format("YYYY/MM/DD")),
	},

	getters: {
		columns: state => state.columns,
		data: state => state.data,
		cdata: state => state.cdata,
		filters: state => state.filters,
		options: state => state.options,
		selectedItemReason: state => state.selectedItemReason,
		currentStart: state => state.currentStart,
		currentEnd: state => state.currentEnd,
		validationErrors: state => state.validationErrors,
		validationSuccess: state => state.validationSuccess,
	},

	mutations: {
		SET_DATA: (state, data) => {
			state.data = Object.assign({}, data)
		},

		SET_CDATA: (state, data) => {
			state.cdata = Object.assign({}, data)
		},

		SET_OPTIONS: (state, options) => {
			if (options.users) state.options.users = [{ id: "", text: "ALL" }].concat(options.users)
			if (options.lates) state.options.lates = options.lates;
		},

		SET_FILTERS: (state, filters) => {
			state.filters = filters
		},


		SET_CURRENT_START: (state, data) => {
			state.currentStart = data
		},

		SET_CURRENT_END: (state, data) => {
			state.currentEnd = data
		},

		SET_SELECTED_ITEM_REASON: (state, selectedItemReason) => {
			state.selectedItemReason = Object.assign({}, selectedItemReason)
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
		getAll({ state, commit, rootGetters, dispatch }, flag = 0) {
			const uri = '/data/checkinout';
			const _flag = flag;
			const dataSend = {
				user_id: state.filters.user_id,
				start_date: rootGetters['dateFormat'](state.filters.start_date, 'YYYY-MM-DD'),
				end_date: rootGetters['dateFormat'](state.filters.end_date, 'YYYY-MM-DD'),
				team_id: state.filters.team_id,
				cstart_date: rootGetters['dateFormat'](state.currentStart, 'YYYY-MM-DD'),
				cend_date: rootGetters['dateFormat'](state.currentEnd, 'YYYY-MM-DD'),
				late: state.filters.late,
				flag: _flag
			}
			axios.post(uri, dataSend).then(response => {
				if (_flag == 0) {
					commit('SET_DATA', response.data)
					setTimeout(function () {
						dispatch('setColorLateEarly');
					}, 300)
				} else if (_flag == 1) {
					commit('SET_CDATA', response.data)
				}
			})
		},

		getOptions({ state, commit, rootGetters }, flag) {
			const uri = '/data/checkinout/get-options?team_id=' + state.filters.team_id;
			axios.get(uri).then(response => {
				commit('SET_OPTIONS', response.data)
			})
		},

		getItemReason({ state, commit, rootGetters }, obj) {
			const result = state.data.data.filter(item => item.userid === obj.id && item.date === obj.date)
			const item = result.length ? result[0] : {};
			commit('SET_SELECTED_ITEM_REASON', item)
		},

		updateReason({ commit }, item) {
			commit('SET_VALIDATE', { error: '', success: '' })
			const data = Object.assign({}, item)
			const uri = '/data/checkinout/reason';
			axios.post(uri, data)
				.then(res => {
					commit('SET_VALIDATE', { error: '', success: res.data.message })
				})
				.catch(err => {
					if (err.response.status === 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
				});
		},


		handleMonthChangeAll({ state, commit, dispatch, rootGetters }, arg) {
			const currentStart = rootGetters['dateFormat'](state.currentStart, 'YYYYMMDD');
			const aCurrentStart = rootGetters['dateFormat'](arg.view.currentStart, 'YYYYMMDD');

			const month = rootGetters['dateFormat'](state.currentEnd, 'MM');
			const aMonth = rootGetters['dateFormat'](arg.view.currentStart, 'MM');

			let endDate = arg.view.currentEnd;
			if(month == aMonth) endDate = state.currentEnd;

			if (currentStart !== aCurrentStart) {
				commit('SET_CURRENT_START', aCurrentStart)
				commit('SET_CURRENT_END', endDate)
				dispatch('getAll', 1);
			}
		},

		setColorLateEarly() {
			$('#table-checkinout tbody tr').removeClass('bg-early').removeClass('bg-all').removeClass('bg-late').find('.cl-reason').removeAttr('title');
			for (const elm of $('#table-checkinout tbody tr')) {
				if ('--' != $(elm).find('.cl-reason').text()) $(elm).find('.cl-reason').attr('title', $(elm).find('.cl-reason').text());
				const late = $(elm).find('.cl-late');
				const early = $(elm).find('.cl-early');
				if (late.text() == '00:00') late.text('--');
				if (early.text() == '00:00') early.text('--');
				if (late.text() != '--') $(elm).removeClass('bg-early').removeClass('bg-all').addClass('bg-late');
				if (early.text() != '--') $(elm).removeClass('bg-late').removeClass('bg-all').addClass('bg-early');
				if (late.text() != '--' && early.text() != '--') $(elm).removeClass('bg-early').removeClass('bg-late').addClass('bg-all');
			}
		},

		resetFilter({ state, commit }, flag = 0) {
			if (flag == 0 || flag == 1) {
				const filters = {
					start_date: new Date(moment().startOf('month').format("YYYY/MM/DD")),
					end_date: new Date(moment().format("YYYY/MM/DD")),
					user_id: '',
					team_id: '',
					late: "00:15",
				};
				commit('SET_FILTERS', filters);
			}
			if (flag == 0 || flag == 2) {
				commit('SET_CURRENT_START', new Date(moment().startOf('month').format("YYYY/MM/DD")));
				commit('SET_CURRENT_END', new Date(moment().add(1, 'months').startOf('month').format("YYYY/MM/DD")));
			}
		},

		resetSelectedItem({ commit }) {
			commit('SET_SELECTED_ITEM_REASON', {})
		},

		resetValidate({ commit, dispatch }) {
			commit('SET_VALIDATE', { error: '', success: '' })
			dispatch('getAll');
			setTimeout(function () {
				dispatch('setColorLateEarly');
			}, 300)
		},

		setColumns({ commit, rootGetters }) {
			const columns = [
				{ id: "fullname", value: rootGetters['getTranslate']('txtName'), width: "160", class: "" },
				{ id: "date", value: rootGetters['getTranslate']('txtDate'), width: "100", class: "text-center" },
				{ id: "dayoweek", value: rootGetters['getTranslate']('txtDay'), width: "60", class: "text-center" },
				{ id: "checkin", value: rootGetters['getTranslate']('txtCheckIn'), width: "100", class: "text-center" },
				{ id: "checkout", value: rootGetters['getTranslate']('txtCheckOut'), width: "120", class: "text-center" },
				{ id: "late", value: rootGetters['getTranslate']('txtLate'), width: "100", class: "text-center" },
				{ id: "early", value: rootGetters['getTranslate']('txtEarly'), width: "100", class: "text-center" },
				{ id: "workingtime", value: rootGetters['getTranslate']('txtWorkingTime'), width: "120", class: "text-center" },
				{ id: "reason", value: rootGetters['getTranslate']('txtAllowed'), width: "", class: "" },
			]

			commit('SET_COLUMNS', columns)
		}
	}
}
