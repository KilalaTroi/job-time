export default {
	namespaced: true,

	state: {
		columns: [],
		items: [],
		selectedDepartment: {},
		validationErrors: '',
		validationSuccess: ''
	},

	getters: {
		columns: state => state.columns,
		items: state => state.items,
		selectedDepartment: state => state.selectedDepartment,
		validationErrors: state => state.validationErrors,
		validationSuccess: state => state.validationSuccess,
	},

	mutations: {
		GET_ALL_DEPARTMENTS: (state, data) => {
			state.items = data
		},

		SET_DEPARTMENTS: (state, departments) => {
			state.items = departments
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
		getAllDepartments({ rootState, commit }) {
			const queryTeam = rootState.currentTeam ? rootState.currentTeam.text.toLowerCase() : ''
			const queryTeamID = rootState.currentTeam ? rootState.currentTeam.id : ''
			const uri = queryTeam && queryTeamID ? '/data/departments?team=' + queryTeam + '&team_id=' + queryTeamID : '/data/departments'
			
			axios.get(uri).then(response => {
				commit('GET_ALL_DEPARTMENTS', response.data)
			})
		},

		deleteDepartment({ rootState, dispatch }, department) {
			if (confirm(department.msgText)) {
				const queryTeam = rootState.currentTeam ? rootState.currentTeam.text.toLowerCase() : ''
				const queryTeamID = rootState.currentTeam ? rootState.currentTeam.id : ''
				const uri = queryTeam && queryTeamID ? '/data/departments/' + department.id + '?team=' + queryTeam + '&team_id=' + queryTeamID : '/data/departments/' + department.id

				axios.delete(uri)
					.then(res => {
						dispatch('getAllDepartments')
					})
					.catch(err => console.log(err))
			}
		},

		getDepartmentById({ state, commit, rootGetters }, id) {
			const department = rootGetters['getObjectByID'](state.items, id)
			commit('SET_SELECTED_DEPARTMENT', department)
		},

		resetSelectedDepartment({ commit }) {
			commit('SET_SELECTED_DEPARTMENT', {})
		},

		updateDepartment({ rootState, commit }, department) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const queryTeam = rootState.currentTeam ? rootState.currentTeam.text.toLowerCase() : ''
			const queryTeamID = rootState.currentTeam ? rootState.currentTeam.id : ''
			const uri = queryTeam && queryTeamID ? '/data/departments/' + department.id + '?team=' + queryTeam + '&team_id=' + queryTeamID : '/data/departments/' + department.id
			
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

			const queryTeam = rootState.currentTeam ? rootState.currentTeam.text.toLowerCase() : ''
			const queryTeamID = rootState.currentTeam ? rootState.currentTeam.id : ''
			const uri = queryTeam && queryTeamID ? '/data/departments?team=' + queryTeam + '&team_id=' + queryTeamID : '/data/departments'

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
			dispatch('getAllDepartments')
			commit('SET_VALIDATE', { error: '', success: '' })
		},

		setColumns({ commit, rootState, rootGetters }) {
			const columns = [
				{ id: "name", value: rootGetters['getTranslate'](rootState.translateTexts, 'txtName'), width: "", class: "" },
				{ id: "name_vi", value: rootGetters['getTranslate'](rootState.translateTexts, 'txtNameVi'), width: "", class: "" },
				{ id: "name_ja", value: rootGetters['getTranslate'](rootState.translateTexts, 'txtNameJa'), width: "", class: "" },
			]

			commit('SET_COLUMNS', columns)
		}
	}
}
