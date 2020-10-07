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
      <label class>{{ $ml.with("VueJS").get("txtSlug") }}</label>
      <input
        v-model="selectedType.slug"
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
            v-model="selectedType.slug_vi"
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
            v-model="selectedType.slug_ja"
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
            v-model="selectedType.dept_id"
            class="select2"
          >
            <option disabled value="0">
              {{ $ml.with("VueJS").get("txtSelectDept") }}
            </option>
          </select-2>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="form-group">
          <label class>{{ $ml.with("VueJS").get("txtLineRoom") }}</label>
          <input
            v-model="selectedType.line_room"
            type="text"
            name="line_room"
            class="form-control"
          />
        </div>
      </div>
    </div>
    <div class="form-group">
      <label class>{{ $ml.with("VueJS").get("txtColor") }}</label>
      <color-picker
        :color="selectedType.value"
        v-model="selectedType.value"
      ></color-picker>
    </div>
    <error-item :errors="validationErrors"></error-item>
    <success-item :success="validationSuccess"></success-item>
    <hr />
    <div class="form-group text-right">
      <button
        @click="createType(selectedType)"
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
    ...mapGetters("types", {
      selectedType: "selectedType",
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
      resetSelectedType: "resetSelectedType",
      createType: "createType",
    }),

    ...mapActions("departments", {
      getDeptOptions: "getOptions",
    }),

    resetValidation() {
      this.resetValidate();
      this.resetSelectedType();
    },
  },

  mounted() {
    const _this = this;
    _this.getDeptOptions();
  },
};
</script>