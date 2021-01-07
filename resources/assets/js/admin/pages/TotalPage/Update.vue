<template>
  <modal id="totalpageAction" sizeClasses="modal-lg">
    <!-- v-on:reset-validation="resetValidation" -->

    <template slot="title">Total Page</template>

    <div class="row">
      <div class="col-sm-4">
        <div class="form-group">
          <label class="">Date</label>
          <datepicker
            input-class="form-control"
            placeholder="Select Year"
            minimum-view="month"
            maximum-view="year"
            v-model="filters.date"
            :clear-button="true"
            format="yyyy/MM"
            :language="getLangCode(this.$ml)"
          >
          </datepicker>
        </div>
      </div>
    </div>
    <!-- <hr /> -->
    <div class="row">
      <div class="col-sm-4" v-for="(type, index) in options.types" :key="index">
        <div class="form-group">
          <label>{{ "ja" == currentLang ? type.slug_ja : type.slug_vi }}</label>
          <input
            type="number"
            class="form-control"
            min="0"
            v-model="selectedItem[type.id].page"
          />
        </div>
      </div>
    </div>

    <error-item :errors="validationErrors"></error-item>
    <success-item :success="validationSuccess"></success-item>
    <hr />
    <div class="form-group text-right">
      <!-- @click="addProject(selectedItem)" -->
      <button
        type="button"
        @click="updateItem(selectedItem)"
        class="btn btn-primary"
      >
        {{ $ml.with("VueJS").get("txtUpdate") }}
      </button>
    </div>
  </modal>
</template>
<script>
import Modal from "../../components/Modals/Modal";
import ErrorItem from "../../components/Validations/Error";
import SuccessItem from "../../components/Validations/Success";
import Datepicker from "vuejs-datepicker";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "total-update",
  components: {
    datepicker: Datepicker,
    ErrorItem,
    SuccessItem,
    Modal,
  },
  computed: {
    ...mapGetters({
      getLangCode: "getLangCode",
      currentLang: "currentLang",
    }),

    ...mapGetters("totalpage", {
      selectedItem: "selectedItem",
      filters: "filters",
      options: "options",
      validationErrors: "validationErrors",
      validationSuccess: "validationSuccess",
    }),
  },
  data() {
    return {
      filtersOld: {
        date: new Date(),
      },
    };
  },
  methods: {
    ...mapActions({
      getItem: "totalpage/getItem",
      getAll: "totalpage/getAll",
      updateItem: "totalpage/updateItem",
    }),
    hideModal() {
      const _this = this;
      $(document).on("hidden.bs.modal",'#totalpageAction', function (e) {
        if(_this.validationSuccess) location.reload();
      });
    },
  },
  async created() {
    const _this = this;
    _this.getAll();
    _this.getItem(_this.filters.date);
    _this.hideModal();
  },
  watch: {
    filters: [
      {
        handler: function (value) {
          if (value.date != this.filtersOld.date) {
            this.getItem(value.date);
            this.filtersOld.date = value.date;
          }
        },
        deep: true,
      },
    ],
  },
};
</script>