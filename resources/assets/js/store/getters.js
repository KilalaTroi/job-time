import moment from 'moment'
import { vi, ja } from 'vuejs-datepicker/dist/locale'

export default {
    translateTexts: state => state.translateTexts,
    loginUser: state => state.loginUser,
    currentLang: state => state.currentLang,
    currentTeam: state => state.currentTeam,
    currentTeamOption: state => state.currentTeamOption,
    queryTeam: state => state.queryTeam,
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

    dateFormat() {
        return (date, string = null) => {
            if ( string ) return moment(date).format(string)
            return moment(date).format()
        }
    },

    getLangCode() {
        const dataLang = {
            vi: vi,
            ja: ja
        }

        return (data) => {
            return dataLang[data.current]
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

    getTeamText(state, getters) {
        return (team) => {
            if (typeof team === 'string' || team instanceof String) {
                return team.split(',').map((item, index) => { 
                    return '<span>' + getters['getObjectByID'](state.teams.options, +item).text + '</span>'
                }).toString()
            }
        }
    }
}