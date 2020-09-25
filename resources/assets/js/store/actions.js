export default {
    setTranslateTexts({ commit }, data) {
        commit('SET_TRANSLATE_TEXTS', data)
    },

    setLoginUser({ commit }, id) {
        const uri = '/data/users/' + id
        axios.get(uri).then((response) => {
            commit('SET_LOGIN_USER', response.data.user)
        });
    },

    setReportNotify({ state, commit }) {
        const uri = "/data/notify?user_id=" + state.loginUser.id
        axios.get(uri).then((response) => {
            commit('SET_REPORT_NOTIFY', response.data.notify)
        });
    }
}