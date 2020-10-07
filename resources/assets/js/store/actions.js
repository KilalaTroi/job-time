export default {
    setTranslateTexts({ commit }, data) {
        commit('SET_TRANSLATE_TEXTS', data)
    },

    setLoginUser({ commit, state, getters }, id) {
        commit('SET_LOGIN_USER', {id: id})
        const uri = '/data/users/' + id
        axios.get(uri).then((response) => {
            commit('SET_LOGIN_USER', response.data.user)

            const arrTeam = response.data.user.team.split(",")
            const teamOptions = state.teams.options
            
            if ( arrTeam.length > 0 ) {
                const currentTeamOption = [...arrTeam.map(item => {
                    return getters['getObjectByID'](teamOptions, +item)
                })]
                commit('SET_CURRENT_TEAM_OPTION', currentTeamOption)
                commit('SET_CURRENT_TEAM', currentTeamOption[0])
            }
        });
    },

    setCurrentLang({ commit }, lang) {
        commit('SET_CURRENT_LANG', lang)
    },

    setCurrentTeam({ state, commit, getters }, data) {
        if ( data ) {
            commit('SET_CURRENT_TEAM', getters['getObjectByID'](state.teams.options, +data))
        }
    },

    setQueryTeam({ state, commit }) {
        const paramTeam = state.currentTeam ? state.currentTeam.text.toLowerCase() : ''
        const paramTeamID = state.currentTeam ? state.currentTeam.id : ''
        const queryTeam = paramTeam && paramTeamID ? 'team=' + paramTeam + '&team_id=' + paramTeamID : ''
        commit('SET_QUERY_TEAM', queryTeam)
    },

    setReportNotify({ state, commit }) { 
        const uri = "/data/notify?user_id=" + state.loginUser.id
        axios.get(uri).then((response) => {
            commit('SET_REPORT_NOTIFY', response.data.notify)
        });
    },

    updateReportNotify({ commit }) { 
        commit('UPDATE_REPORT_NOTIFY')
    }
}