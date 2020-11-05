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
		paged: 1,
		validationErrors: '',
		validationSuccess: ''
	},

	getters: {
        columns: state => state.columns,
        data: state => state.data,
		options: state => state.options,
        selectedItem: state => state.selectedItem,
        filters: state => state.filters,
		paged: state => state.paged,
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

		SET_PAGED: (state, paged) => {
			state.paged = paged
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
			commit('SET_PAGED', page)

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

		setSelectedItem ({ commit }, item) {
			commit('SET_SELECTED_ITEM', item)
		},

		getItem({ state, commit, getters, rootGetters, rootState }, id) {
			const item = getters['getProjectByIssueID'](state.data.data, id)
            if ( item.team ) {
                const arrTeam = item.team.split(',')
                item.team = arrTeam.map((item, index) => {
                    return rootGetters['getObjectByID'](rootState.currentTeamOption, +item)
                })
            }
			commit('SET_SELECTED_ITEM', item)
		},

		archiveItem({ state, dispatch }, data) {
            const uri = '/data/issues/archive/' + data.id + '/' + data.status;
            axios.get(uri).then((res) => {
				if ( (state.paged - 1) * state.data.per_page < (state.data.total - 1) ) {
					dispatch('getAll', state.paged);
				} else {
					let page = state.paged > 1 ? state.paged - 1 : 1;
					dispatch('getAll', page);
				}
            }).catch(err => console.log(err));
		},
		
		deleteItem({ state, dispatch }, issue) {
            if (confirm(issue.msgText)) {
                let uri = '/data/issues/' + issue.id;
                axios.delete(uri).then((res) => {
                    if ( (state.paged - 1) * state.data.per_page < (state.data.total - 1) ) {
						dispatch('getAll', state.paged);
					} else {
						let page = state.paged > 1 ? state.paged - 1 : 1;
						dispatch('getAll', page);
					}
                }).catch(err => console.log(err));
            }
        },

		updateItem({ commit, rootGetters, rootState }, item) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const data = Object.assign({}, item)
			data.team = data.team.map((item, index) => { return item.id }).toString()
			
			if ( data.type_id ) {
				data.dept_id = rootGetters['getObjectByID'](rootState.types.options, +data.type_id).dept_id
			}

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
		
		addProject({ commit, rootState, rootGetters }, item) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const data = Object.assign({}, item)

			if ( data.team ) {
				data.team = data.team.map((item, index) => { return item.id }).toString()
			}
			
			if ( data.type_id ) {
				data.dept_id = rootGetters['getObjectByID'](rootState.types.options, +data.type_id).dept_id
			}

            let uri = '/data/projects';
            axios.post(uri, data)
                .then(res => {
                    commit('SET_SELECTED_ITEM', {type_id: 0})
					commit('SET_VALIDATE', { error: '', success: res.data.message })
                })
                .catch(err => {
                    if (err.response.status === 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
                });
        },

		addIssue({ commit }, item) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const uri = '/data/issues';
			axios.post(uri, item)
				.then(res => {
					commit('SET_SELECTED_ITEM', {project_id: 0, type_id: 0})
					commit('SET_VALIDATE', { error: '', success: res.data.message })
				})
				.catch(err => {
					if (err.response.status === 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
				});
		},

		resetSelectedItem({ commit }) {
			commit('SET_SELECTED_ITEM', {type_id: 0})
		},

		resetValidate({ state, dispatch, commit }) {
			if ( (state.paged - 1) * state.data.per_page < state.data.total ) {
				dispatch('getAll', state.paged);
			} else {
				let page = state.paged > 1 ? state.paged - 1 : 1;
				dispatch('getAll', page);
			}
			commit('SET_VALIDATE', { error: '', success: '' })
		},

		setColumns({ commit, rootGetters }) {
			const columns = [
				{ id: 'department', value: rootGetters['getTranslate']('txtDepartment'), width: '', class: '' },
                { id: 'project', value: rootGetters['getTranslate']('txtName'), width: '', class: '' },
                { id: 'issue', value: rootGetters['getTranslate']('txtIssue'), width: '150', class: '' },
                { id: 'page', value: rootGetters['getTranslate']('txtPage'), width: '60', class: '' },
                { id: 'type', value: rootGetters['getTranslate']('txtType'), width: '', class: '' },
                { id: 'value', value: rootGetters['getTranslate']('txtColor'), width: '110', class: 'text-center' },
				{ id: 'html_team', value: rootGetters['getTranslate']('txtTeam'), width: '', class: 'text-center' },
                { id: 'ct_start_date', value: rootGetters['getTranslate']('lblStartDate'), width: '', class: '' },
                { id: 'ct_end_date', value: rootGetters['getTranslate']('lblEndDate'), width: '', class: '' }
			]

			commit('SET_COLUMNS', columns)
		}
	}
}
