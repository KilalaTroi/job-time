export default {
	namespaced: true,

	state: {
		columns: [],
		data: {},
		options: [],
		selectedItem: {
			value: '#000000',
			dept_id: 0,
			checkFinsh: {
				lineroom: false,
				email: false,
			}
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
			state.data = Object.assign({}, data)
		},

		SET_OPTIONS: (state, options) => {
			state.options = options
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
		async getAll({ rootState, rootGetters, commit, dispatch }, page = 1) {
			const uri = '/data/types?page=' + page

			if ( ! rootState.departments.options.length ) await dispatch( 'departments/getOptions', true, {root:true} );

			await axios.get(uri).then(response => {
				if (response.data.data.length) {
					response.data.data = response.data.data.map((item, index) => {
						const dept = rootGetters['getObjectByID'](rootState.departments.options, item.dept_id)
						return Object.assign({}, {
							html_value: '<span class="type-color" style="background: ' + item.value + '"></span>',
							htmldept_vi: dept.name_vi ? dept.name_vi : dept.text,
							htmldept_ja: dept.name_ja ? dept.name_ja : dept.text,
						}, item)
					});
				}

				commit('SET_DATA', response.data)
				dispatch('getOptions', true)
			})
		},

		async getOptions({ commit, rootGetters }, dafaultValue = false) {
			const uri = '/data/types?page=0'

			await axios.get(uri).then(response => {
				let dataOptions = dafaultValue ? [{ id: 0, text: '<div>' + rootGetters['getTranslate']('txtSelectOne') + '<div>' }] : []

				dataOptions = [...dataOptions, ...response.data.map(item => {
					return {
						id: item.id,
						text: '<div><span class="type-color" style="background: ' + item.value + '"></span>' + item.slug + '</div>',
						slug: item.slug,
						value: item.value,
						dept_id: item.dept_id
					}
				})]
				commit('SET_OPTIONS', dataOptions)
			})
		},

		deleteItem({ dispatch }, type) {
			if (confirm(type.msgText)) {
				const uri = '/data/types/' + type.id

				axios.delete(uri)
					.then(res => {
						dispatch('getAll')
					})
					.catch(err => console.log(err))
			}
		},

		getItem({ state, commit, rootGetters }, id) {
			let item = rootGetters['getObjectByID'](state.data.data, id)
			item.checkFinsh = {
				lineroom: item.line_room ? true : false,
				email: item.email ? true : false,
			}
			commit('SET_SELECTED_ITEM', item)
		},

		resetSelectedItem({ commit }) {
			commit('SET_SELECTED_ITEM', { value: '#000000', dept_id: 0, checkFinsh: { lineroom: false, email: false, } })
		},

		updateItem({ commit }, item) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const uri = '/data/types/' + item.id

			axios
				.patch(uri, item)
				.then(res => {
					commit('SET_VALIDATE', { error: '', success: res.data.message })
				})
				.catch(err => {
					console.log(err);
					if (err.response.status == 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
				});
		},

		createItem({ commit }, type) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const uri = '/data/types'

			axios
				.post(uri, type)
				.then(res => {
					commit('SET_SELECTED_ITEM', { value: '#000000', dept_id: 0 , checkFinsh: { lineroom: false, email: false, } })
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
				{ id: 'slug', value: rootGetters['getTranslate']('txtName'), width: '200', class: '' },
				{ id: 'html_value', value: rootGetters['getTranslate']('txtColor'), width: '110', class: 'text-center' },
				{ id: 'slug_vi', value: rootGetters['getTranslate']('txtName') + ' VI', width: '200', class: '' },
				{ id: 'slug_ja', value: rootGetters['getTranslate']('txtName') + ' JA', width: '200', class: '' },
				{ id: 'htmldept_' + rootState.currentLang, value: rootGetters['getTranslate']('txtDepartments'), width: '', class: '' },
				{ id: 'email', value: rootGetters['getTranslate']('txtEmail'), width: '', class: '' },
				{ id: 'line_room', value: rootGetters['getTranslate']('txtLineRoom'), width: '200', class: '' },
			]

			commit('SET_COLUMNS', columns)
		}
	}
}
