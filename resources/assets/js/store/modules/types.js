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
		getAllTypes({ rootState, commit }, page=1) {
			const uri = rootState.queryTeam ? '/data/types?page=' + page+ '&' + rootState.queryTeam : '/data/types?page=' + page

			axios.get(uri).then(response => {
				commit('GET_ALL_TYPES', response.data)
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
			const type = rootGetters['getObjectByID'](state.items.data, id)
			commit('SET_SELECTED_TYPE', type)
		},

		resetSelectedType({ commit }) {
			commit('SET_SELECTED_TYPE', { value: '' })
		},

		updateType({ rootState, commit }, type) {
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

		createType({ rootState, commit }, type) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const uri = rootState.queryTeam ? '/data/types' + '?' + rootState.queryTeam : '/data/types'

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

		setColumns({ commit, rootGetters }) {
			const langDefault = document.querySelector("meta[name='user-language']").getAttribute('content');
			const columns = [
				{id: 'slug', value: rootGetters['getTranslate']('txtSlug'), width: '200', class: ''},
				{id: 'htmlValue', value: rootGetters['getTranslate']('txtColor'), width: '110', class: 'text-center'},
				{id: 'slug_' + langDefault, value: rootGetters['getTranslate']('txtName'), width: '200', class: ''},
				{id: 'description_' + langDefault, value: rootGetters['getTranslate']('txtDesc'), width: '', class: ''}
			]

			commit('SET_COLUMNS', columns)
		}
	}
}
