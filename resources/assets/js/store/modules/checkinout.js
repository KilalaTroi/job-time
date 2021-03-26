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
					id: 5,
					text: '00:05'
				},
				{
					id: 15,
					text: '00:15'
				},
				{
					id: 30,
					text: '00:30'
				}
			]
		},
		validationErrors: '',
		validationSuccess: '',
		filters: {
			start_date: new Date(moment().startOf('month').format("YYYY/MM/DD")),
			end_date: new Date(moment().format("YYYY/MM/DD")),
			user_id: [],
			team_id: '',
			late: 5,
		},
		currentStart: '',
		currentEnd: '',
	},

	getters: {
		columns: state => state.columns,
		data: state => state.data,
		cdata: state => state.cdata,
		filters: state => state.filters,
		options: state => state.options,
		currentStart: state => state.currentStart,
		currentEnd: state => state.currentEnd,
		validationErrors: state => state.validationErrors,
		validationSuccess: state => state.validationSuccess,
		recapName() {
			return (str) => {
				let words = str.split(" ");
				let firstName = words[words.length - 1],
					middleName = words[words.length - 2]
						? words[words.length - 2] + " "
						: "";
				return middleName + firstName;
			}
		},
	},

	mutations: {
		SET_DATA: (state, data) => {
			state.data = Object.assign({}, data)
		},

		SET_CDATA: (state, data) => {
			state.cdata = Object.assign({}, data)
		},

		SET_OPTIONS: (state, options) => {
			state.options.users = options.users
		},

		SET_CURRENT_START: (state, data) => {
			state.currentStart = data
		},

		SET_CURRENT_END: (state, data) => {
			state.currentEnd = data
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
		getAll({ state, commit, rootGetters }, flag = 0) {
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
						for (const elm of $('#checkinout tbody tr')) {
							const late = $(elm).find('.cl-late');
							const early = $(elm).find('.cl-early');
							if (late.text() == '00:00') late.text('--');
							if (early.text() == '00:00') early.text('--');

							if (late.text() != '--') $(elm).removeClass('bg-early').removeClass('bg-all').addClass('bg-late');
							if (early.text() != '--') $(elm).removeClass('bg-late').removeClass('bg-all').addClass('bg-early');
							if (late.text() != '--' && early.text() != '--') $(elm).removeClass('bg-early').removeClass('bg-late').addClass('bg-all');
						}
					}, 300)
				} else if (_flag == 1) {
					commit('SET_CDATA', response.data)
				}
			})
		},

		getOptions({ state, commit }) {
			const uri = '/data/checkinout/get-options?team_id=' + state.filters.team_id;
			axios.get(uri).then(response => {
				commit('SET_OPTIONS', response.data)
			})
		},

		getItem({ state, commit, rootGetters }, id) {
			const item = rootGetters['getObjectByID'](state.data.data, id)
			commit('SET_SELECTED_ITEM', item)
		},


		handleMonthChangeAll({ commit, dispatch }, arg) {
			commit('SET_CURRENT_START', arg.view.currentStart)
			commit('SET_CURRENT_END', arg.view.currentEnd)
			dispatch('getAll', 1)
		},


		setColumns({ commit, rootGetters }) {
			const columns = [
				{ id: "fullname", value: "Name", width: "200", class: "" },
				{ id: "date", value: "Date", width: "150", class: "text-center" },
				{ id: "checkin", value: "Check in", width: "110", class: "text-center" },
				{ id: "checkout", value: "Check out", width: "110", class: "text-center" },
				{ id: "late", value: "Late", width: "110", class: "text-center" },
				{ id: "early", value: "Early", width: "110", class: "text-center" },
				{ id: "workingtime", value: "Working time", width: "150", class: "text-center" },
				{ id: "team", value: "Team", width: "110", class: "text-center" },
				{ id: "", value: "", width: "", class: "text-center" },
			]

			commit('SET_COLUMNS', columns)
		}
	}
}
