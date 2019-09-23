<template>
  <div class="wrapper">
    <side-bar>
      <mobile-menu slot="content"></mobile-menu>
      <sidebar-link to="/overview">
        <i class="nc-icon nc-palette"></i>
        <p>Dashboard</p>
      </sidebar-link>
      <sidebar-link to="/user">
        <i class="nc-icon nc-circle-09"></i>
        <p>User Profile</p>
      </sidebar-link>
      <sidebar-link to="/notifications">
        <i class="nc-icon nc-bell-55"></i>
        <p>Notifications</p>
      </sidebar-link>
      <sidebar-link to="/clients">
        <i class="nc-icon nc-money-coins"></i>
        <p>Clients</p>
      </sidebar-link>
      <sidebar-link to="/departments">
        <i class="nc-icon nc-bank"></i>
        <p>Departments</p>
      </sidebar-link>
      <sidebar-link to="/types">
        <i class="nc-icon nc-tag-content"></i>
        <p>Types</p>
      </sidebar-link>
      <sidebar-link to="/projects">
        <i class="nc-icon nc-bag"></i>
        <p>Projects</p>
      </sidebar-link>
      <sidebar-link to="/schedules">
        <i class="nc-icon nc-paper-2"></i>
        <p>Schedules</p>
      </sidebar-link>
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
  import MobileMenu from './MobileMenu.vue'
  export default {
    components: {
      TopNavbar,
      ContentFooter,
      DashboardContent,
      MobileMenu
    },
    methods: {
      toggleSidebar () {
        if (this.$sidebar.showSidebar) {
          this.$sidebar.displaySidebar(false)
        }
      }
    }
  }

</script>
