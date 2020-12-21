<template>
  <modal
    id="editIssue"
    :sizeClasses="modalLg"
    v-on:reset-validation="resetValidation"
  >
    <template slot="title">{{
      $ml.with("VueJS").get("txtEditIssue")
    }}</template>
    <div v-if="selectedItem">
      <div class="row">
        <div class="col-sm-2">
          <div class="form-group">
            <label class="">{{
              $ml.with("VueJS").get("txtYearOfIssue")
            }}</label>
             <datepicker
            input-class="form-control"
            placeholder="Select Year"
            minimum-view="year"
            :clear-button="true"
            v-model="selectedItem.i_year"
            format="yyyy"
            :language="getLangCode(this.$ml)"
          >
          </datepicker>
          </div>
        </div>
        <div class="col-sm-2">
          <div class="form-group">
            <label class="">{{ $ml.with("VueJS").get("txtIssue") }}</label>
            <input
              v-model="selectedItem.i_name"
              type="text"
              name="issue"
              class="form-control"
            />
          </div>
        </div>
        <div class="col-sm-2">
          <div class="form-group">
            <label class="">{{ $ml.with("VueJS").get("txtPage") }}</label>
            <input
              v-model="selectedItem.page"
              type="number"
              name="page"
              class="form-control"
            />
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
            <label class="">{{ $ml.with("VueJS").get("txtProject") }}</label>
            <div>
              <select-2
                :options="projectOptions"
                v-model="selectedItem.id"
                class="select2"
              />
            </div>
          </div>
        </div>
        <div class="col-sm-2">
          <div class="form-group">
            <label class="">{{ $ml.with("VueJS").get("txtNoPeriod") }}</label>
            <input
              v-model="selectedItem.no_period"
              type="checkbox"
              name="no_period"
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
              name="startDate"
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
              name="endDate"
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
      <error-item :errors="validationErrors"></error-item>
      <success-item :success="validationSuccess"></success-item>
      <hr />
      <div class="form-group text-right">
        <button
          @click="updateIssue(selectedItem)"
          type="button"
          class="btn btn-primary"
        >
          {{ $ml.with("VueJS").get("txtUpdate") }}
        </button>
      </div>
    </div>
  </modal>
</template>

<script>
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import Modal from "../../components/Modals/Modal";
import ErrorItem from "../../components/Validations/Error";
import SuccessItem from "../../components/Validations/Success";
import Datepicker from "vuejs-datepicker";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "edit-issue",

  components: {
    Select2,
    datepicker: Datepicker,
    ErrorItem,
    SuccessItem,
    Modal,
  },

  computed: {
    ...mapGetters({
      projectOptions: "projects/options",
      selectedItem: "projects/selectedItem",
      validationErrors: "projects/validationErrors",
      validationSuccess: "projects/validationSuccess",
      customFormatter: "customFormatter",
      dateFormat: "dateFormat",
      getLangCode: "getLangCode",
    }),
  },

  data() {
    return {
      modalLg: "modal-lg",
      has_period: true,
    };
  },

  methods: {
    ...mapActions({
      updateIssue: "projects/updateIssue",
      resetValidate: "projects/resetValidate",
      resetSelectedItem: "projects/resetSelectedItem",
    }),
    resetValidation() {
      this.resetValidate();
      this.resetSelectedItem();
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
    updatePeriod() {
      if (this.selectedItem.no_period) {
        this.selectedItem.start_date = "";
        this.selectedItem.end_date = "";
        this.has_period = false;
      } else {
        this.has_period = true;
      }
    },
    currentPeriod(data) {
      if (
        this.dateFormat(data.start_date) === "--" &&
        this.dateFormat(data.end_date) === "--"
      ) {
        this.selectedItem.no_period = true;
        this.has_period = false;
      } else {
        this.has_period = true;
      }
    },
  },
  watch: {
    selectedItem: [
      {
        handler: "currentPeriod",
      },
    ],
  },
};
</script>