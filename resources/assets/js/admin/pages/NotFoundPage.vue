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
      <sidebar-link to="/table-list">
        <i class="nc-icon nc-notes"></i>
        <p>Table list</p>
      </sidebar-link>
      <sidebar-link to="/typography">
        <i class="nc-icon nc-paper-2"></i>
        <p>Typography</p>
      </sidebar-link>
      <sidebar-link to="/icons">
        <i class="nc-icon nc-atom"></i>
        <p>Icons</p>
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
      <sidebar-link to="/projects">
        <i class="nc-icon nc-bag"></i>
        <p>Projects</p>
      </sidebar-link>
      <sidebar-link to="/schedules">
        <i class="nc-icon nc-watch-time"></i>
        <p>Schedules</p>
      </sidebar-link>
    </side-bar>
    <div class="main-panel">
      <top-navbar></top-navbar>

      <div class="content">
        <div class="container-fluid">
          <div class="card shadow">
            <h2 class="card-header mt-0 text-danger">404 Not Found</h2>

            <div class="card-body">
              <p class="title">Oops! It seems that this page does not exist.</p>
            </div>
          </div>
        </div>
      </div>

      <content-footer></content-footer>
    </div>
  </div>
</template>
<style lang="scss">

</style>
<script>
    import TopNavbar from '../layout/TopNavbar.vue'
    import ContentFooter from '../layout/ContentFooter.vue'
    import MobileMenu from '../layout/MobileMenu.vue'
    export default {
        components: {
            TopNavbar,
            ContentFooter,
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
