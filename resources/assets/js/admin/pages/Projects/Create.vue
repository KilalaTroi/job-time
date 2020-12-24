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
  },
  computed: {
    ...mapGetters({
      typeOptions: "types/options",
      selectedItem: "projects/selectedItem",
      currentTeamOption: "currentTeamOption",
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
input[type="radio"],
input[type="checkbox"] {
  &.form-control {
    width: 40px;
  }
}
</style>