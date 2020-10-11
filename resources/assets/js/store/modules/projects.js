export default {
	namespaced: true,

	state: {
		columns: [],
        data: {},
		options: [],
        selectedItem: {},
        filters: {
            keyword: '',
            team: '',
            type_id: 0,
            dept_id: 0,
            showArchive: false
        },
		validationErrors: '',
		validationSuccess: ''
	},

	getters: {
        columns: state => state.columns,
        data: state => state.data,
		options: state => state.options,
        selectedItem: state => state.selectedItem,
        filters: state => state.filters,
		validationErrors: state => state.validationErrors,
		validationSuccess: state => state.validationSuccess,
		getProjectByIssueID() {
			return (Arr, id) => {
				const result = Arr.filter(item => item.issue_id === id)
				return result.length ? result[0] : {}
			}
		},
	},

	mutations: {
        SET_DATA: (state, data) => {
			state.data = data
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
		async getAll({ commit, state, rootState, rootGetters, dispatch }, page = 1) {
            const uri = '/data/projects?page=' + page + '&filters=' + JSON.stringify(state.filters)

			await axios.get(uri).then(response => {

                if (response.data.data.length) {
					response.data.data = response.data.data.map((item, index) => {
                        let checkArchive = item.status === "archive" ? " <i style='color: #FF4A55;'>(Archived)</i>" : ""
                        let type = rootGetters['getObjectByID'](rootState.types.options, item.type_id)
                        let checkTR = type.slug.includes("_tr") ? " (TR)" : ""
                        let department = rootGetters['getObjectByID'](rootState.departments.options, item.dept_id)

						return Object.assign({}, {
							html_team: rootGetters['getTeamText']('' + item.team),
							department: department.text !== 'All' ? department.text : '',
							project: item.p_name + checkTR + checkArchive,
							issue: item.i_name,
							type: type.slug,
							value: type.value,
							ct_start_date: rootGetters['dateFormat'](item.start_date, 'YYYY/MM/DD'),
							ct_end_date: rootGetters['dateFormat'](item.end_date, 'YYYY/MM/DD')
						}, item)
                    });
                }

				commit('SET_DATA', response.data)
				dispatch('getOptions', true)
			})
		},

		async getOptions({ commit, rootGetters, state }, dafaultValue = false) {
            const uri = '/data/projects?page=0&filters=' + JSON.stringify(state.filters)

			await axios.get(uri).then(response => {
				let dataOptions = dafaultValue ? [{id: 0,	text: rootGetters['getTranslate']('txtSelectOne')}] : []
                dataOptions = [...dataOptions, ...response.data]

				commit('SET_OPTIONS', dataOptions)
			})
		},

		getItem({ state, commit, getters, rootGetters, rootState }, id) {
			const item = getters['getProjectByIssueID'](state.data.data, id)
            if ( item.team ) {
                const arrTeam = item.team.split(',')
                item.team = arrTeam.map((item, index) => {
                    return rootGetters['getObjectByID'](rootState.teams.options, +item)
                })
            }
			commit('SET_SELECTED_ITEM', item)
		},

		updateItem({ commit }, item) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const data = Object.assign({}, item)
            data.team = data.team.map((item, index) => { return item.id }).toString()

            const uri = '/data/projects/' + data.id;
            axios.patch(uri, data).then((res) => {
					commit('SET_VALIDATE', { error: '', success: res.data.message })
                })
                .catch(err => {
                    if (err.response.status === 422) {
						commit('SET_VALIDATE', { error: err.response.data, success: '' })
                    }
                });
		},

		updateIssue({ commit }, item) {
            commit('SET_VALIDATE', { error: '', success: '' })

            // Update issue
            const uri_issue = '/data/issues/' + item.issue_id;
            axios.patch(uri_issue, item).then((res) => {
                commit('SET_VALIDATE', { error: '', success: res.data.message })
            })
            .catch(err => {
                if (err.response.status === 422) {
                    commit('SET_VALIDATE', { error: err.response.data, success: '' })
                }
            });
        },

		addIssue({ commit }, item) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const uri = '/data/issues';
			axios.post(uri, item)
				.then(res => {
					commit('SET_SELECTED_ITEM', {})
					commit('SET_VALIDATE', { error: '', success: res.data.message })
				})
				.catch(err => {
					if (err.response.status === 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
				});
		},

		resetSelectedItem({ commit }) {
			commit('SET_SELECTED_ITEM', {})
		},

		resetValidate({ dispatch, commit }) {
			dispatch('getAll')
			commit('SET_VALIDATE', { error: '', success: '' })
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

		setColumns({ commit, rootGetters }) {
			const columns = [
				{ id: 'department', value: rootGetters['getTranslate']('txtDepartment'), width: '', class: '' },
                { id: 'project', value: rootGetters['getTranslate']('txtName'), width: '', class: '' },
                { id: 'issue', value: rootGetters['getTranslate']('txtIssue'), width: '150', class: '' },
                { id: 'page', value: rootGetters['getTranslate']('txtPage'), width: '60', class: '' },
                { id: 'type', value: rootGetters['getTranslate']('txtType'), width: '', class: '' },
                { id: 'value', value: rootGetters['getTranslate']('txtColor'), width: '110', class: 'text-center' },
				{ id: 'html_team', value: 'Team', width: '', class: 'text-center' },
                { id: 'ct_start_date', value: rootGetters['getTranslate']('lblStartDate'), width: '', class: '' },
                { id: 'ct_end_date', value: rootGetters['getTranslate']('lblEndDate'), width: '', class: '' }
			]

			commit('SET_COLUMNS', columns)
		}
	}
}
