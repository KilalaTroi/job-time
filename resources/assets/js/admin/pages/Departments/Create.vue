<template>
  <modal id="itemCreate" v-on:reset-validation="resetValidation">
    <template slot="title">{{$ml.with('VueJS').get('txtCreateDept')}}</template>
    <div class="form-group">
      <label class>{{$ml.with('VueJS').get('txtName')}}</label>
      <input
        v-model="selectedDepartment.name"
        type="text"
        name="name"
        class="form-control"
        required
      />
    </div>
    <div class="form-group">
      <label class>{{$ml.with('VueJS').get('txtNameVi')}}</label>
      <input v-model="selectedDepartment.name_vi" type="text" name="name_vi" class="form-control" />
    </div>
    <div class="form-group">
      <label class>{{$ml.with('VueJS').get('txtNameJa')}}</label>
      <input v-model="selectedDepartment.name_ja" type="text" name="name_ja" class="form-control" />
    </div>
    <error-item :errors="validationErrors"></error-item>
    <success-item :success="validationSuccess"></success-item>
    <hr />
    <div class="form-group text-right">
      <button
        @click="createDepartment(selectedDepartment)"
        type="button"
        class="btn btn-primary"
      >{{$ml.with('VueJS').get('txtCreate')}}</button>
    </div>
  </modal>
</template>

<script>
import ErrorItem from "../../components/Validations/Error";
import SuccessItem from "../../components/Validations/Success";
import Modal from "../../components/Modals/Modal";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "create-item",
  components: {
    Modal,
    ErrorItem,
    SuccessItem,
  },
  computed: {
    ...mapGetters({
      selectedDepartment: "departments/selectedDepartment",
      validationErrors: "departments/validationErrors",
      validationSuccess: "departments/validationSuccess",
    }),
  },

  methods: {
    ...mapActions({
      resetValidate: "departments/resetValidate",
      resetSelectedDepartment: "departments/resetSelectedDepartment",
      createDepartment: "departments/createDepartment",
    }),

    resetValidation() {
      this.resetValidate()
      this.resetSelectedDepartment()
    },
  },
};
</script>