<template>
  <modal
    id="itemCreate"
    sizeClasses="modal-lg"
    v-on:reset-validation="resetValidation"
  >
    <template slot="title">Add Profile</template>

    <div class="form-group">
      <div class="d-flex align-items-start">
        <figure
          class="m-0 mr-3"
          :style="{ width: '80px', height: '100px', border: '1px solid #000' }"
        >
          <img
            :style="{ objectFit: 'cover', width: '100%', height: '100%' }"
            :src="selectedItem.lavatar"
          />
        </figure>
        <input
          v-on:change="handleFileUpload"
          ref="inputFile"
          type="file"
          accept="image/jpeg, image/png"
        />
      </div>
    </div>
    <div class="row">
      <div class="form-group col-sm-6">
        <label class="">Code</label>
        <input v-model="selectedItem.code" type="text" class="form-control" />
      </div>
      <div class="form-group col-sm-6">
        <label class="">{{ $ml.with("VueJS").get("txtName") }}</label>
        <input v-model="selectedItem.name" type="text" class="form-control" />
      </div>
    </div>
    <div class="row">
      <div class="form-group col-sm-6">
        <label class="">{{ $ml.with("VueJS").get("txtEmail") }}</label>
        <input v-model="selectedItem.email" type="text" class="form-control" />
      </div>
      <div class="form-group col-sm-6">
        <label class="">Tel</label>
        <input v-model="selectedItem.tel" type="text" class="form-control" />
      </div>
    </div>
    <div class="row">
      <div class="form-group col-sm-6">
        <label class="">{{ $ml.with("VueJS").get("txtTeam") }}</label>
        <select2
          :options="currentFullTeamOption"
          v-model="selectedItem.team_id"
          :allow-clear="true"
          class="select2"
        />
      </div>
      <div class="form-group col-sm-6">
        <label class="">Position</label>
        <input
          v-model="selectedItem.position"
          type="text"
          class="form-control"
        />
      </div>
    </div>

    <div class="row">
      <div class="form-group col-sm-12">
        <label class="">{{ $ml.with("VueJS").get("txtDesc") }}</label>
        <textarea
          v-model="selectedItem.description"
          class="form-control"
        ></textarea>
      </div>
    </div>
    <error-item :errors="validationErrors"></error-item>
    <success-item :success="validationSuccess"></success-item>
    <hr />
    <div class="form-group text-right">
      <button type="button" class="btn btn-primary" @click="createItem(selectedItem)">
        {{ $ml.with("VueJS").get("txtAdd") }}
      </button>
    </div>
  </modal>
</template>
<script>
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import ErrorItem from "../../components/Validations/Error";
import SuccessItem from "../../components/Validations/Success";
import Modal from "../../components/Modals/Modal";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "create-profile",

  components: {
    Select2,
    ErrorItem,
    SuccessItem,
    Modal,
  },

  computed: {
    ...mapGetters("hrprofiles", {
      selectedItem: "selectedItem",
      validationErrors: "validationErrors",
      validationSuccess: "validationSuccess"
    }),

    ...mapGetters({
      currentFullTeamOption: "currentFullTeamOption",
    }),
  },

  methods: {
    ...mapActions("hrprofiles", {
      handleFileUpload: "handleFileUpload",
      createItem: "createItem",
      resetValidate: "resetValidate",
      resetSelectedItem: "resetSelectedItem"
    }),

    resetValidation() {
      this.$refs.inputFile.value=null
      this.resetValidate();
      this.resetSelectedItem();
    },
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