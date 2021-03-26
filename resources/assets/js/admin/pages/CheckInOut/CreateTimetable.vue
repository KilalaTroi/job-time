<template>
  <modal id="timeTableCreate" v-on:reset-validation="resetValidation">
    <template slot="title">Create Timetable</template>
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
        <label><strong>Monday</strong></label>
        <div class="row">
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.check_in.mon"
              hide-disabled-items
              :minute-range="defaultMinuteRange"
              :hour-range="[[8, 17]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.check_out.mon"
              hide-disabled-items
              :minute-range="defaultMinuteRange"
              :hour-range="[[8, 17]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label><strong>Tuesday</strong></label>
        <div class="row">
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.check_in.tue"
              hide-disabled-items
              :minute-range="defaultMinuteRange"
              :hour-range="[[8, 17]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.check_out.tue"
              hide-disabled-items
              :minute-range="defaultMinuteRange"
              :hour-range="[[8, 17]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label><strong>Wednesday</strong></label>
        <div class="row">
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.check_in.wed"
              hide-disabled-items
              :minute-range="defaultMinuteRange"
              :hour-range="[[8, 17]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.check_out.wed"
              hide-disabled-items
              :minute-range="defaultMinuteRange"
              :hour-range="[[8, 17]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label><strong>Thursday</strong></label>
        <div class="row">
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.check_in.thu"
              hide-disabled-items
              :minute-range="defaultMinuteRange"
              :hour-range="[[8, 17]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.check_out.thu"
              hide-disabled-items
              :minute-range="defaultMinuteRange"
              :hour-range="[[8, 17]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label><strong>Friday</strong></label>
        <div class="row">
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.check_in.fri"
              hide-disabled-items
              :minute-range="defaultMinuteRange"
              :hour-range="[[8, 17]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.check_out.fri"
              hide-disabled-items
              :minute-range="defaultMinuteRange"
              :hour-range="[[8, 17]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label><strong>Saturday</strong></label>
        <div class="row">
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.check_in.sat"
              hide-disabled-items
              :minute-range="defaultMinuteRange"
              :hour-range="[[8, 17]]"
              input-width="100%"
              close-on-complete
              required
            ></vue-timepicker>
          </div>
          <div class="col-sm-6">
            <vue-timepicker
              v-model="selectedItemt.check_out.sat"
              hide-disabled-items
              :minute-range="defaultMinuteRange"
              :hour-range="[[8, 17]]"
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
        <button type="button" class="btn btn-primary" @click="addTimetable(selectedItemt)" >
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
    	addTimetable: "addTimetable"
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