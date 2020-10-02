export default {
	namespaced: true,

	state: {
		columns: [],
		items: {},
		selectedType: {
			value: ''
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
		getAll({ commit }, page=1) {
			const uri = '/data/types?page=' + page;
			axios.get(uri).then(response => {
				commit('GET_ALL_TYPES', response.data)
			})
		},

		deleteItem({ dispatch }, type) {
			if (confirm(type.msgText)) {
				axios.delete('/data/types/' + type.id)
					.then(res => {
						dispatch('getAll')
					})
					.catch(err => console.log(err))
			}
		},

		getItem({ state, commit, rootGetters }, id) {
			const type = rootGetters['getObjectByID'](state.items.data, id)
			commit('SET_SELECTED_TYPE', type)
		},

		resetSelectedType({ commit }) {
			commit('SET_SELECTED_TYPE', { value: '' })
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
			dispatch('getAll')
			commit('SET_VALIDATE', { error: '', success: '' })
		},

		setColumns({ commit, rootState, rootGetters }) {
			const langDefault = document.querySelector("meta[name='user-language']").getAttribute('content');
			const columns = [
				{ id: 'slug', value: rootGetters['getTranslate'](rootState.translateTexts, 'txtSlug'), width: '200', class: '' },
				{ id: 'htmlValue', value: rootGetters['getTranslate'](rootState.translateTexts, 'txtColor'), width: '110', class: 'text-center' },
				{ id: 'slug_' + langDefault, value: rootGetters['getTranslate'](rootState.translateTexts, 'txtName'), width: '200', class: '' },
				{ id: 'description_' + langDefault, value: rootGetters['getTranslate'](rootState.translateTexts, 'txtDesc'), width: '', class: '' }
			]

			commit('SET_COLUMNS', columns)
		}
	}
}
