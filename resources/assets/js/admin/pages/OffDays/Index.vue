<template>
  <div class="content" id="offdays">
    <div class="container-fluid">
      <div class="all-of-day">
        <all-in-one-off-days class="all" />
      </div>
    </div>
  </div>
</template>
<script>
import AllInOneOffDays from "./AllInOneOffDays";

export default {
  components: {
    AllInOneOffDays,
  }
};
</script>

<style lang="scss">
#offdays {
  .card {
    &:not(.all) {
      .fc-dayGrid-view .fc-body .fc-row {
        height: 70px !important;
      }
    }
    &.all {
      .fc-dayGrid-view .fc-body .fc-row {
        height: 120px !important;
      }
    }
  }
}
</style>