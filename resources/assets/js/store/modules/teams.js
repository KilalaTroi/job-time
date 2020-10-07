export default {
	namespaced: true,

	state: {
		columns: [],
		items: {},
		options: [],
		selectedTeam: {},
		validationErrors: '',
		validationSuccess: ''
	},

	getters: {
		columns: state => state.columns,
		items: state => state.items,
		options: state => state.options,
		selectedTeam: state => state.selectedTeam,
		validationErrors: state => state.validationErrors,
		validationSuccess: state => state.validationSuccess
	},

	mutations: {
		SET_TEAMS: (state, data) => {
			state.items = data
		},

		SET_OPTIONS_TEAMS: (state, data) => {
			state.options = data
		},

		SET_SELECTED_TEAM: (state, data) => {
			state.selectedTeam = data
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
			const uri = '/data/teams?page=' + page

			axios.get(uri).then(response => {
				commit('SET_TEAMS', response.data)
			})
		},

		getOptions({ rootState, commit }, dafaultValue = false) {
			const uri = rootState.queryTeam ? '/data/teams?page=0&' + rootState.queryTeam : '/data/teams?page=0'
			axios.get(uri).then(response => {
				let dataOptions = dafaultValue ? [{id: 0,	text: "Select team"}] : []
				dataOptions = [...dataOptions, ...response.data.map(item => {
					return {
						id: item.id,
						text: item.name
					}
				})]
				commit('SET_OPTIONS_TEAMS', dataOptions)
			})
		},

		deleteItem({ dispatch }, data) {
			if (confirm(data.msgText)) {
				const uri = '/data/teams/' + data.id

				axios.delete(uri)
					.then(res => {
						dispatch('getAll')
					})
					.catch(err => console.log(err))
			}
		},

		getItem({ state, commit, rootGetters }, id) {
			const data = rootGetters['getObjectByID'](state.items.data, id)
			commit('SET_SELECTED_TEAM', data)
		},

		resetSelectedTeam({ commit }) {
			commit('SET_SELECTED_TEAM', { value: '' })
		},

		updateTeam({ commit }, data) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const uri = '/data/teams/' + data.id

			axios
				.patch(uri, data)
				.then(res => {
					commit('SET_VALIDATE', { error: '', success: res.data.message })
				})
				.catch(err => {
					console.log(err);
					if (err.response.status == 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
				});
		},

		createTeam({ commit }, data) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const uri = '/data/teams'

			axios
				.post(uri, data)
				.then(res => {
					commit('SET_SELECTED_TEAM', {})
					commit('SET_VALIDATE', { error: '', success: res.data.message })
				})
				.catch(err => {
					console.log(err);
					if (err.response.status == 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
				});
		},

		resetValidate({ dispatch, commit }) {
			dispatch('getAll')
			commit('SET_VALIDATE', { error: '', success: '' })
		},

		setColumns({ commit, rootGetters }) {
			const columns = [
				{id: 'name', value: rootGetters['getTranslate']('txtName'), width: '', class: ''}
			]

			commit('SET_COLUMNS', columns)
		}
	}
}
