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
        <label>{{ $ml.with("VueJS").get("txtPhase") }} </label>
        <input
          type="text"
          name="memo"
          readonly
          v-model="selectedItem.memo"
          class="form-control project-memo"
        />
      </div>
    </div>
  </modal>
</template>

<script>
import Modal from "../../components/Modals/Modal"
import { mapGetters, mapActions } from "vuex"
import ErrorItem from "../../components/Validations/Error"
import SuccessItem from "../../components/Validations/Success"

export default {
  name: "edit-item",
  components: {
    ErrorItem,
    SuccessItem,
    Modal,
  },

  computed: {
    ...mapGetters("bookings", {
      selectedItem: "selectedItem",
      validationErrors: "validationErrors",
      validationSuccess: "validationSuccess",
    }),
  },

  methods: {
    ...mapActions("bookings", {
      resetValidate: "resetValidate",
      resetSelectedItem: "resetSelectedItem",
    }),

    resetValidation() {
      this.resetValidate()
      this.resetSelectedItem()
    },
  },
};
</script>