<template>
  <modal
    id="itemDetail"
    :sizeClasses="modalLg"
    v-on:reset-validation="resetValidation"
  >
    <template slot="title">{{ $ml.with("VueJS").get("txtEditType") }}</template>
    <div v-if="selectedItem">
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
            <label class
              >{{ $ml.with("VueJS").get("txtLineRoom") }}
              <input
                class="ml-2"
                v-model="selectedItem.checkFinsh.lineroom"
                type="checkbox"
              />
              FINISH MESSAGE</label
            >
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
        <div class="col-sm-6"></div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class
              >{{ $ml.with("VueJS").get("txtEmail") }}
              <input class="ml-2" v-model="selectedItem.checkFinsh.email" type="checkbox" />
              FINISH MESSAGE</label
            >
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
        <color-picker :color="selectedItem.value" v-model="selectedItem.value"></color-picker>
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
          {{ $ml.with("VueJS").get("txtUpdate") }}
        </button>
      </div>
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
  name: "edit-item",

  data() {
    return {
      modalLg: "modal-lg",
    };
  },

  components: {
    Select2,
    ErrorItem,
    SuccessItem,
    Modal,
    ColorPicker,
  },

  computed: {
    ...mapGetters("types", {
      selectedItem: "selectedItem",
      validationErrors: "validationErrors",
      validationSuccess: "validationSuccess",
    }),

    ...mapGetters("departments", {
      deptOptions: "options",
    }),
  },

  methods: {
    ...mapActions("types", {
      resetValidate: "resetValidate",
      resetSelectedItem: "resetSelectedItem",
      updateItem: "updateItem",
    }),

    resetValidation() {
      this.resetValidate();
      this.resetSelectedItem();
    },

    // checkFinshMessage() {
    //     this.checkFinsh.email = this.selectedItem.email ? true : false;
    //     this.checkFinsh.lineroom = this.selectedItem.line_room ? true : false;
    // },
  },
  watch: {
    // selectedItem: [
    //   {
    //     handler: "checkFinshMessage",
    //   },
    // ],
  },
};
</script>