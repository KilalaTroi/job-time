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
import projects from './modules/projects'
import jobs from './modules/jobs'
import offdays from './modules/offdays'
import schedules from './modules/schedules'
import bookings from './modules/bookings'
import totaling from './modules/totaling'
import reports from './modules/reports'
import totalpage from './modules/totalpage'
import checkinout from './modules/checkinout'
import timetable from './modules/timetable'
import hrprofiles from './modules/hrprofiles'

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
    projects,
    jobs,
    offdays,
    schedules,
    bookings,
    totaling,
    reports,
    totalpage,
    checkinout,
    timetable,
    hrprofiles
  },
})









