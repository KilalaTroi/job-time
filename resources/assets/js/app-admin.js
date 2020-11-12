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

import DashboardLayout from './admin/layout/DashboardLayout'
// GeneralViews
import NotFound from './admin/pages/NotFoundPage'

// Admin pages
import Overview from './admin/pages/Overview';
import UserProfile from './admin/pages/UserProfile/';
import Teams from './admin/pages/Teams';
import TableList from './admin/pages/TableList';
import Typography from './admin/pages/Typography';
import Icons from './admin/pages/Icons';
import Notifications from './admin/pages/Notifications';
import Departments from './admin/pages/Departments';
import Types from './admin/pages/Types';
import Projects from './admin/pages/Projects';
import Schedules from './admin/pages/Schedules';
import Profile from './admin/pages/Profile';
import Totaling from './admin/pages/Totaling';
import Ckeditor from './admin/pages/Ckeditor';
import Reports from './admin/pages/Reports';
import Finish from './admin/pages/Finish';

const routes = [{
        path: '/',
        component: DashboardLayout,
        redirect: '/overview'
    },
    {
        path: '/',
        component: DashboardLayout,
        redirect: '/overview',
        children: [{
                path: 'overview',
                name: 'Overview',
                component: Overview
            },
            {
                path: 'user',
                name: 'User',
                component: UserProfile
            },
            {
                path: 'teams',
                name: 'Teams',
                component: Teams
            },
            {
                path: 'table-list',
                name: 'Table List',
                component: TableList
            },
            {
                path: 'typography',
                name: 'Typography',
                component: Typography
            },
            {
                path: 'icons',
                name: 'Icons',
                component: Icons
            },
            {
                path: 'notifications',
                name: 'Notifications',
                component: Notifications
            },
            {
                path: 'departments',
                name: 'Departments',
                component: Departments
            },
            {
                path: 'types',
                name: 'Types',
                component: Types
            },
            {
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
                path: 'totaling',
                name: 'Totaling',
                component: Totaling
            },
            {
                path: 'ckeditor',
                name: 'Ckeditor',
                component: Ckeditor
            },
            {
                path: 'reports',
                name: 'Reports',
                component: Reports
            },
            {
                path: 'finish',
                name: 'Finish',
                component: Finish
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
    store,
    render: h => h(App),
    router
});