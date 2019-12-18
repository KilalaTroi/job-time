<template>
    <div class="wrapper">
        <side-bar>
            <sidebar-link to="/projects">
                <i class="nc-icon nc-bag"></i>
                <p>Projects</p>
            </sidebar-link>
            <sidebar-link to="/schedules">
                <i class="nc-icon nc-paper-2"></i>
                <p>Schedules</p>
            </sidebar-link>
            <sidebar-link to="/profile" class="d-block d-lg-none">
                <i class="nc-icon nc-circle-09"></i>
                <p>Profile</p>
            </sidebar-link>
            <li class="nav-item d-block d-lg-none">
                <a class="nav-link" href="/logout">
                    <i class="nc-icon nc-button-power"></i> Log out
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