export default {
	setTranslateTexts({ commit }, data) {
		commit('SET_TRANSLATE_TEXTS', data)
	},

	setLoginUser({ commit, state, getters }, id) {
		commit('SET_LOGIN_USER', { id: id })
		const uri = '/data/users/' + id
		axios.get(uri).then((response) => {
			let _user = response.data.user;
			_user.role = response.data.role;
			_user.team = getters['getObjectByID'](state.currentFullTeamOption, +_user.team)
			commit('SET_LOGIN_USER', _user)
		});
	},

	setCurrentLang({ commit }, lang) {
		commit('SET_CURRENT_LANG', lang)
	},

	setCurrentTeam({ state, commit, getters, dispatch }, data) {
		if (data) {
			const team = getters['getObjectByID'](state.currentTeamOption, +data);
			if ("undefined" != (typeof(team.id))) {
				localStorage.setItem('team', team.id)
				commit('SET_CURRENT_TEAM', team)
				dispatch('setReportNotify', team.id)
			}
		}
	},

	setReportNotify({ commit }, teamID) {
		const uri = "/data/notify?team_id=" + teamID
		axios.get(uri).then((response) => {
			commit('SET_REPORT_NOTIFY', response.data.notify)
		});
	},

	updateReportNotify({ commit }) {
		commit('UPDATE_REPORT_NOTIFY')
	}
}