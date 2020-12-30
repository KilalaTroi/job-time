<template>
  <div class="content">
    <div class="container-fluid">
      <off-days v-if="1 != userID" />
      <div class="all-of-day">
        <select-2 :options="currentTeamOption" v-model="team" class="select2" />
        <all-off-days :team="team" class="all" />
      </div>
    </div>
  </div>
</template>
<script>
import OffDays from "./OffDays";
import AllOffDays from "./AllOffDays";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    OffDays,
    AllOffDays,
    Select2,
  },
  data() {
    return {
      team: "",
      userID: document.querySelector("meta[name='user-id']").getAttribute('content')
    };
  },
  computed: {
    ...mapGetters({
      currentTeamOption: "currentTeamOption",
      currentTeam: "currentTeam",
    }),
  },
  async created() {
    this.team = this.currentTeam.id;
  },
};
</script>

<style lang="scss">
.all-of-day {
  position: relative;
  .select2  {
    position: absolute;
    right: 15px;
    top: 15px;
    width: 100px !important;
    z-index: 1;
  }
}

.card{
  &:not(.all){
    .fc-dayGrid-view .fc-body .fc-row{
      height: 70px !important;
    }
  }
  &.all{
    .fc-dayGrid-view .fc-body .fc-row{
      height: 120px !important;
    }
  }
}
</style>