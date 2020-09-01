/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('select2');

window.Vue = require('vue');
import VueRouter from 'vue-router';
import App from './admin/App.vue';
import './admin/ml';

// component
Vue.component('pagination', require('laravel-vue-pagination'));

// LightBootstrap plugin
import LightBootstrap from './admin/light-bootstrap-main'

window.Vue.use(VueRouter);
window.Vue.use(LightBootstrap);

import DashboardLayout from './admin/layout/EmployeeLayout'
// GeneralViews
import NotFound from './admin/pages/NotFoundPage'

// Admin pages
import Schedules from './admin/pages/SchedulesEmployee';
import Jobs from './admin/pages/Jobs';
import Profile from './admin/pages/Profile';
import OffDays from './admin/pages/OffDays';
import Overview from './admin/pages/Overview';
import Totaling from './admin/pages/Totaling';

const routes = [{
        path: '/',
        component: DashboardLayout,
        redirect: '/jobs'
    },
    {
        path: '/',
        component: DashboardLayout,
        redirect: '/jobs',
        children: [{
                path: 'jobs',
                name: 'Jobs',
                component: Jobs
            },
            {
                path: 'schedules',
                name: 'Schedules',
                component: Schedules
            },
            {
                path: 'profile',
                name: 'Profile',
                component: Profile
            },
            {
                path: 'off-days',
                name: 'OffDays',
                component: OffDays
            },
            {
                path: 'overview',
                name: 'Overview',
                component: Overview
            },
            {
                path: 'totaling',
                name: 'Totaling',
                component: Totaling
            },
            {
                path: '*',
                component: NotFound
            }
        ]
    }
];

const router = new VueRouter({
    routes, // short for routes: routes
    linkActiveClass: 'nav-item active',
    scrollBehavior: (to) => {
        if (to.hash) {
            return { selector: to.hash }
        } else {
            return { x: 0, y: 0 }
        }
    }
});

const app = new Vue({
    el: '#app',
    render: h => h(App),
    router
});