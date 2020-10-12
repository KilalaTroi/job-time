export default {
    setTranslateTexts({ commit }, data) {
        commit('SET_TRANSLATE_TEXTS', data)
    },

    setLoginUser({ commit, state, getters }, id) {
        commit('SET_LOGIN_USER', {id: id})
        const uri = '/data/users/' + id
        axios.get(uri).then((response) => {
            let _user = response.data.user;
            if (typeof _user.team === 'string' || _user.team instanceof String) {
                const arrTeam = _user.team.split(',')
                _user.team = arrTeam.map((item, index) => {
                    return getters['getObjectByID'](state.teams.options, +item)
                })

                const teamOptions = state.teams.options
            
                if ( arrTeam.length > 0 ) {
                    const currentTeamOption = [...arrTeam.map(item => {
                        return getters['getObjectByID'](teamOptions, +item)
                    })]
                    commit('SET_CURRENT_TEAM_OPTION', currentTeamOption)
                    commit('SET_CURRENT_TEAM', currentTeamOption[0])
                }
            }
            commit('SET_LOGIN_USER', _user)
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