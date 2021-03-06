export default {
    SET_TRANSLATE_TEXTS: (state, translateTexts) => {
        state.translateTexts = translateTexts
    },
   
    SET_LOGIN_USER: (state, loginUser) => {
        state.loginUser = Object.assign({}, loginUser)
    },

    SET_CURRENT_LANG: (state, lang) => {
        state.currentLang = lang
    },

    SET_CURRENT_TEAM: (state, currentTeam) => {
        state.currentTeam = Object.assign({}, currentTeam)
    },

    SET_CURRENT_TEAM_OPTION: (state, currentTeamOption) => {
        state.currentTeamOption = currentTeamOption
    },

    SET_REPORT_NOTIFY: (state, reportNotify) => {
        state.reportNotify = reportNotify
    },

    UPDATE_REPORT_NOTIFY: (state) => {
        state.reportNotify--;
    },
}