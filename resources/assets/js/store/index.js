import Vuex from 'vuex'
import Vue from 'vue'

import state from './state'
import getters from './getters'
import mutations from './mutations'
import actions from './actions'

import table from './modules/table'
import users from './modules/users'
import teams from './modules/teams'
import departments from './modules/departments'
import types from './modules/types'
import jobs from './modules/jobs'
import offdays from './modules/offdays'

Vue.use(Vuex)

export default new Vuex.Store({
  state,
  getters,
  mutations,
  actions,
  modules: {
    table,
    users,
    teams,
    departments,
    types,
    jobs,
    offdays
  },
})









