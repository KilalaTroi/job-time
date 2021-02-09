<template>
  <modal
    id="itemCreate"
    :sizeClasses="modalLg"
    v-on:reset-validation="resetValidation"
  >
    <template slot="title">{{
      $ml.with("VueJS").get("txtCreateProject")
    }}</template>

    <div class="row">
      <div class="col-sm-12">
        <div class="form-group">
          <label class="">{{ $ml.with("VueJS").get("txtName") }}</label>
          <input
            v-model="selectedItem.project_name"
            type="text"
            class="form-control"
            required
          />
        </div>
      </div>
    </div>
    <hr />
    <div class="row">
      <div class="col-sm-3">
        <div class="form-group">
          <label class="">{{ $ml.with("VueJS").get("txtYearOfIssue") }}</label>
          <datepicker
            input-class="form-control"
            placeholder="Select Year"
            minimum-view="year"
            v-model="selectedItem.issue_year"
            :clear-button="true"
            format="yyyy"
            :language="getLangCode(this.$ml)"
          >
          </datepicker>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="form-group">
          <label class="">{{ $ml.with("VueJS").get("txtIssue") }}</label>
          <input
            v-model="selectedItem.issue_name"
            type="text"
            class="form-control"
          />
        </div>
      </div>
      <div class="col-sm-3">
        <div class="form-group">
          <label class="">{{ $ml.with("VueJS").get("txtPage") }}</label>
          <input
            v-model="selectedItem.page"
            type="number"
            class="form-control"
          />
        </div>
      </div>
      <div class="col-sm-3">
        <div class="form-group">
          <label class="">{{ $ml.with("VueJS").get("txtNoPeriod") }}</label>
          <input
            v-model="selectedItem.no_period"
            type="checkbox"
            @change="updatePeriod"
            class="form-control"
          />
        </div>
      </div>
    </div>
    <div class="row" v-if="has_period">
      <div class="col-sm-6">
        <div class="form-group">
          <label class="">{{ $ml.with("VueJS").get("txtStartDate") }}</label>
          <datepicker
            input-class="form-control"
            placeholder="Select Date"
            v-model="selectedItem.start_date"
            :format="customFormatter"
            :disabled-dates="disabledEndDates()"
            :language="getLangCode(this.$ml)"
          >
          </datepicker>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label class="">{{ $ml.with("VueJS").get("txtEndDate") }}</label>
          <datepicker
            input-class="form-control"
            placeholder="Select Date"
            v-model="selectedItem.end_date"
            :format="customFormatter"
            :disabled-dates="disabledStartDates()"
            :language="getLangCode(this.$ml)"
          >
          </datepicker>
        </div>
      </div>
    </div>
    <hr />
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <label class>
            {{ $ml.with("VueJS").get("txtTypes") }}
          </label>
          <div>
            <select2-type
              :options="typeOptions"
              v-model="selectedItem.type_id"
              class="select2"
            />
          </div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label class="">{{ $ml.with("VueJS").get("txtTeam") }}</label>
          <div>
            <multiselect
              :multiple="true"
              v-model="selectedItem.team"
              :options="currentTeamOption"
              :clear-on-select="false"
              :preserve-search="false"
              :placeholder="$ml.with('VueJS').get('txtPickSome')"
              label="text"
              track-by="text"
            ></multiselect>
          </div>
        </div>
      </div>
    </div>
    <hr v-if="currentTeam.id == 2"/>
    <div class="row" v-if="currentTeam.id == 2">
      <div class="col-sm-6">
        <div class="form-group">
          <label class>
            {{ $ml.with("VueJS").get("txtSchedule") }}
          </label>
          <datepicker
            v-model="selectedItem.schedule_date"
            input-class="form-control"
            placeholder="yyyy/mm/dd"
            :format="customFormatter"
            :disabled-dates="disabledScheduleDates()"
            :language="getLangCode(this.$ml)"
          >
          </datepicker>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="form-group">
          <label class>
            {{ $ml.with("VueJS").get("lblStartTime") }}
          </label>
          <vue-timepicker
            v-model="selectedItem.schedule_start_time"
            hide-disabled-items
            :minute-range="startMinuteRange"
            :hour-range="[[7, 22]]"
            input-width="100%"
            close-on-complete
            @change="changeStartTime"
          ></vue-timepicker>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="form-group">
          <label class>
            {{ $ml.with("VueJS").get("lblEndTime") }}
          </label>
          <vue-timepicker
            v-model="selectedItem.schedule_end_time"
            hide-disabled-items
            :minute-range="endMinuteRange"
            :hour-range="endHourRange"
            input-width="100%"
            close-on-complete
            @change="changeEndTime"
            :disabled="endDisabled"
          ></vue-timepicker>
        </div>
      </div>
    </div>
    <hr v-if="currentTeam.id == 2 && selectedItem.schedule_date"/>
    <div class="row" v-if="currentTeam.id == 2 && selectedItem.schedule_date">
      <div class="col-sm-6">
        <div class="form-group d-flex align-items-center">
          <label class="mb-0">
            {{ $ml.with("VueJS").get("txtStartWorking") }}
          </label>
          <base-checkbox v-model="selectedItem.start_working">Yes</base-checkbox>
        </div>
      </div>
    </div>
    <div class="row" v-if="currentTeam.id == 2 && selectedItem.start_working">
      <div class="col">
        <div class="form-group">
          <label class="">{{
            $ml.with("VueJS").get("txtData")
          }}</label>
          <input
            v-model="selectedItem.work_data"
            type="text"
            class="form-control"
          />
        </div>
      </div>
      <div class="col">
        <div class="form-group">
          <label class="">{{
            $ml.with("VueJS").get("txtInkiet")
          }}</label>
          <input
            v-model="selectedItem.work_inkjet"
            type="text"
            class="form-control"
          />
        </div>
      </div>
      <div class="col">
        <div class="form-group">
          <label class="">{{
            $ml.with("VueJS").get("txtFinishRQ")
          }}</label>
          <input
            v-model="selectedItem.work_rq"
            type="text"
            class="form-control"
          />
        </div>
      </div>
      <div class="col-12">
        <label>{{ $ml.with("VueJS").get("txtMessage") }}</label>
        <textarea
            v-model="selectedItem.work_message"
            class="form-control"
            rows="4"
          ></textarea>
      </div>
    </div>
    <error-item :errors="validationErrors"></error-item>
    <success-item :success="validationSuccess"></success-item>
    <hr />
    <div class="form-group text-right">
      <button
        type="button"
        @click="addProject(selectedItem)"
        class="btn btn-primary"
      >
        {{ $ml.with("VueJS").get("txtCreate") }}
      </button>
    </div>
  </modal>
</template>
<script>
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import Select2Type from "../../components/SelectTwo/SelectTwoType.vue";
import Modal from "../../components/Modals/Modal";
import ErrorItem from "../../components/Validations/Error";
import SuccessItem from "../../components/Validations/Success";
import Datepicker from "vuejs-datepicker";
import Multiselect from "vue-multiselect";
import VueTimepicker from "vue2-timepicker";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "create-item",
  components: {
    Select2,
    Select2Type,
    datepicker: Datepicker,
    ErrorItem,
    SuccessItem,
    Modal,
    Multiselect,
    VueTimepicker,
  },
  computed: {
    ...mapGetters({
      typeOptions: "types/options",
      selectedItem: "projects/selectedItem",
      currentTeamOption: "currentTeamOption",
      currentTeam: "currentTeam",
      validationErrors: "projects/validationErrors",
      validationSuccess: "projects/validationSuccess",
      customFormatter: "customFormatter",
      getLangCode: "getLangCode",
    }),
  },
  data() {
    return {
      has_period: true,
      modalLg: "modal-lg",
      defaultMinuteRange: [0, 10, 20, 30, 40, 50],
      startMinuteRange: [0, 10, 20, 30, 40, 50],
			endMinuteRange: [0, 10, 20, 30, 40, 50],
			endHourRange: [[7, 19]],
      startHour: "",
      startMinute: "",
      endDisabled: true,
    };
  },
  methods: {
    ...mapActions({
      addProject: "projects/addProject",
      resetValidate: "projects/resetValidate",
      resetSelectedItem: "projects/resetSelectedItem",
    }),
    resetValidation() {
      this.resetValidate();
      this.resetSelectedItem();
    },
    updatePeriod() {
      if (this.selectedItem.no_period) {
        this.selectedItem.start_date = "";
        this.selectedItem.end_date = "";
        this.has_period = false;
      } else {
        this.has_period = true;
      }
    },
    disabledStartDates() {
      if (this.selectedItem.start_date) {
        let obj = {
          to: new Date(this.selectedItem.start_date), // Disable all dates after specific date
          // days: [0], // Disable Saturday's and Sunday's
        };
        return obj;
      }
    },
    disabledEndDates() {
      if (this.selectedItem.end_date) {
        let obj = {
          from: new Date(this.selectedItem.end_date), // Disable all dates after specific date
          // days: [0], // Disable Saturday's and Sunday's
        };
        return obj;
      }
    },
    disabledScheduleDates() {
      if (this.selectedItem.end_date) {
        let obj = {
          from: new Date(this.selectedItem.end_date),
          to: new Date(this.selectedItem.start_date),   
        };
        return obj;
      }
    },
    changeStartTime(eventData) {
      // this.startMinute = eventData.data.m * 1;

      // if (this.startMinute === 50) {
      //   this.startHour = eventData.data.H * 1 + 1;
      //   this.endMinuteRange = this.defaultMinuteRange;
      // } else {
			// 	this.startHour = eventData.data.H * 1;
      //   if(this.selectedItem.schedule_start_time['HH'] == this.selectedItem.schedule_end_time['HH']) this.endMinuteRange = this.endMinuteRange.filter( (item) => item > this.startMinute);
      //   else this.endMinuteRange = this.defaultMinuteRange;
      // }

			// this.endHourRange = [[this.startHour, 19]];

			// this.endDisabled = true;
      
			if (this.selectedItem.schedule_start_time["HH"] && this.selectedItem.schedule_start_time["mm"]) this.endDisabled = false;

			// if (this.selectedItem.schedule_end_time && this.selectedItem.schedule_end_time["HH"] && this.selectedItem.schedule_end_time["mm"]) {
			// 	const startTime = this.selectedItem.schedule_start_time["HH"] + this.selectedItem.schedule_start_time["mm"] * 1;
			// 	const endTime = this.selectedItem.schedule_end_time["HH"] + this.selectedItem.schedule_end_time["mm"] * 1;
			// 	if(startTime >= endTime) this.selectedItem.schedule_end_time = {"HH": '', 'mm': ''};
      // }
    },
    changeEndTime(eventData) {
      // if (eventData.data.H * 1 > this.startHour) this.endMinuteRange = this.defaultMinuteRange;
			// else this.endMinuteRange = this.startMinute === 50 ? this.defaultMinuteRange : this.endMinuteRange.filter((item) => item > this.startMinute);
    },
  },
  watch: {
    selectedItem: [
      {
        handler: "updatePeriod",
      },
    ],
  },
};
</script>
<style lang="scss">
@import "~vue2-timepicker/dist/VueTimepicker.css";
input[type="radio"],
input[type="checkbox"] {
  &.form-control {
    width: 40px;
  }
}
span.time-picker {
  height: 100%;

  input.display-time {
    height: 40px;
    border-radius: 4px;
  }
}
</style>