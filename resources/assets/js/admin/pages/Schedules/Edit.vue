<template>
  <modal id="itemDetail" v-on:reset-validation="resetValidation">
    <div v-if="selectedItem">
      <div class="form-group">
        <h4 class="text-center mb-1">
          <b>{{ selectedItem.title_not_memo }}</b>
        </h4>
        <h5 class="text-center mt-1">
          {{ selectedItem.start_time }} -
          {{ selectedItem.end_time }}
        </h5>
      </div>
      <hr />
      <div class="form-group">
        <label>{{ $ml.with("VueJS").get("txtPhase") }}</label>
        <input
          type="text"
          v-model="selectedItem.memo"
          class="form-control project-memo"
        />
      </div>
      <div v-if="14 == selectedItem.p_id || 344 == selectedItem.p_id" class="form-group d-flex align-items-center">
        <input
          type="checkbox"
          v-model="selectedItem.booking"
          class="form-control mr-2"
          :style="{width: '25px'}"
        />
        <label class="mb-0">{{ $ml.with("VueJS").get("txtUseTheMeetingRoom") }}</label>
      </div>
      <div v-if="14 == selectedItem.p_id || 344 == selectedItem.p_id" class="form-group d-flex align-items-center">
        <input
          type="checkbox"
          v-model="selectedItem.weekendcheck"
          class="form-control mr-2"
          :style="{width: '25px'}"
        />
        <label class="mb-0 mr-3">Every Weekend</label>

        <datepicker
          v-if="selectedItem.weekendcheck"
          input-class="form-control"
          placeholder="Select Date"
          v-model="selectedItem.weekend"
          :disabled-dates="disabledStartDates(selectedItem.datew)"
          :format="customFormatter"
          :language="getLangCode(this.$ml)"
        ></datepicker>
      </div>
      <error-item :errors="validationErrors"></error-item>
      <success-item :success="validationSuccess"></success-item>
      <hr />
      <div class="form-group text-right">
        <button
          @click="updateItem(selectedItem)"
          type="button"
          class="btn btn-primary"
        >
          {{ $ml.with("VueJS").get("txtSave") }}
        </button>
        <button
          type="button"
          class="btn btn-danger ml-3"
          @click="deleteItem($ml.with('VueJS').get('msgConfirmDelete'))"
        >
         {{$ml.with('VueJS').get('txtDelete')}}
        </button>
      </div>
    </div>
  </modal>
</template>

<script>
import Modal from "../../components/Modals/Modal"
import { mapGetters, mapActions } from "vuex"
import ErrorItem from "../../components/Validations/Error"
import SuccessItem from "../../components/Validations/Success"
import Datepicker from "vuejs-datepicker";

export default {
  name: "edit-item",
  components: {
    ErrorItem,
    SuccessItem,
    Modal,
    Datepicker
  },

  computed: {
    ...mapGetters("schedules", {
      selectedItem: "selectedItem",
      validationErrors: "validationErrors",
      validationSuccess: "validationSuccess",
    }),
    ...mapGetters({
      getLanguage: "getLanguage",
      getLangCode: "getLangCode",
      customFormatter: "customFormatter",
    }),
  },

  methods: {
    ...mapActions("schedules", {
      resetValidate: "resetValidate",
      resetSelectedItem: "resetSelectedItem",
      updateItem: "updateItem",
      deleteItem: "deleteItem",
    }),

    disabledStartDates(start_date) {
			if (start_date) return { to: start_date };
	  },

    resetValidation() {
      this.resetValidate()
      this.resetSelectedItem()
    },
  },
};
</script>