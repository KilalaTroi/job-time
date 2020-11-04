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
import store from './store/index'
import './admin/ml';

// component
Vue.component('pagination', require('laravel-vue-pagination'));

import CKEditor from '@ckeditor/ckeditor5-vue';
window.Vue.use( CKEditor );

// LightBootstrap plugin
import LightBootstrap from './admin/light-bootstrap-main'

window.Vue.use(VueRouter);
window.Vue.use(LightBootstrap);

// Numeral plugin
import vueNumeralFilterInstaller from 'vue-numeral-filter';
Vue.use(vueNumeralFilterInstaller, { locale: 'en-gb' });

import DashboardLayout from './admin/layout/JapanesePlannerLayout'
// GeneralViews
import NotFound from './admin/pages/NotFoundPage'

// Admin pages
import Projects from './admin/pages/Projects';
import Schedules from './admin/pages/Schedules';
import Profile from './admin/pages/Profile';
import Overview from './admin/pages/Overview';
import Totaling from './admin/pages/Totaling';
import Reports from './admin/pages/Reports';
// import Upload from './admin/pages/Upload';

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
                path: 'profile',
                name: 'Profile',
                component: Profile
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
                path: 'reports',
                name: 'Reports',
                component: Reports
            },
            // {
            //     path: 'upload',
            //     name: 'Upload',
            //     component: Upload
            // },
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
    store,
    render: h => h(App),
    router
});