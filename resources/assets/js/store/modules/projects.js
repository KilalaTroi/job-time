export default {
	namespaced: true,

	state: {
		columns: [],
        data: {},
        items: [],
		options: [],
        selectedItem: {},
        filters: {
            keyword: '',
            team: [],
            type_id: -1,
            dept_id: 1,
            showArchive: false
        },
		validationErrors: '',
		validationSuccess: ''
	},

	getters: {
        columns: state => state.columns,
        data: state => state.data,
		items: state => state.items,
		options: state => state.options,
        selectedItem: state => state.selectedItem,
        filters: state => state.filters,
		validationErrors: state => state.validationErrors,
		validationSuccess: state => state.validationSuccess,
	},

	mutations: {
        SET_DATA: (state, data) => {
			state.data = data
        },
        
		SET_ITEMS: (state, items) => {
			state.items = items
		},

		SET_OPTIONS: (state, options) => {
			state.options = options
		},

		SET_SELECTED_ITEM: (state, selectedItem) => {
			state.selectedItem = selectedItem
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
		getAll({ commit, state, rootState, rootGetters }, page = 1) {
            const uri = '/data/projects?page=' + page + '&filters=' + JSON.stringify(state.filters)

			axios.get(uri).then(response => {
                commit('SET_DATA', response.data)

                if (response.data.data.length) {
                    let items = response.data.data.map((item, index) => {
                        let checkArchive = item.status === "archive" ? " <i style='color: #FF4A55;'>(Archived)</i>" : ""
                        let type = rootGetters['getObjectByID'](rootState.types.items, item.type_id)
                        let checkTR = type.slug.includes("_tr") ? " (TR)" : ""
                        let department = rootGetters['getObjectByID'](rootState.departments.items, item.dept_id)

                        return {
                            id: item.id,
                            department: department.text != 'All' ? department.text : '',
                            project: item.p_name + checkTR + checkArchive,
                            issue: item.i_name,
                            issue_id: item.issue_id,
                            page: item.page,
                            status: item.status,
                            type: type.slug,
                            value: type.value,
                            start_date: rootGetters['dateFormat'](item.start_date, 'YYYY/MM/DD'),
                            end_date: rootGetters['dateFormat'](item.end_date, 'YYYY/MM/DD')
                        };
                    });

                    commit('SET_ITEMS', items)
                }
			})
		},

		getOptions({ commit, rootGetters, state }, dafaultValue = false) {
            const uri = '/data/projects?page=0&filters=' + JSON.stringify(state.filters)

			axios.get(uri).then(response => {
				let dataOptions = dafaultValue ? [{id: 0,	text: rootGetters['getTranslate']('txtSelectOne')}] : []
                dataOptions = [...dataOptions, ...response.data]
                
				commit('SET_OPTIONS', dataOptions)
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
				{ id: 'department', value: rootGetters['getTranslate']('txtDepartment'), width: '', class: '' },
                { id: 'project', value: rootGetters['getTranslate']('txtName'), width: '', class: '' },
                { id: 'issue', value: rootGetters['getTranslate']('txtIssue'), width: '110', class: '' },
                { id: 'page', value: rootGetters['getTranslate']('txtPage'), width: '60', class: '' },
                { id: 'type', value: rootGetters['getTranslate']('txtType'), width: '', class: '' },
                { id: 'value', value: rootGetters['getTranslate']('txtColor'), width: '110', class: 'text-center' },
                { id: 'start_date', value: rootGetters['getTranslate']('lblStartDate'), width: '', class: '' },
                { id: 'end_date', value: rootGetters['getTranslate']('lblEndDate'), width: '', class: '' }
			]

			commit('SET_COLUMNS', columns)
		}
	}
}
