export default {
    namespaced: true,

    state: {
        columns: [],
        items: [],
        roles: [],
        selectedUser: {},
        validationErrors: '',
        validationSuccess: ''
    },

    getters: {
        columns: state => state.columns,
        items: state => state.items,
        roles: state => state.roles,
        selectedUser: state => state.selectedUser,
        validationErrors: state => state.validationErrors,
        validationSuccess: state => state.validationSuccess,
    },

    mutations: {
        GET_ALL_USER: (state, data) => {
            state.items = data.users
            state.roles = data.roles
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
        },

        SET_COLUMNS: (state, columns) => {
            state.columns = columns
        }
    },

    actions: {
        getAllUser({ commit }) {
            axios.get('/data/users').then(response => {
                commit('GET_ALL_USER', response.data)
            })
        },

        deleteUser({ state, commit }, user) {
            if (confirm(user.msgText)) {
                axios.delete('/data/users/' + user.id)
                .then(res => {
                    const users = state.items.filter(item => item.id !== user.id)
                    commit('SET_USERS', [...users])
                })
                .catch(err => console.log(err))
            }
        },

        archiveUser({ state, commit, rootGetters }, user) {
            const disable_date = !user.disable_date ? rootGetters['dateFormat'](new Date(), 'YYYY-MM-DD') : null
            const uri = '/data/users/archive/' + user.id + '/' + disable_date

            axios.get(uri)
            .then((response) => {
                const users = state.items
                const foundIndex = users.findIndex(x => x.id == user.id )
                users[foundIndex].disable_date = disable_date
                commit('SET_USERS', [...users])
            }).catch(err => console.log(err))
        },

        getUserById({ state, commit, rootGetters }, id) {
            const user = rootGetters['getObjectByID'](state.items, id)
            commit('SET_SELECTED_USER', user)
        },

        resetSelectedUser({ commit }) {
            commit('SET_SELECTED_USER', {})
        },

        updateUser({ state, commit }, user) {
            commit('SET_VALIDATE', {error: '', success: ''})

            const uri = "/data/users/" + user.id;
            axios
                .patch(uri, user)
                .then(res => {
                    const users = state.items
                    const foundIndex = users.findIndex(x => x.id == user.id);
                    users[foundIndex] = user;

                    commit('SET_USERS', [...users])
                    commit('SET_VALIDATE', { error: '', success: res.data.message })
                })
                .catch(err => {
                    console.log(err);
                    if (err.response.status == 422) {
                        commit('SET_VALIDATE', { error: err.response.data, success: '' })
                    }
                });
        },

        resetValidate({ commit }) {
            commit('SET_VALIDATE', {error: '', success: ''})
        },

        setColumns({ commit }, _translate) {
            const columns = [
                { id: "username", value: _translate.get('lblUsername'), width: "120", class: "" },
                { id: "r_name", value: _translate.get('txtRole'), width: "120", class: "" },
                { id: "name", value: _translate.get('txtName'), width: "120", class: "" },
                { id: "email", value: _translate.get('txtEmail'), width: "120", class: "" }
            ]

            commit('SET_COLUMNS', columns)
        }
    }
}
