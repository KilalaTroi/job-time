export default {
	namespaced: true,

	state: {
		columns: [],
		items: {},
		options: [],
		selectedDepartment: {},
		validationErrors: '',
		validationSuccess: ''
	},

	getters: {
		columns: state => state.columns,
		items: state => state.items,
		options: state => state.options,
		selectedDepartment: state => state.selectedDepartment,
		validationErrors: state => state.validationErrors,
		validationSuccess: state => state.validationSuccess,
	},

	mutations: {
		SET_DEPARTMENTS: (state, departments) => {
			state.items = departments
		},

		SET_OPTIONS_DEPARTMENTS: (state, departments) => {
			state.options = departments
		},

		SET_SELECTED_DEPARTMENT: (state, department) => {
			state.selectedDepartment = department
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
		getAll({ rootState, commit }, page = 1) {
			const uri = rootState.queryTeam ? '/data/departments?page=' + page + '&' + rootState.queryTeam : '/data/departments?page=' + page

			axios.get(uri).then(response => {
				commit('SET_DEPARTMENTS', response.data)
			})
		},

		getOptions({ rootState, commit }, dafaultValue = false) {
			const uri = rootState.queryTeam ? '/data/departments?page=0&' + rootState.queryTeam : '/data/departments?page=0'
			axios.get(uri).then(response => {
				let dataOptions = dafaultValue ? [{id: 0,	text: "Select departmet"}] : []
				dataOptions = [...dataOptions, ...response.data.map(item => {
					return {
						id: item.id,
						text: item.name
					}
				})]
				commit('SET_OPTIONS_DEPARTMENTS', dataOptions)
			})
		},

		deleteItem({ rootState, dispatch }, department) {
			if (confirm(department.msgText)) {
				const uri = rootState.queryTeam ? '/data/departments/' + department.id + '?' + rootState.queryTeam : '/data/departments/' + department.id

				axios.delete(uri)
					.then(res => {
						dispatch('getAll')
					})
					.catch(err => console.log(err))
			}
		},

		getItem({ state, commit, rootGetters }, id) {
			const department = rootGetters['getObjectByID'](state.items.data, id)
			commit('SET_SELECTED_DEPARTMENT', department)
		},

		resetSelectedDepartment({ commit }) {
			commit('SET_SELECTED_DEPARTMENT', {})
		},

		updateDepartment({ rootState, commit }, department) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const uri = rootState.queryTeam ? '/data/departments/' + department.id + '?' + rootState.queryTeam : '/data/departments/' + department.id

			axios
				.patch(uri, department)
				.then(res => {
					commit('SET_VALIDATE', { error: '', success: res.data.message })
				})
				.catch(err => {
					console.log(err);
					if (err.response.status == 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
				});
		},

		createDepartment({ rootState, commit }, department) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const uri = rootState.queryTeam ? '/data/departments?' + rootState.queryTeam : '/data/departments'

			axios
				.post(uri, department)
				.then(res => {
					commit('SET_SELECTED_DEPARTMENT', {})
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
				{ id: "name", value: rootGetters['getTranslate']('txtName'), width: "", class: "" },
				{ id: "name_vi", value: rootGetters['getTranslate']('txtNameVi'), width: "", class: "" },
				{ id: "name_ja", value: rootGetters['getTranslate']('txtNameJa'), width: "", class: "" },
			]

			commit('SET_COLUMNS', columns)
		}
	}
}
