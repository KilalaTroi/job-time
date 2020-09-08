import '../../admin/ml'

export default {
    namespaced: true,

    state: {
    },

    getters: {
        hasValue() {
            return (item, column) => {
                return item[column.id.toLowerCase()]
            }
        },
        itemValue() {
            return (item, column) => {
                return item[column.id.toLowerCase()] ? item[column.id.toLowerCase()] : '--'
            }
        },
        checkTypeColor() {
            return (data) => {
                return data.id == 'value'
            }
        },
        setBackground() {
            return (color) => {
                return {
                    background: color
                };
            }
        },
        archiveClass() {
            return (archive) => {
                return archive === null ? "fa fa-archive" : "fa fa-unlock"
            }
        }
    },

    mutations: {
    },

    actions: {
    }
}
