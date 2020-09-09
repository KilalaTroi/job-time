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
		getAllDepartments({ commit }) {
			axios.get('/data/departments').then(response => {
				commit('GET_ALL_DEPARTMENTS', response.data)
			})
		},

		deleteDepartment({ dispatch }, department) {
			if (confirm(department.msgText)) {
				axios.delete('/data/departments/' + department.id)
					.then(res => {
						dispatch('getAllDepartments');
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

		updateDepartment({ commit }, department) {
			commit('SET_VALIDATE', { error: '', success: '' })
			const uri = "/data/departments/" + department.id
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

		createDepartment({ commit }, department) {
			commit('SET_VALIDATE', { error: '', success: '' })
			const uri = "/data/departments"
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

		setColumns({ commit }, _translate) {
			const columns = [
				{ id: "name", value: _translate.get('txtName'), width: "", class: "" },
				{ id: "name_vi", value: _translate.get('txtNameVi'), width: "", class: "" },
				{ id: "name_ja", value: _translate.get('txtNameJa'), width: "", class: "" },
			]

			commit('SET_COLUMNS', columns)
		}
	}
}
