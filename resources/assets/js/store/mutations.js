export default {
    SET_TRANSLATE_TEXTS: (state, translateTexts) => {
        state.translateTexts = translateTexts
    },
   
    SET_LOGIN_USER: (state, loginUser) => {
        state.loginUser = loginUser
    },

    SET_CURRENT_TEAM: (state, currentTeam) => {
        state.currentTeam = currentTeam
    },

    SET_CURRENT_TEAM_OPTION: (state, currentTeamOption) => {
        state.currentTeamOption = currentTeamOption
    },

    SET_QUERY_TEAM: (state, queryTeam) => {
        state.queryTeam = queryTeam
    },

    SET_REPORT_NOTIFY: (state, reportNotify) => {
        state.reportNotify = reportNotify
    },

    UPDATE_REPORT_NOTIFY: (state) => {
        state.reportNotify--;
    },
}