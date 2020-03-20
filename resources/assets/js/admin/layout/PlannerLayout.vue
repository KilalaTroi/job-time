<template>
    <div class="wrapper">
        <side-bar>
            <sidebar-link to="/projects">
                <i class="nc-icon nc-bag"></i>
                <p v-text="$ml.with('VueJS').get('sbProjects')" />
            </sidebar-link>
            <sidebar-link to="/schedules">
                <i class="nc-icon nc-paper-2"></i>
                <p v-text="$ml.with('VueJS').get('sbSchedules')" />
            </sidebar-link>
            <sidebar-link to="/jobs">
                <i class="nc-icon nc-watch-time"></i>
                <p v-text="$ml.with('VueJS').get('sbJobs')" />
            </sidebar-link>
            <sidebar-link to="/off-days"> 
                <i class="nc-icon nc-spaceship"></i>
                <p v-text="$ml.with('VueJS').get('sbOffDays')" />
            </sidebar-link>
            <sidebar-link to="/profile" class="d-block d-lg-none">
                <i class="nc-icon nc-circle-09"></i>
                <p v-text="$ml.with('VueJS').get('mnProfile')" />
            </sidebar-link>
            <sidebar-link to="/overview">
                <i class="nc-icon nc-chart-pie-36"></i>
                <p v-text="$ml.with('VueJS').get('sbStatistics')" />
            </sidebar-link>
            <sidebar-link to="/totaling">
                <i class="nc-icon nc-chart-bar-32"></i>
                <p v-text="$ml.with('VueJS').get('sbTotaling')" />
            </sidebar-link>
            <sidebar-link to="/upload">
                <i class="nc-icon nc-cloud-upload-94"></i>
                <p>Upload</p>
            </sidebar-link>
            <li class="nav-item d-block d-lg-none">
                <a class="nav-link" href="/logout">
                    <i class="nc-icon nc-button-power"></i>
                    <p v-text="$ml.with('VueJS').get('mnLogOut')" />
                </a>
            </li>
        </side-bar>
        <div class="main-panel">
            <top-navbar></top-navbar>
            <dashboard-content @click="toggleSidebar">
            </dashboard-content>
            <content-footer></content-footer>
        </div>
    </div>
</template>
<style lang="scss">
</style>
<script>
import TopNavbar from './TopNavbar.vue'
import ContentFooter from './ContentFooter.vue'
import DashboardContent from './Content.vue'
export default {
    components: {
        TopNavbar,
        ContentFooter,
        DashboardContent
    },
    methods: {
        toggleSidebar() {
            if (this.$sidebar.showSidebar) {
                this.$sidebar.displaySidebar(false)
            }
        }
    }
}
</script>