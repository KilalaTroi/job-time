import moment from 'moment'
import { vi, ja } from 'vuejs-datepicker/dist/locale'

export default {
    translateTexts: state => state.translateTexts,
    loginUser: state => state.loginUser,
    currentLang: state => state.currentLang,
    currentTeam: state => state.currentTeam,
    currentTeamOption: state => state.currentTeamOption,
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

    dateFormat() {
        return (date, string = null) => {
            if (moment(date).format() === 'Invalid date') return '--'
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
                    return '<span>' + getters['getObjectByID'](state.currentTeamOption, +item).text + '</span>'
                }).toString()
            }
        }
    }
}