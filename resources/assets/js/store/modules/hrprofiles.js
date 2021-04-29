export default {
	namespaced: true,

	state: {
		columns: [],
		data: {},
		selectedItem: {
			avatar: '',
			lavatar: ''
		},
		options: {
			users: [],
			teams: [],
		},
		validationErrors: '',
		validationSuccess: '',
		filters: {
			user_id: '',
			team_id: '',
		},
	},

	getters: {
		columns: state => state.columns,
		data: state => state.data,
		filters: state => state.filters,
		selectedItem: state => state.selectedItem,
		options: state => state.options,
		validationErrors: state => state.validationErrors,
		validationSuccess: state => state.validationSuccess,
	},

	mutations: {
		SET_DATA: (state, data) => {
			state.data = Object.assign({}, data)
		},

		SET_OPTIONS: (state, options) => {
			if (options.users) state.options.users = options.users
		},

		SET_SELECTED_ITEM: (state, selectedItem) => {
			state.selectedItem = Object.assign({ avatar: '', lavatar: '' }, selectedItem)
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
		getAll({ commit }) {
			const uri = '/data/hr/get-profiles';
			const dataSend = {

			}
			axios.post(uri, dataSend).then(response => {
				commit('SET_DATA', response.data)
			})
		},

		getOptions({ state, commit, rootGetters }) {
			const uri = '/data/checkinout/get-options';
			axios.get(uri).then(response => {
				commit('SET_OPTIONS', response.data)
			})
		},

		createItem({ commit }, data) {
			commit('SET_VALIDATE', { error: '', success: '' })
			const uri = '/data/hr/profile';
			let dataSend = new FormData()
			if (data.avatar) dataSend.append('avatar', data.avatar)
			if (data.code) dataSend.append('code', data.code)
			if (data.name) dataSend.append('name', data.name)
			if (data.email) dataSend.append('email', data.email)
			if (data.tel) dataSend.append('tel', data.tel)
			if (data.team_id) dataSend.append('team_id', data.team_id)
			if (data.position) dataSend.append('position', data.position)
			if (data.description) dataSend.append('description', data.description)

			axios.post(uri, dataSend,
				{
					headers: {
						'Content-Type': 'multipart/form-data'
					}
				})
				.then(res => {
					commit('SET_SELECTED_ITEM', {})
					commit('SET_VALIDATE', { error: '', success: res.data.message })
				})
				.catch(err => {
					console.log(err);
					if (err.response.status == 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
				});
		},

		getItem({ state, commit, rootGetters }, id) {
			const item = rootGetters['getObjectByID'](state.data.data, id)
			commit('SET_SELECTED_ITEM', item)
		},

		updateItem({ commit }, data) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const uri = '/data/hr/profile/' + data.id;

			let dataSend = new FormData()
			if (data.avatar) dataSend.append('avatar', data.avatar)
			if (data.code) dataSend.append('code', data.code)
			if (data.name) dataSend.append('name', data.name)
			if (data.email) dataSend.append('email', data.email)
			if (data.tel) dataSend.append('tel', data.tel)
			if (data.team_id) dataSend.append('team_id', data.team_id)
			if (data.position) dataSend.append('position', data.position)
			if (data.description) dataSend.append('description', data.description)

			axios
				.post(uri, dataSend, {
					headers: {
						'Content-Type': 'multipart/form-data'
					}
				})
				.then(res => {
					commit('SET_VALIDATE', { error: '', success: res.data.message })
				})
				.catch(err => {
					console.log(err);
					if (err.response.status == 422) commit('SET_VALIDATE', { error: err.response.data, success: '' })
				});
		},

		deleteItem({ dispatch }, data) {
			if (confirm(data.msgText)) {
				const uri = '/data/hr/profile/' + data.id
				axios.delete(uri)
					.then(res => {
						dispatch('getAll')
					})
					.catch(err => console.log(err))
			}
		},

		handleFileUpload({ state }, event) {
			const file = event.target.files[0];
			state.selectedItem.avatar = file;
			state.selectedItem.lavatar = URL.createObjectURL(file);
		},

		resetValidate({ commit, dispatch }) {
			commit('SET_VALIDATE', { error: '', success: '' })
			dispatch('getAll');
		},

		resetSelectedItem({ commit }) {
			commit('SET_SELECTED_ITEM', {})
		},

		setColumns({ commit, rootGetters }) {
			const columns = [
				{ id: "havatar", value: '', width: "104", class: "" },
				{ id: "code", value: 'Code', width: "130", class: "text-center" },
				{ id: "name", value: rootGetters['getTranslate']('txtName'), width: "200", class: "text-center" },
				{ id: "team", value: rootGetters['getTranslate']('txtTeam'), width: "120", class: "text-center" },
				{ id: "position", value: 'Position', width: "120", class: "text-center" },
				{ id: "email", value: rootGetters['getTranslate']('txtEmail'), width: "200", class: "text-center" },
				{ id: "tel", value: 'tel', width: "150", class: "text-center" },
				{ id: "description", value: 'Description', width: "", class: "text-center" },
			]

			commit('SET_COLUMNS', columns)
		}
	}
}
