<template>
  <modal
    id="itemCreate"
    :sizeClasses="modalLg"
    v-on:reset-validation="resetValidation"
  >
    <template slot="title">{{
      $ml.with("VueJS").get("txtCreateType")
    }}</template>
    <div class="form-group">
      <label class>{{ $ml.with("VueJS").get("txtName") }}</label>
      <input
        v-model="selectedItem.slug"
        type="text"
        name="slug"
        class="form-control"
        required
      />
    </div>
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <label class>{{ $ml.with("VueJS").get("txtNameVi") }}</label>
          <input
            v-model="selectedItem.slug_vi"
            type="text"
            name="slug_vi"
            class="form-control"
          />
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label class>{{ $ml.with("VueJS").get("txtNameJa") }}</label>
          <input
            v-model="selectedItem.slug_ja"
            type="text"
            name="slug_ja"
            class="form-control"
          />
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <label class>{{ $ml.with("VueJS").get("txtDepartments") }}</label>
          <select-2
            :options="deptOptions"
            v-model="selectedItem.dept_id"
            class="select2"
          ></select-2>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label class>{{ $ml.with("VueJS").get("txtLineRoom") }} <input class="ml-2" v-model="selectedItem.checkFinsh.lineroom" type="checkbox"> FINISH MESSAGE</label>
          <input
            v-model="selectedItem.line_room"
            type="text"
            name="line_room"
            class="form-control"
            :disabled="!selectedItem.checkFinsh.lineroom"
          />
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6">
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label class>{{ $ml.with("VueJS").get("txtEmail") }} <input class="ml-2" v-model="selectedItem.checkFinsh.email" type="checkbox"> FINISH MESSAGE</label>
          <input
            v-model="selectedItem.email"
            type="text"
            name="email"
            class="form-control"
            :disabled="!selectedItem.checkFinsh.email"
          />
        </div>
      </div>
    </div>
    <div class="form-group">
      <label class>{{ $ml.with("VueJS").get("txtColor") }}</label>
      <color-picker
        :color="selectedItem.value"
        v-model="selectedItem.value"
      ></color-picker>
    </div>
    <error-item :errors="validationErrors"></error-item>
    <success-item :success="validationSuccess"></success-item>
    <hr />
    <div class="form-group text-right">
      <button
        @click="createItem(selectedItem)"
        type="button"
        class="btn btn-primary"
      >
        {{ $ml.with("VueJS").get("txtCreate") }}
      </button>
    </div>
  </modal>
</template>

<script>
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import ErrorItem from "../../components/Validations/Error";
import SuccessItem from "../../components/Validations/Success";
import Modal from "../../components/Modals/Modal";
import ColorPicker from "../../components/ColorPicker/ColorPicker";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "create-item",

  data() {
    return {
      modalLg: "modal-lg",
    };
  },

  components: {
    Select2,
    Modal,
    ErrorItem,
    SuccessItem,
    ColorPicker,
  },

  computed: {
    ...mapGetters({
      selectedItem: "types/selectedItem",
      validationErrors: "types/validationErrors",
      validationSuccess: "types/validationSuccess",
      deptOptions: "departments/options",
    })
  },

  methods: {
    ...mapActions("types", {
      resetValidate: "resetValidate",
      resetSelectedItem: "resetSelectedItem",
      createItem: "createItem",
    }),

    resetValidation() {
      this.resetValidate();
      this.resetSelectedItem();
    },
  }
};
</script>