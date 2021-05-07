export default {
	namespaced: true,

	state: {
		columns: [],
		items: [],
		filters: {
			team: {}
		},
		roles: [],
		options: {
			profiles: [],
			teams: [],
		},
		roleOptions: [],
		selectedUser: {},
		validationErrors: '',
		validationSuccess: ''
	},

	getters: {
		columns: state => state.columns,
		items: state => state.items,
		roles: state => state.roles,
		filters: state => state.filters,
		roleOptions: state => state.roleOptions,
		options: state => state.options,
		selectedUser: state => state.selectedUser,
		validationErrors: state => state.validationErrors,
		validationSuccess: state => state.validationSuccess
	},

	mutations: {
		SET_COLUMNS: (state, columns) => {
			state.columns = columns
		},

		GET_ALL_USER: (state, data) => {
			state.items = data.users
			state.roles = data.roles
		},

		SET_OPTIONS: (state, options) => {
			state.options.profiles = options.profiles;
		},

		SET_ROLE_OPTIONS: (state, dataOptions) => {
			state.roleOptions = dataOptions
		},

		SET_USERS: (state, users) => {
			state.items = users
		},

		SET_SELECTED_USER: (state, user) => {
			state.selectedUser = Object.assign({}, user)
		},

		SET_VALIDATE: (state, data) => {
			state.validationErrors = data.error
			state.validationSuccess = data.success
		}
	},

	actions: {
		setColumns({ commit, rootGetters }) {
			const columns = [
				{ id: "username", value: rootGetters['getTranslate']('lblUsername'), width: "120", class: "" },
				{ id: "r_name", value: rootGetters['getTranslate']('txtRole'), width: "120", class: "" },
				{ id: "team", value: rootGetters['getTranslate']('txtTeam'), width: "120", class: "" },
				{ id: "name", value: rootGetters['getTranslate']('txtName'), width: "120", class: "" },
				{ id: "email", value: rootGetters['getTranslate']('txtEmail'), width: "120", class: "" }
			]

			commit('SET_COLUMNS', columns)
		},

		getAllUser({ commit, state }) {
			axios.get('/data/users?team_id=' + state.filters.team.id).then(response => {
				commit('GET_ALL_USER', response.data)
			})
		},

		getOptions({ commit }, obj) {
			let params = '';
			if (obj && obj.profile_old) params = '?profile_id=' + obj.profile_old.id + '&profile_name=' + obj.profile_old.text;
			const uri = '/data/users/get-options' + params;
			axios.get(uri).then(response => {
				commit('SET_OPTIONS', response.data)
				setTimeout(() => {
					commit('SET_SELECTED_USER', obj);
				}, 1)
			})
		},

		getRoleOptions({ state, commit }) {
			let dataOptions = []
			let obj = {
				id: 0,
				text: "Select role"
			}
			dataOptions.push(obj)
			dataOptions = [...dataOptions, ...state.roles.map(item => {
				return {
					id: item.name,
					text: item.name
				}
			})]
			commit('SET_ROLE_OPTIONS', dataOptions)
		},

		deleteUser({ dispatch }, user) {
			if (confirm(user.msgText)) {
				axios.delete('/data/users/' + user.id)
					.then(res => {
						dispatch('getAllUser');
					})
					.catch(err => console.log(err))
			}
		},

		archiveUser({ dispatch, rootGetters }, user) {
			const disable_date = !user.disable_date ? rootGetters['dateFormat'](new Date(), 'YYYY-MM-DD') : null
			const uri = '/data/users/archive/' + user.id + '/' + disable_date

			axios.get(uri)
				.then((response) => {
					dispatch('getAllUser')
				}).catch(err => console.log(err))
		},

		getUserById({ state, commit, dispatch, rootGetters, rootState }, id) {
			const user = rootGetters['getObjectByID'](state.items, id);
			user.team = rootGetters['getObjectByID'](rootState.currentFullTeamOption, +user.team);
			user.profile_old = user.profile;
			dispatch("getOptions", user)
		},

		updateUser({ commit, dispatch }, user) {
			commit('SET_VALIDATE', { error: '', success: '' })

			const data = Object.assign({}, user)
			const uri = "/data/users/" + user.id

			axios
				.patch(uri, data)
				.then(res => {
					data.profile_old = data.profile;
					commit('SET_SELECTED_USER', data);
					commit('SET_VALIDATE', { error: '', success: res.data.message })
				})
				.catch(err => {
					console.log(err);
					if (err.response.status == 422) {
						if (err.response.data && err.response.data.profile) {
							data.profile = data.profile_old;
							dispatch("getOptions", data);
						}
						commit('SET_VALIDATE', { error: err.response.data, success: '' })
					}
				});
		},

		resetValidate({ dispatch, commit }) {
			dispatch("getOptions")
			dispatch('getAllUser')
			commit('SET_VALIDATE', { error: '', success: '' })
		},

		createUser({ dispatch, commit }, newUser) {
			commit('SET_VALIDATE', { error: '', success: '' });

			const data = Object.assign({}, newUser);
			const uri = "/data/users";

			axios
				.post(uri, data)
				.then(res => {
					dispatch('resetSelectedUser');
					commit('SET_VALIDATE', { error: '', success: res.data.message });
				})
				.catch(err => {
					console.log(err);
					if (err.response.status == 422) {
						if (err.response.data && err.response.data.profile){
							data.profile = '';
							dispatch("getOptions");
							commit('SET_SELECTED_USER', data)
						}
						commit('SET_VALIDATE', { error: err.response.data, success: '' });
					}
				});
		},

		resetSelectedUser({ commit, rootState }) {
			commit('SET_SELECTED_USER', {
				r_name: 0,
				language: rootState.currentLang
			})
		},
	}
}
