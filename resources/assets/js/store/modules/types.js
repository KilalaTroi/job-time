export default {
	namespaced: true,

	state: {
		columns: [],
		data: {},
		options: [],
		selectedItem: {
			value: '#000000',
			dept_id: 0
		},
		validationErrors: '',
		validationSuccess: ''
	},

	getters: {
		columns: state => state.columns,
		data: state => state.data,
		options: state => state.options,
		selectedItem: state => state.selectedItem,
		validationErrors: state => state.validationErrors,
		validationSuccess: state => state.validationSuccess
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
		async getAll({ rootState, rootGetters, commit, dispatch }, page = 1) {
			const uri = rootState.queryTeam ? '/data/types?page=' + page+ '&' + rootState.queryTeam : '/data/types?page=' + page

			await axios.get(uri).then(response => {
				if (response.data.data.length) {
                    response.data.data = response.data.data.map((item, index) => {
						return Object.assign({}, {
							html_value: '<span class="type-color" style="background: ' + item.value + '"></span>',
							htmldept_vi: rootGetters['getObjectByID'](rootState.departments.data.data, item.dept_id).name_vi,
							htmldept_ja: rootGetters['getObjectByID'](rootState.departments.data.data, item.dept_id).name_ja,
						}, item)
                    });
				}

				commit('SET_DATA', response.data)
				dispatch('getOptions', true)
			})
		},

		async getOptions({ rootState, commit, rootGetters }, dafaultValue = false) {
			const uri = rootState.queryTeam ? '/data/types?page=0&' + rootState.queryTeam : '/data/types?page=0'

			await axios.get(uri).then(response => {
				let dataOptions = dafaultValue ? [{id: 0, text: '<div>' + rootGetters['getTranslate']('txtSelectOne') + '<div>'}] : []

				dataOptions = [...dataOptions, ...response.data.map(item => {
					return {
						id: item.id,
						text: '<div><span class="type-color" style="background: ' + item.value + '"></span>' + item.slug + '</div>',
						slug: item.slug,
						value: item.value,
					}
				})]
				commit('SET_OPTIONS', dataOptions)
			})
		},

		deleteItem({ rootState, dispatch }, type) {
			if (confirm(type.msgText)) {
				const uri = rootState.queryTeam ? '/data/types/' + type.id + '?' + rootState.queryTeam : '/data/types/' + type.id

				axios.delete(uri)
					.then(res => {
						dispatch('getAll')
					})
					.catch(err => console.log(err))
			}
		},

		getItem({ state, commit, rootGetters }, id) {
			const type = rootGetters['getObjectByID'](state.data.data, id)
			commit('SET_SELECTED_ITEM', type)
		},

		resetSelectedItem({ commit }) {
			commit('SET_SELECTED_ITEM', { value: '#000000', dept_id: 0 })
		},

		updateItem({ rootState, commit }, type) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const uri = rootState.queryTeam ? '/data/types/' + type.id + '?' + rootState.queryTeam : '/data/types/' + type.id

			axios
				.patch(uri, type)
				.then(res => {
					commit('SET_VALIDATE', { error: '', success: res.data.message })
				})
				.catch(err => {
					console.log(err);
					if (err.response.status == 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
				});
		},

		createItem({ rootState, commit }, type) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const uri = rootState.queryTeam ? '/data/types' + '?' + rootState.queryTeam : '/data/types'

			axios
				.post(uri, type)
				.then(res => {
					commit('SET_SELECTED_ITEM', { value: '#000000', dept_id: 0 })
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

		setColumns({ commit, rootGetters, rootState }) {
			const columns = [
				{id: 'slug', value: rootGetters['getTranslate']('txtName'), width: '200', class: ''},
				{id: 'html_value', value: rootGetters['getTranslate']('txtColor'), width: '110', class: 'text-center'},
				{id: 'slug_vi', value: rootGetters['getTranslate']('txtName')+ ' VI', width: '200', class: ''},
				{id: 'slug_ja', value: rootGetters['getTranslate']('txtName')+ ' JA', width: '200', class: ''},
				{id: 'htmldept_' + rootState.currentLang, value: rootGetters['getTranslate']('txtDepartments'), width: '', class: ''},
				{id: 'line_room', value: rootGetters['getTranslate']('txtLineRoom'), width: '200', class: ''},
			]

			commit('SET_COLUMNS', columns)
		}
	}
}
