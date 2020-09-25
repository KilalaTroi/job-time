export default {
    namespaced: true,

    state: {
        columns: [],
        items: [],
        roles: [],
        roleOptions: [],
        selectedUser: {},
        validationErrors: '',
        validationSuccess: '',
        teamOptions: [{ id: 1, text: "DTP" }, { id: 2, text: "PATH" }, { id: 3, text: "WEB" }]
    },

    getters: {
        columns: state => state.columns,
        items: state => state.items,
        roles: state => state.roles,
        roleOptions: state => state.roleOptions,
        teamOptions: state => state.teamOptions,
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

        SET_ROLE_OPTIONS: (state, dataOptions) => {
            state.roleOptions = dataOptions
        },

        SET_USERS: (state, users) => {
            state.items = users
        },

        SET_SELECTED_USER: (state, user) => {
            state.selectedUser = user
        },

        SET_VALIDATE: (state, data) => {
            state.validationErrors = data.error
            state.validationSuccess = data.success
        }
    },

    actions: {
        setColumns({ commit, rootState, rootGetters }) {
            const columns = [
                { id: "username", value: rootGetters['getTranslate'](rootState.translateTexts, 'lblUsername'), width: "120", class: "" },
                { id: "r_name", value: rootGetters['getTranslate'](rootState.translateTexts, 'txtRole'), width: "120", class: "" },
                { id: "name", value: rootGetters['getTranslate'](rootState.translateTexts, 'txtName'), width: "120", class: "" },
                { id: "email", value: rootGetters['getTranslate'](rootState.translateTexts, 'txtEmail'), width: "120", class: "" }
            ]

            commit('SET_COLUMNS', columns)
        },

        getAllUser({ commit }) {
            axios.get('/data/users').then(response => {
                commit('GET_ALL_USER', response.data)
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
                    dispatch('getAllUser')
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

        getUserById({ state, commit, rootGetters }, id) {
            const user = rootGetters['getObjectByID'](state.items, id)
            if ( user.team ) {
                const arrTeam = user.team.split(',')
                user.team = arrTeam.map((item, index) => {
                    return rootGetters['getObjectByID'](state.teamOptions, +item)
                })
            }
            commit('SET_SELECTED_USER', user)
        },

        setSelectedUser({ state, commit, rootGetters }, obj) {
            if ( obj.team ) {
                const arrTeam = obj.team.split(',')
                obj.team = arrTeam.map((item, index) => {
                    return rootGetters['getObjectByID'](state.teamOptions, +item)
                })
            }
            commit('SET_SELECTED_USER', obj)
        },

        updateUser({ commit }, user) {
            commit('SET_VALIDATE', {error: '', success: ''})

            const data = Object. assign({}, user)
            data.team = data.team.map((item, index) => { return item.id }).toString()

            const uri = "/data/users/" + user.id

            axios
                .patch(uri, data)
                .then(res => {
                    commit('SET_VALIDATE', { error: '', success: res.data.message })
                })
                .catch(err => {
                    console.log(err);
                    if (err.response.status == 422) {
                        commit('SET_VALIDATE', { error: err.response.data, success: '' })
                    }
                });
        },

        resetValidate({ dispatch, commit }) {
            dispatch('getAllUser')
            commit('SET_VALIDATE', {error: '', success: ''})
        },

        createUser({ commit }, newUser) {
            commit('SET_VALIDATE', {error: '', success: ''})

            const data = Object. assign({}, newUser)
            data.team = data.team.map((item, index) => { return item.id }).toString()

            const uri = "/data/users"

            axios
                .post(uri, data)
                .then(res => {
                    commit('SET_SELECTED_USER', {})
                    commit('SET_VALIDATE', { error: '', success: res.data.message })
                })
                .catch(err => {
                    console.log(err);
                    if (err.response.status == 422) {
                        commit('SET_VALIDATE', { error: err.response.data, success: '' })
                    }
                });
        },

        resetSelectedUser({ commit }) {
            commit('SET_SELECTED_USER', {})
        },
    }
}
