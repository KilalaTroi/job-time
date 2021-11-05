<template>
  <modal id="UpdateReason" v-on:reset-validation="resetValidation">
    <template slot="title">{{ $ml.with('VueJS').get('txtUpdateReason') }}</template>
    <form>
      <div class="form-group">
        <div>
          <input
            v-model="selectedItemReason.fullname"
            type="text"
            class="form-control"
            readonly
            required
          />
        </div>
      </div>
      <div class="form-group">
        <input
          v-model="selectedItemReason.date"
          type="text"
          class="form-control"
          readonly
          required
        />
      </div>
      <div class="form-group">
        <textarea
          v-model="selectedItemReason.reason"
          class="form-control"
          rows="8"
        ></textarea>
      </div>
      <error-item :errors="validationErrors"></error-item>
      <success-item :success="validationSuccess"></success-item>
      <div class="form-group text-right">
        <button
          type="button"
          class="btn btn-primary"
          @click="updateReason(selectedItemReason)"
        >
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
import Multiselect from "vue-multiselect";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import Datepicker from "vuejs-datepicker";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "UpdateReason",
  components: {
    ErrorItem,
    SuccessItem,
    Modal,
    Multiselect,
    Select2,
    Datepicker,
  },
  computed: {
    ...mapGetters("checkinout", {
      selectedItemReason: "selectedItemReason",
      validationErrors: "validationErrors",
      validationSuccess: "validationSuccess",
    }),

    ...mapGetters({
      getLangCode: "getLangCode",
      customFormatter: "customFormatter",
      currentTeamOption: "currentTeamOption",
    }),
  },
  methods: {
    ...mapActions("checkinout", {
      resetValidate: "resetValidate",
      resetSelectedItem: "resetSelectedItem",
      updateReason: "updateReason",
    }),
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