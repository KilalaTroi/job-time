<template>
  <modal id="itemCreate" v-on:reset-validation="resetValidation">
    <template slot="title">Create Schedules Timetable</template>
    <form>
      <div class="form-group">
        <label class="">Timetable</label>
        <div>
          <select-2
            :options="options.timetabels"
            v-model="selectedItem.time_table_id"
            class="select2"
          />
        </div>
      </div>
      <div class="form-group">
        <label class>{{ $ml.with("VueJS").get("txtUsers") }}</label>
        <div>
          <multiselect
            :multiple="true"
            v-model="selectedItem.users"
            :options="options.users"
            :clear-on-select="false"
            :preserve-search="true"
            :placeholder="$ml.with('VueJS').get('txtPickSome')"
            label="text"
            track-by="text"
          ></multiselect>
        </div>
      </div>
      <div class="form-group">
        <label class="">{{ $ml.with("VueJS").get("txtTeam") }}</label>
        <div>
          <multiselect
            :multiple="true"
            v-model="selectedItem.teams"
            :options="currentTeamOption"
            :clear-on-select="false"
            :preserve-search="true"
            :placeholder="$ml.with('VueJS').get('txtPickSome')"
            label="text"
            track-by="text"
          ></multiselect>
        </div>
      </div>
      <div class="form-group">
        <label class="">{{ $ml.with("VueJS").get("txtStartDate") }}</label>
        <datepicker
          input-class="form-control"
          :placeholder="$ml.with('VueJS').get('txtSelectDate')"
          v-model="selectedItem.start_date"
          :format="customFormatter"
          :disabled-dates="disabledEndDates()"
          :language="getLangCode(this.$ml)"
        >
        </datepicker>
      </div>
      <div class="form-group">
        <label class="">{{ $ml.with("VueJS").get("txtEndDate") }}</label>
        <datepicker
          input-class="form-control"
          :placeholder="$ml.with('VueJS').get('txtSelectDate')"
          v-model="selectedItem.end_date"
          :format="customFormatter"
          :disabled-dates="disabledStartDates()"
          :language="getLangCode(this.$ml)"
        >
        </datepicker>
      </div>
      <error-item :errors="validationErrors"></error-item>
      <success-item :success="validationSuccess"></success-item>
      <div class="form-group text-right">
        <button
          type="button"
          class="btn btn-primary"
          @click="addSchedulesTimetable(selectedItem)"
        >
          {{ $ml.with("VueJS").get("txtAdd") }}
        </button>
      </div>
    </form>
  </modal>
</template>
<script>
import ErrorItem from "../../components/Validations/Error";
import SuccessItem from "../../components/Validations/Success";
import Modal from "../../components/Modals/Modal";
import Multiselect from "vue-multiselect";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import Datepicker from "vuejs-datepicker";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "CreateSchedulesTimetable",
  components: {
    ErrorItem,
    SuccessItem,
    Modal,
    Multiselect,
    Select2,
    Datepicker,
  },
  computed: {
    ...mapGetters("timetable", {
      selectedItem: "selectedItem",
      validationErrors: "validationErrors",
      validationSuccess: "validationSuccess",
    }),

    ...mapGetters({
      getLangCode: "getLangCode",
      customFormatter: "customFormatter",
      currentTeamOption: "currentTeamOption",
    }),
  },
  props: ["options"],
  methods: {
    ...mapActions("timetable", {
      resetValidate: "resetValidate",
      resetSelectedItem: "resetSelectedItem",
      addSchedulesTimetable: "addSchedulesTimetable",
    }),
    disabledStartDates() {
      if (this.selectedItem.start_date) {
        const obj = { to: new Date(this.selectedItem.start_date) };
        return obj;
      }
    },
    disabledEndDates() {
      if (this.selectedItem.end_date) {
        const obj = { from: new Date(this.selectedItem.end_date) };
        return obj;
      }
    },
    resetValidation() {
      this.resetValidate();
      this.resetSelectedItem();
    },
  },
};
</script>
<style lang="scss">
@import "~vue-multiselect/dist/vue-multiselect.min.css";
</style>