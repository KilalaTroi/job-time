<template>
  <div
    class="wrapper"
    :class="loginUser.role ? 'loaded-role wrapper-' + loginUser.role.name : ''"
  >
    <side-bar>
      <sidebar-link to="/projects">
        <!-- <i class="nc-icon nc-bag"></i> -->
        <i class="fa fa-sitemap" aria-hidden="true"></i>
        <p v-text="$ml.with('VueJS').get('sbProjects')" />
      </sidebar-link>
      <sidebar-link to="/schedules">
        <!-- <i class="nc-icon nc-paper-2"></i> -->
        <i class="fa fa-calendar" aria-hidden="true"></i>
        <p v-text="$ml.with('VueJS').get('sbSchedules')" />
      </sidebar-link>
      <sidebar-link to="/finish">
        <i class="fa fa-flag fa-side-menu"></i>
        <p v-text="$ml.with('VueJS').get('txtFinish')" />

        <template v-slot:submenu>
          <ul>
            <sidebar-link to="/uploaded">
              <p v-text="$ml.with('VueJS').get('txtFinishTotaling')" />
            </sidebar-link>
          </ul>
        </template>
      </sidebar-link>
      <sidebar-link to="/jobs">
        <i class="fa fa-list-ul" aria-hidden="true"></i>
        <p v-text="$ml.with('VueJS').get('sbJobs')" />
      </sidebar-link>
      <sidebar-link to="/totaling">
        <i class="fa fa-file-text-o" aria-hidden="true"></i>
        <p v-text="$ml.with('VueJS').get('sbTotaling')" />
      </sidebar-link>
      <sidebar-link to="/overview">
        <i class="fa fa-line-chart" aria-hidden="true"></i>
        <p v-text="$ml.with('VueJS').get('sbStatistics')" />
      </sidebar-link>
      <sidebar-link to="/reports">
        <i class="fa fa-thumb-tack" aria-hidden="true"></i>
        <p v-text="$ml.with('VueJS').get('sbReports')" />
        <span class="report-notify" v-if="reportNotify">{{
          reportNotify
        }}</span>
      </sidebar-link>
      <sidebar-link to="/user">
        <i class="fa fa-users" aria-hidden="true"></i>
        <p v-text="$ml.with('VueJS').get('sbUsers')" />
      </sidebar-link>
      <sidebar-link to="/off-days">
        <i class="fa fa-toggle-off" aria-hidden="true"></i>
        <p v-text="$ml.with('VueJS').get('sbOffDays')" />
      </sidebar-link>
      <sidebar-link to="/checkinout">
        <i class="fa fa-clock-o fa-side-menu"></i>
        <p v-text="$ml.with('VueJS').get('sbAttendance')" />
      </sidebar-link>
      <sidebar-link to="/bookings">
        <i class="fa fa-calendar-o" aria-hidden="true"></i>
        <p v-text="$ml.with('VueJS').get('sbMeetingRoom')" />
      </sidebar-link>
      <sidebar-link to="/hr/profiles">
        <i style="font-size: 22px; position: relative; top: 3px;" class="fa fa-address-card" aria-hidden="true"></i>
        <p>HRM</p>
      </sidebar-link>
      <sidebar-link to="/settings/departments">
        <i style="font-size: 22px" class="fa fa-cog" aria-hidden="true"></i>
        <p v-text="$ml.with('VueJS').get('sbSettings')" />
      </sidebar-link>
      <sidebar-link to="/profile" class="d-block d-lg-none">
        <i class="nc-icon nc-circle-09"></i>
        <p v-text="$ml.with('VueJS').get('mnProfile')" />
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
      <dashboard-content @click="toggleSidebar"> </dashboard-content>
      <content-footer></content-footer>
    </div>
  </div>
</template>
<style lang="scss">
.nav-link {
  position: relative;

  .report-notify {
    position: absolute;
    right: 5px;
    background: #dc3545;
    width: 25px;
    height: 25px;
    line-height: 25px;
    text-align: center;
    border-radius: 50%;
  }
}

// Hide menu items by role
.wrapper:not(.loaded-role) {
  .sidebar-wrapper {
    opacity: 0;
  }
}

// li[class*="menu"] {
//     order: 99;
// }

// .wrapper-admin {
//     .menu {
//         &-jobs {
//             display: none;
//         }
//     }
// }

.wrapper-planner,
.wrapper-japanese_planner,
.wrapper-employee {
  .menu {
    &-user,
    &-settings-teams,
    &-settings-types,
    &-settings-departments,
    &-hr-profiles{
      display: none;
    }

    // &-projects {
    //     order: 1;
    // }

    // &-schedules {
    //     order: 2;
    // }

    // &-jobs {
    //     order: 3;
    // }

    // &-off-days {
    //     order: 4;
    // }
  }
}

.wrapper-employee {
  .menu {
    &-projects {
      display: none;
    }

    // &-jobs {
    //     order: 1;
    // }

    // &-off-days {
    //     order: 3;
    // }
  }
}

.wrapper-japanese_planner {
  .menu {
    &-jobs,
    &-off-days,
    &-checkinout,
    &-mettingroom {
      display: none;
    }
  }
}
</style>
<script>
import TopNavbar from "./TopNavbar.vue";
import ContentFooter from "./ContentFooter.vue";
import DashboardContent from "./Content.vue";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    TopNavbar,
    ContentFooter,
    DashboardContent,
  },

  computed: {
    ...mapGetters({
      reportNotify: "reportNotify",
      loginUser: "loginUser",
    }),
  },

  methods: {
    toggleSidebar() {
      if (this.$sidebar.showSidebar) {
        this.$sidebar.displaySidebar(false);
      }
    },
  },
};
</script>