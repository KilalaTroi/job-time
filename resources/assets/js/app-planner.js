/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('select2');

window.Vue = require('vue');
import VueRouter from 'vue-router';
import App from './admin/App.vue'

// component
Vue.component('pagination', require('laravel-vue-pagination'));

// LightBootstrap plugin
import LightBootstrap from './admin/light-bootstrap-main'

window.Vue.use(VueRouter);
window.Vue.use(LightBootstrap);

import DashboardLayout from './admin/layout/PlannerLayout'
// GeneralViews
import NotFound from './admin/pages/NotFoundPage'

// Admin pages
import Projects from './admin/pages/Projects';
import Schedules from './admin/pages/Schedules';
import Jobs from './admin/pages/Jobs';
import Profile from './admin/pages/Profile';

const routes = [{
        path: '/',
        component: DashboardLayout,
        redirect: '/projects'
    },
    {
        path: '/',
        component: DashboardLayout,
        redirect: '/projects',
        children: [{
                path: 'projects',
                name: 'Projects',
                component: Projects
            },
            {
                path: 'schedules',
                name: 'Schedules',
                component: Schedules
            },
            {
                path: 'jobs',
                name: 'Jobs',
                component: Jobs
            },
            {
                path: 'profile',
                name: 'Profile',
                component: Profile
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

// passport
// Vue.component(
//     'passport-clients',
//     require('./components/passport/Clients.vue')
// );
//
// Vue.component(
//     'passport-authorized-clients',
//     require('./components/passport/AuthorizedClients.vue')
// );
//
// Vue.component(
//     'passport-personal-access-tokens',
//     require('./components/passport/PersonalAccessTokens.vue')
// );
// end passport

const app = new Vue({
    el: '#app',
    render: h => h(App),
    router
});