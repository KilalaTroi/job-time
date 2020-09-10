import moment from 'moment'
import { vi, ja } from 'vuejs-datepicker/dist/locale'

export default {
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
            return dataLang[data.current]
        }
    }
}