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
          v-model="selectedItem.memo"
          class="form-control project-memo"
        />
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

export default {
  name: "edit-item",
  components: {
    ErrorItem,
    SuccessItem,
    Modal,
  },

  computed: {
    ...mapGetters("timeslots", {
      selectedItem: "selectedItem",
      validationErrors: "validationErrors",
      validationSuccess: "validationSuccess",
    }),
  },

  methods: {
    ...mapActions("timeslots", {
      resetValidate: "resetValidate",
      resetSelectedItem: "resetSelectedItem",
      updateItem: "updateItem",
      deleteItem: "deleteItem",
    }),

    resetValidation() {
      this.resetValidate()
      this.resetSelectedItem()
    },
  },
};
</script>