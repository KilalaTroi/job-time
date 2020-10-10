<template>
  <card>
    <h4 slot="header" class="card-title">
      {{ $ml.with("VueJS").get("txtEditProfile") }}
    </h4>
    <form>
      <div class="row">
        <div class="col-md-6">
          <base-input
            type="text"
            :label="this.$ml.with('VueJS').get('txtName')"
            v-model="loginUser.name"
          >
          </base-input>
        </div>
        <div class="col-md-6">
          <base-input
            type="text"
            :label="this.$ml.with('VueJS').get('txtUsername')"
            v-model="loginUser.username"
          >
          </base-input>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <base-input
            type="text"
            :label="this.$ml.with('VueJS').get('txtEmail')"
            v-model="loginUser.email"
          >
          </base-input>
        </div>
        <div class="col-md-6">
          <label class>{{ $ml.with("VueJS").get("txtTeam") }}</label>

          <div>
            <multiselect
              :multiple="true"
              v-model="loginUser.team"
              :options="teamOptions"
              :clear-on-select="false"
              :preserve-search="false"
              :placeholder="$ml.with('VueJS').get('txtContactAdmin')"
              label="text"
              track-by="text"
              :preselect-first="true"
            ></multiselect>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <label class="">{{ $ml.with("VueJS").get("txtLang") }}</label>
          <select-2 v-model="loginUser.language" class="select2">
            <option value="vi">English</option>
            <option value="ja">Japanese</option>
          </select-2>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <base-input
            type="password"
            :label="this.$ml.with('VueJS').get('txtPassword')"
            v-model="loginUser.password"
          >
          </base-input>
        </div>
        <div class="col-md-6">
          <base-input
            type="password"
            :label="this.$ml.with('VueJS').get('txtRePassword')"
            v-model="loginUser.password_confirmation"
          >
          </base-input>
        </div>
      </div>
      <error-item :errors="validationErrors"></error-item>
      <success-item :success="validationSuccess"></success-item>
      <div class="text-center">
        <button
          type="submit"
          class="btn btn-info btn-fill float-right"
          @click="updateUser(loginUser)"
        >
          {{ $ml.with("VueJS").get("txtUpdate") }}
        </button>
      </div>
      <div class="clearfix"></div>
    </form>
  </card>
</template>

<script>
import Card from "../../components/Cards/Card.vue";
import ErrorItem from "../../components/Validations/Error";
import SuccessItem from "../../components/Validations/Success";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import Multiselect from "vue-multiselect";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "edit-profile",

  components: {
    Card,
    ErrorItem,
    SuccessItem,
    Select2,
    Multiselect,
  },

  data() {
    return {
      userID: document
        .querySelector("meta[name='user-id']")
        .getAttribute("content"),
    };
  },

  computed: {
    ...mapGetters({
      teamOptions: "teams/options",
      validationErrors: "users/validationErrors",
      validationSuccess: "users/validationSuccess",
      dateFormat: "dateFormat",
      getLangCode: "getLangCode",
      loginUser: "loginUser",
    }),
  },

  methods: {
    ...mapActions({
      resetValidate: "users/resetValidate",
      updateUser: "users/updateUser",
    }),

    resetValidation() {
      this.resetValidate();
      this.resetSelectedUser();
    },
  },
};
</script>

<style lang="scss">
@import "~vue-multiselect/dist/vue-multiselect.min.css";
</style>
