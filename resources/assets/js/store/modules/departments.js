export default {
	namespaced: true,

	state: {
		columns: [],
		data: {},
		options: [],
		selectedItem: {},
		validationErrors: '',
		validationSuccess: ''
	},

	getters: {
		columns: state => state.columns,
		data: state => state.data,
		options: state => state.options,
		selectedItem: state => state.selectedItem,
		validationErrors: state => state.validationErrors,
		validationSuccess: state => state.validationSuccess,
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
		async getAll({ commit, dispatch }, page = 1) {
			const uri = '/data/departments?page=' + page

			await axios.get(uri).then(response => {
				commit('SET_DATA', response.data)
				dispatch('getOptions', true)
			})
		},

		async getOptions({ commit, rootGetters }, dafaultValue = false) {
			const uri = '/data/departments?page=0'
			await axios.get(uri).then(response => {
				let dataOptions = dafaultValue ? [{id: 0,	text: rootGetters['getTranslate']('txtSelectOne')}] : []
				dataOptions = [...dataOptions, ...response.data.map(item => {
					return {
						id: item.id,
						text: item.name,
						name_vi: item.name_vi,
						name_ja: item.name_ja
					}
				})]
				commit('SET_OPTIONS', dataOptions)
			})
		},

		deleteItem({ dispatch }, item) {
			if (confirm(item.msgText)) {
				const uri = '/data/departments/' + item.id

				axios.delete(uri)
					.then(res => {
						dispatch('getAll')
					})
					.catch(err => console.log(err))
			}
		},

		getItem({ state, commit, rootGetters }, id) {
			const item = rootGetters['getObjectByID'](state.data.data, id)
			commit('SET_SELECTED_ITEM', item)
		},

		resetSelectedItem({ commit }) {
			commit('SET_SELECTED_ITEM', {})
		},

		updateItem({ commit }, item) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const uri = '/data/departments/' + item.id

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

		createItem({ commit }, item) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const uri = '/data/departments'

			axios
				.post(uri, item)
				.then(res => {
					commit('SET_SELECTED_ITEM', {})
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
