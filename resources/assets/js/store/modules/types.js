export default {
	namespaced: true,

	state: {
		columns: [],
		items: [],
		selectedType: {
			value: null
		},
		validationErrors: '',
		validationSuccess: ''
	},

	getters: {
		columns: state => state.columns,
		items: state => state.items,
		selectedType: state => state.selectedType,
		validationErrors: state => state.validationErrors,
		validationSuccess: state => state.validationSuccess
	},

	mutations: {
		GET_ALL_TYPES: (state, data) => {
			state.items = data
		},

		SET_TYPES: (state, types) => {
			state.items = types
		},

		SET_SELECTED_TYPE: (state, type) => {
			state.selectedType = type
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
		getAllTypes({ commit }) {
			axios.get('/data/types').then(response => {
				commit('GET_ALL_TYPES', response.data)
			})
		},

		deleteType({ dispatch }, type) {
			if (confirm(type.msgText)) {
				axios.delete('/data/types/' + type.id)
					.then(res => {
						dispatch('getAllTypes')
					})
					.catch(err => console.log(err))
			}
		},

		getTypeById({ state, commit, rootGetters }, id) {
			const type = rootGetters['getObjectByID'](state.items, id)
			commit('SET_SELECTED_TYPE', type)
		},

		resetSelectedType({ commit }) {
			commit('SET_SELECTED_TYPE', {value: null})
		},

		updateType({ commit }, type) {
			commit('SET_VALIDATE', { error: '', success: '' })
			const uri = "/data/types/" + type.id
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

		createType({ commit }, type) {
			commit('SET_VALIDATE', { error: '', success: '' })
			const uri = "/data/types"
			axios
				.post(uri, type)
				.then(res => {
					commit('SET_SELECTED_TYPE', {})
					commit('SET_VALIDATE', { error: '', success: res.data.message })
				})
				.catch(err => {
					console.log(err);
					if (err.response.status == 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
				});
		},

		resetValidate({ dispatch, commit }) {
			dispatch('getAllTypes')
			commit('SET_VALIDATE', { error: '', success: '' })
		},

		setColumns({ commit }, _translate) {
			const langDefault = document.querySelector("meta[name='user-language']").getAttribute('content');
			const columns = [
				{id: 'slug', value: _translate.get('txtSlug'), width: '200', class: ''},
				{id: 'value', value: _translate.get('txtColor'), width: '110', class: 'text-center'},
				{id: 'slug_' + langDefault, value: _translate.get('txtName'), width: '200', class: ''},
				{id: 'description_' + langDefault, value: _translate.get('txtDesc'), width: '', class: ''}
			]

			commit('SET_COLUMNS', columns)
		}
	}
}
