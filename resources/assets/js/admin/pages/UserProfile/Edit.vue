<template>
  <modal
    id="itemDetail"
    :sizeClasses="modalLg"
    v-on:reset-validation="resetValidation"
  >
    <template slot="title">{{ $ml.with("VueJS").get("txtEditUser") }}</template>
    <div v-if="selectedUser">
      <div class="row">
        <div class="col-sm-6">
          <div class="form-group">
            <label class="">{{ $ml.with("VueJS").get("txtName") }}</label>
            <input
              v-model="selectedUser.name"
              type="text"
              class="form-control"
            />
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class="">{{ $ml.with("VueJS").get("txtUsername") }}</label>
            <input
              v-model="selectedUser.username"
              type="text"
              class="form-control"
            />
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class="">{{ $ml.with("VueJS").get("txtEmail") }}</label>
            <input
              v-model="selectedUser.email"
              type="email"
              class="form-control"
            />
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class>{{ $ml.with("VueJS").get("txtTeam") }}</label>
            <div>
              <multiselect
                :multiple="false"
                v-model="selectedUser.team"
                :options="options.teams"
                :clear-on-select="false"
                :preserve-search="false"
                :placeholder="$ml.with('VueJS').get('txtSelectOne')"
                label="text"
                track-by="text"
              ></multiselect>
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class="">{{ $ml.with("VueJS").get("txtRole") }}</label>
            <div>
              <select-2
                :options="roleOptions"
                v-model="selectedUser.r_name"
                class="select2"
              >
                <option disabled value="0">Select role</option>
              </select-2>
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class="">{{ $ml.with("VueJS").get("txtLang") }}</label>
            <select-2 v-model="selectedUser.language" class="select2">
              <option value="vi">Vietnamese</option>
              <option value="ja">Japanese</option>
            </select-2>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class="">CheckInOut user id</label>
            <input
              v-model="selectedUser.checkinout_user_id"
              type="number"
              class="form-control"
            />
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class="">Work date</label>
            <datepicker
                input-class="form-control"
                :placeholder="$ml.with('VueJS').get('txtSelectDate')"
                v-model="selectedUser.work_date"
                :format="customFormatter"
                :language="getLangCode(this.$ml)"
              >
            </datepicker>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class="">Disable date</label>
            <datepicker
              name="disable_date"
              input-class="form-control"
              :placeholder="$ml.with('VueJS').get('txtSelectDate')"
              v-model="selectedUser.disable_date"
              :format="customFormatter"
              :language="getLangCode(this.$ml)"
            >
            </datepicker>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class="">Profile</label>
            <multiselect
              :multiple="false"
              v-model="selectedUser.profile"
              :options="options.profiles"
              :clear-on-select="false"
              :searchable="false"
              :placeholder="$ml.with('VueJS').get('txtSelectOne')"
              label="text"
              track-by="text"
            ></multiselect>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class="">{{ $ml.with("VueJS").get("txtPassword") }}</label>
            <input
              v-model="selectedUser.password"
              type="password"
              name="password"
              class="form-control"
            />
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class="">{{ $ml.with("VueJS").get("txtRePassword") }}</label>
            <input
              v-model="selectedUser.password_confirmation"
              type="password"
              name="password_confirmation"
              class="form-control"
            />
          </div>
        </div>
      </div>

      <error-item :errors="validationErrors"></error-item>
      <success-item :success="validationSuccess"></success-item>
      <hr />
      <div class="form-group text-right">
        <button
          @click="updateUser(selectedUser)"
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
import Modal from "../../components/Modals/Modal";
import ErrorItem from "../../components/Validations/Error";
import SuccessItem from "../../components/Validations/Success";
import Datepicker from "vuejs-datepicker";
import Multiselect from "vue-multiselect";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "edit-item",

  components: {
    Select2,
    ErrorItem,
    SuccessItem,
    Modal,
    Datepicker,
    Multiselect,
  },

  props: ["options"],

  data() {
    return {
      modalLg: "modal-lg",
    };
  },

  computed: {
    ...mapGetters({
      roleOptions: "users/roleOptions",
      selectedUser: "users/selectedUser",
      validationErrors: "users/validationErrors",
      validationSuccess: "users/validationSuccess",
      dateFormat: "dateFormat",
      getLangCode: "getLangCode",
    }),
  },

  methods: {
    ...mapActions({
      resetValidate: "users/resetValidate",
      resetSelectedUser: "users/resetSelectedUser",
      updateUser: "users/updateUser",
    }),

    customFormatter(date) {
      return this.dateFormat(date, "YYYY/MM/DD");
    },

    resetValidation() {
      this.resetValidate();
      this.resetSelectedUser();
    },
  },
};
</script>
