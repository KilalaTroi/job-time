import moment from 'moment'
import { vi, ja } from 'vuejs-datepicker/dist/locale'

export default {
    translateTexts: state => state.translateTexts,
    loginUser: state => state.loginUser,
    reportNotify: state => state.reportNotify,

    getTranslate() {
        return (translateTexts, string) => {
            return translateTexts ? translateTexts.get(string) : ''
        }
    },

    getObjectByID() {
        return (Arr, id) => {
            const result = Arr.filter(item => item.id === id)
            return result.length ? result[0] : {}
        }
    },

    dateFormat() {
        return (date, string) => {
            return moment(date).format(string)
        }
    },

    getLangCode() {
        const dataLang = {
            vi: vi,
            ja: ja
        }

        return (data) => {
            console.log(dataLang[data.current])
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

    }
}