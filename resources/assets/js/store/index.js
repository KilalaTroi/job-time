import Vuex from 'vuex'
import Vue from 'vue'

import state from './state'
import getters from './getters'
import mutations from './mutations'
import actions from './actions'

import table from './modules/table'
import users from './modules/users'
import departments from './modules/departments'

Vue.use(Vuex)

export default new Vuex.Store({
  state,
  getters,
  mutations,
  actions,
  modules: {
    table,
    users,
    departments
  },
})









