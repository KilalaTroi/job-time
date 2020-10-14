<template>
  <modal id="itemCreate" v-on:reset-validation="resetValidation">
    <template slot="title">{{$ml.with('VueJS').get('txtCreateDept')}}</template>
    <div class="form-group">
      <label class>{{$ml.with('VueJS').get('txtName')}}</label>
      <input
        v-model="selectedItem.name"
        type="text"
        name="name"
        class="form-control"
        required
      />
    </div>
    <div class="form-group">
      <label class>{{$ml.with('VueJS').get('txtNameVi')}}</label>
      <input v-model="selectedItem.name_vi" type="text" name="name_vi" class="form-control" />
    </div>
    <div class="form-group">
      <label class>{{$ml.with('VueJS').get('txtNameJa')}}</label>
      <input v-model="selectedItem.name_ja" type="text" name="name_ja" class="form-control" />
    </div>
    <error-item :errors="validationErrors"></error-item>
    <success-item :success="validationSuccess"></success-item>
    <hr />
    <div class="form-group text-right">
      <button
        @click="createItem(selectedItem)"
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
    ...mapGetters('departments', {
      selectedItem: "selectedItem",
      validationErrors: "validationErrors",
      validationSuccess: "validationSuccess",
    }),
  },

  methods: {
    ...mapActions('departments', {
      resetValidate: "resetValidate",
      resetSelectedItem: "resetSelectedItem",
      createItem: "createItem",
    }),

    resetValidation() {
      this.resetValidate()
      this.resetSelectedItem()
    },
  },
};
</script>