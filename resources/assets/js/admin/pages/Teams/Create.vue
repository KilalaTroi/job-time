<template>
  <modal id="itemCreate" :sizeClasses="modalLg" v-on:reset-validation="resetValidation">
    <template slot="title">{{ $ml.with("VueJS").get("txtCreateTeam") }}</template>
    <div class="form-group">
      <label class>{{$ml.with('VueJS').get('txtName')}}</label>
      <input v-model="selectedTeam.name" type="text" name="name" class="form-control" required />
    </div>
    <error-item :errors="validationErrors"></error-item>
    <success-item :success="validationSuccess"></success-item>
    <hr />
    <div class="form-group text-right">
      <button
        @click="createType(selectedTeam)"
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

  data() {
    return {
      modalLg: "modal-lg",
    };
  },

  components: {
    Modal,
    ErrorItem,
    SuccessItem,
  },

  computed: {
    ...mapGetters('teams',{
      selectedTeam: "selectedTeam",
      validationErrors: "validationErrors",
      validationSuccess: "validationSuccess",
    }),
  },

  methods: {
    ...mapActions('teams',{
      resetValidate: "resetValidate",
      resetSelectedTeam: "resetSelectedTeam",
      createType: "createTeam",
    }),

    resetValidation() {
      this.resetValidate();
      this.resetSelectedTeam();
    },
  },
};
</script>