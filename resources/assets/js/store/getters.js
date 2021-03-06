import moment from 'moment'
import { vi, ja } from 'vuejs-datepicker/dist/locale'

export default {
	translateTexts: state => state.translateTexts,
	loginUser: state => state.loginUser,
	currentLang: state => state.currentLang,
	currentTeam: state => state.currentTeam,
	currentTeamOption: state => state.currentTeamOption,
	currentFullTeamOption: state => state.currentFullTeamOption,
	reportNotify: state => state.reportNotify,

	getTranslate(state) {
		return (string) => {
			return state.translateTexts ? state.translateTexts.get(string) : ''
		}
	},

	getObjectByID() {
		return (Arr, id) => {
			const result = Arr.filter(item => item.id === id)
			return result.length ? result[0] : {}
		}
	},

	getArrObjectByID() {
		return (Arr, id) => {
			const result = Arr.filter(item => item.id === id)
			return result.length ? result : []
		}
	},

	getLogTime() {
		return (Arr, id, date) => {
			const result = Arr.filter(item => item.id === id && item.date === date)
			return result.length ? result : []
		}
	},

	dateFormat() {
		return (date, string = null, text = '--') => {
			if (moment(date).format() === 'Invalid date') return text
			if (string) return moment(date).format(string)
			return moment(date).format()
		}
	},

	customFormatter() {
		return (date) => {
			return moment(date).format('YYYY/MM/DD');
		}
	},

	getLangCode() {
		const dataLang = {
			vi: vi,
			ja: ja
		}

		return (data) => {
			if ('ja' == data.current) return dataLang[data.current]
		}
	},

	getLanguage() {
		return (data) => {
			const language = 'ja' == data.current ? 'ja' : 'en';
			return language;
		}
	},

	setBackground() {
		return (color) => {
			return {
				backgroundColor: color,
				borderColor: color
			}
		}
	},

	disabledStartDates() {
		return (date = false) => {
			if (date) return { to: new Date(date), from: new Date() };
			return { from: new Date() };
		}
	},

	disabledEndDates() {
		return (date = false) => {
			if (date) return { from: new Date(date) };
			return { from: new Date() };
		}
	},

	getTeamText(state, getters) {
		return (team) => {
			if (typeof team === 'string' || team instanceof String) {
				return team.split(',').map((item, index) => {
					return '<span>' + getters['getObjectByID'](state.currentFullTeamOption, +item).text + '</span>'
				}).toString()
			}
		}
	}
}