<template>
  <modal id="timeTableCreate" v-on:reset-validation="resetValidation">
    <template slot="title">{{ $ml.with("VueJS").get("txtCreateTimetable") }}</template>
    <form>
      <div class="form-group">
        <label class="">{{ $ml.with("VueJS").get("txtName") }}</label>
        <input
          v-model="selectedItemt.name"
          type="text"
          class="form-control"
          required
        />
      </div>
      <div class="form-group">
        <label><strong>{{ $ml.with("VueJS").get("txtMondayFriday") }}</strong></label>
        <div class="row mb-3">
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.monfri.check_in"
              hide-disabled-items
              hide-clear-button
              :minute-range="defaultMinuteRange"
              :hour-range="[[5, 20]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.monfri.check_out"
              hide-disabled-items
              hide-clear-button
              :minute-range="defaultMinuteRange"
              :hour-range="[[5, 20]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
        </div>
        <label><strong>{{ $ml.with("VueJS").get("txtEffectiveTimeStart") }}</strong></label>
        <div class="row mb-3">
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.monfri.check_in_start"
              hide-disabled-items
              hide-clear-button
              :minute-range="defaultMinuteRange"
              :hour-range="[[4, 12]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.monfri.check_in_end"
              hide-disabled-items
              hide-clear-button
              :minute-range="defaultMinuteRange"
              :hour-range="[[4, 12]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
        </div>
        <label><strong>{{ $ml.with("VueJS").get("txtEffectiveTimeEnd") }}</strong></label>
        <div class="row mb-3">
            <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.monfri.check_out_start"
              hide-disabled-items
              hide-clear-button
              :minute-range="defaultMinuteRange"
              :hour-range="[[13, 22]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.monfri.check_out_end"
              hide-disabled-items
              hide-clear-button
              :minute-range="defaultMinuteRange"
              :hour-range="[[13, 22]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
        </div>
      </div>
      <hr>
      <div class="form-group">
        <label><strong>{{ $ml.with("VueJS").get("txtSaturdaySunday") }}</strong></label>
        <div class="row mb-3">
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.satsun.check_in"
              hide-disabled-items
              hide-clear-button
              :minute-range="defaultMinuteRange"
              :hour-range="[[5, 20]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.satsun.check_out"
              hide-disabled-items
              hide-clear-button
              :minute-range="defaultMinuteRange"
              :hour-range="[[8, 20]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
        </div>
        <label><strong>{{ $ml.with("VueJS").get("txtEffectiveTimeStart") }}</strong></label>
        <div class="row mb-3">
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.satsun.check_in_start"
              hide-disabled-items
              hide-clear-button
              :minute-range="defaultMinuteRange"
              :hour-range="[[4, 10]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.satsun.check_in_end"
              hide-disabled-items
              hide-clear-button
              :minute-range="defaultMinuteRange"
              :hour-range="[[4, 10]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
        </div>
        <label><strong>{{ $ml.with("VueJS").get("txtEffectiveTimeEnd") }}</strong></label>
        <div class="row">
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.satsun.check_out_start"
              hide-disabled-items
              hide-clear-button
              :minute-range="defaultMinuteRange"
              :hour-range="[[11, 22]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.satsun.check_out_end"
              hide-disabled-items
              hide-clear-button
              :minute-range="defaultMinuteRange"
              :hour-range="[[11, 22]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
        </div>
      </div>
      <error-item :errors="validationErrors"></error-item>
      <success-item :success="validationSuccess"></success-item>
      <div class="form-group text-right">
        <button
          type="button"
          class="btn btn-primary"
          @click="addTimetable(selectedItemt)"
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
import VueTimepicker from "vue2-timepicker";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "CreateTimetable",
  components: {
    ErrorItem,
    SuccessItem,
    Modal,
    VueTimepicker,
  },
  computed: {
    // data: "data",

    ...mapGetters("timetable", {
      selectedItemt: "selectedItemt",
      validationErrors: "validationErrors",
      validationSuccess: "validationSuccess",
    }),

    ...mapGetters({
      getLangCode: "getLangCode",
      customFormatter: "customFormatter",
      disabledStartDates: "disabledStartDates",
      disabledEndDates: "disabledEndDates",
    }),
  },
  data() {
    return {
      defaultMinuteRange: [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55],
    };
  },
  methods: {
    ...mapActions("timetable", {
      resetValidate: "resetValidate",
      resetSelectedItem: "resetSelectedItem",
      addTimetable: "addTimetable",
      getAll: "getAll",
      getAllSchedules: "getAllSchedules",
    }),
    resetValidation() {
      this.resetValidate();
      this.resetSelectedItem();
    },
  },
};
</script>
<style lang="scss">
@import "~vue2-timepicker/dist/VueTimepicker.css";
</style>