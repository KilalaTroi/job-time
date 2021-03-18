<template>
  <modal id="itemDetail" v-on:reset-validation="resetValidation">
    <template slot="title">{{
      $ml.with("VueJS").get("txtUpdateTime")
    }}</template>
    <form v-if="selectedItem">
      <div class="form-group">
       <h4 class="text-center mb-1">
          <b>{{ selectedItem.fullproject }}</b>
        </h4>
        <h5 class="text-center mt-1">{{ dateFormat(selectedItem.date,'DD/MMM/YYYY','') }}</h5>
      </div>
      <hr />
      <div class="form-group">
        <div class="row">
          <div :class="{'col-sm-4': filters.team == 2, 'col-sm-6': filters.team != 2}">
           <label><strong>{{ $ml.with("VueJS").get("lblStartTime") }}</strong></label>
            <vue-timepicker
              v-model="selectedItem.start_time"
              hide-disabled-items
              :minute-range="startMinuteRange"
              :hour-range="[[7, 22]]"
              input-width="100%"
              close-on-complete
              @change="changeStartTime"
              required
            ></vue-timepicker>
          </div>
          <div :class="{'col-sm-4': filters.team == 2, 'col-sm-6': filters.team != 2}">
           <label><strong>{{ $ml.with("VueJS").get("lblEndTime") }}:</strong></label>
            <vue-timepicker
              v-on:error="disabledSubmit"
              v-model="selectedItem.end_time"
              hide-disabled-items
              :minute-range="endMinuteRange"
              :hour-range="endHourRange"
              input-width="100%"
              close-on-complete
              @change="changeEndTime"
              required
              :disabled="endDisabled"
            ></vue-timepicker>
          </div>
          <div class="col-sm-4" v-if="filters.team == 2">
            <label>{{ $ml.with("VueJS").get("txtQuantity") }}:</label>
            <span class="w-100 vue__time-picker">
              <input :disabled="selectedItem.email || selectedItem.room_id || loginUser.username === 'nancy'" type="number" v-model="selectedItem.quantity" class="w-100 display-time all-selected" />
            </span>
          </div>
        </div>
      </div>
      <div class="form-group" v-if="selectedItem.showLunchBreak">
        <base-checkbox v-model="selectedItem.exceptLunchBreak" class="align-self-end">{{ $ml.with("VueJS").get("txtExcludeLunchBreak") }}</base-checkbox>
      </div>
      <div class="form-group" v-if="filters.team == 2 || filters.team == 3">
        <label>{{ $ml.with("VueJS").get("txtWork") }}</label>
        <input type="text" v-model="selectedItem.note" class="form-control" />
      </div>
      <error-item :errors="validationErrors"></error-item>
      <success-item :success="validationSuccess"></success-item>
      <hr />
      <div class="form-group text-right">
        <button type="button" class="btn btn-primary" :disabled="overlap" @click="updateTime(selectedItem)">
          {{ $ml.with("VueJS").get("txtUpdate") }}
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
import moment from "moment";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "EditTime",
  components: {
    ErrorItem,
    SuccessItem,
    Modal,
    VueTimepicker,
  },
  computed: {
    // data: "data",

    ...mapGetters("jobs", {
      selectedItem: "selectedItem",
      filters: "filters",
      overlap: "overlap",
      validationErrors: "validationErrors",
      validationSuccess: "validationSuccess",
    }),

    ...mapGetters({
      dateFormat: "dateFormat",
      loginUser: "loginUser",
    }),
	},

  data() {
    return {
			defaultMinuteRange: [0, 10, 20, 30, 40, 50],
      endHourRange: [[7, 22]],
      startMinuteRange: [0, 10, 20, 30, 40, 50],
      endMinuteRange: [0, 10, 20, 30, 40, 50],
      startHour: "",
      startMinute: "",
      buttonDisabled: true,
      endDisabled: true,
      showLunchBreak: false,
      exceptLunchBreak: true,
    };
  },
  methods: {
    ...mapActions("jobs", {
      resetValidate: "resetValidate",
      resetSelectedItem: "resetSelectedItem",
      checkTimeOverlap: "checkTimeOverlap",
      updateTime: "updateTime",
		}),

		resetValidation() {
			this.resetValidate();
			this.resetSelectedItem();
		},

    changeStartTime(eventData) {
      const slItem = this.selectedItem;
      const data = {
        slItem: slItem,
        run: 2
      };

      this.startMinute = eventData.data.m * 1;
      if (this.startMinute === 50) {
        this.startHour = eventData.data.H * 1 + 1;
        this.endMinuteRange = this.defaultMinuteRange;
      } else {
        this.startHour = eventData.data.H * 1;
        if(slItem.start_time['HH'] == slItem.end_time['HH']) this.endMinuteRange = this.endMinuteRange.filter( (item) => item > this.startMinute);
        else this.endMinuteRange = this.defaultMinuteRange;
      }

      this.endHourRange = [[this.startHour, 22]];

      this.endDisabled = true;

			if (slItem.start_time["HH"] && slItem.start_time["mm"]) this.endDisabled = false;

      if (slItem.end_time && slItem.end_time["HH"] && slItem.end_time["mm"]) {
        const startTime = slItem.start_time["HH"] + slItem.start_time["mm"] * 1;
        const endTime = slItem.end_time["HH"] + slItem.end_time["mm"] * 1;
        if (startTime >= endTime) slItem.end_time = { HH: "", mm: "" };
      }
      this.checkTimeOverlap(data);
    },
    changeEndTime(eventData) {
      const slItem = this.selectedItem;
      const data = {
        slItem: slItem,
        run: 2
      };

      if (eventData.data.H * 1 > this.startHour) this.endMinuteRange = this.defaultMinuteRange;
      else this.endMinuteRange = this.startMinute === 50 ? this.defaultMinuteRange : this.endMinuteRange.filter((item) => item > this.startMinute);

      this.checkTimeOverlap(data);

      if (this.startHour < 12 && eventData.data.H * 1 > 13) this.selectedItem.showLunchBreak = true;
      else this.selectedItem.showLunchBreak = false;
    },
    disabledSubmit(){
      this.$store.commit('jobs/SET_OVERLAP', true);
    }
  },
};
</script>
<style lang="scss">
@import "~vue2-timepicker/dist/VueTimepicker.css";
</style>