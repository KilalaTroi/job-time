<template>
  <modal id="itemDetail" :sizeClasses="modalLg" v-on:reset-validation="resetValidation">
    <template slot="title">{{$ml.with('VueJS').get('txtEditType')}}</template>
    <div v-if="selectedType">
      <div class="form-group">
        <label class>{{$ml.with('VueJS').get('txtSlug')}}</label>
        <input v-model="selectedType.slug" type="text" name="slug" class="form-control" required />
      </div>
      <div class="row">
        <div class="col-sm-6">
          <div class="form-group">
            <label class>{{$ml.with('VueJS').get('txtNameVi')}}</label>
            <input v-model="selectedType.slug_vi" type="text" name="slug_vi" class="form-control" />
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class>{{$ml.with('VueJS').get('txtNameJa')}}</label>
            <input v-model="selectedType.slug_ja" type="text" name="slug_ja" class="form-control" />
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-6">
          <div class="form-group">
            <label class>{{$ml.with('VueJS').get('txtDescVi')}}</label>
            <textarea
              v-model="selectedType.description_vi"
              name="description_vi"
              class="form-control"
            ></textarea>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class>{{$ml.with('VueJS').get('txtDescJa')}}</label>
            <textarea
              v-model="selectedType.description_ja"
              name="description_ja"
              class="form-control"
            ></textarea>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class>{{$ml.with('VueJS').get('txtColor')}}</label>
        <color-picker :color="selectedType.value" v-model="selectedType.value"></color-picker>
      </div>
      <error-item :errors="validationErrors"></error-item>
      <success-item :success="validationSuccess"></success-item>
      <hr />
      <div class="form-group text-right">
        <button
          @click="updateType(selectedType)"
          type="button"
          class="btn btn-primary"
        >{{$ml.with('VueJS').get('txtUpdate')}}</button>
      </div>
    </div>
  </modal>
</template>

<script>
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
    ErrorItem,
    SuccessItem,
    Modal,
    ColorPicker,
  },

  computed: {
    ...mapGetters({
      selectedType: "types/selectedType",
      validationErrors: "types/validationErrors",
      validationSuccess: "types/validationSuccess",
    }),
  },
  
  methods: {
    ...mapActions({
      resetValidate: "types/resetValidate",
      resetSelectedType: "types/resetSelectedType",
      updateType: "types/updateType",
    }),

    resetValidation() {
      this.resetValidate();
      this.resetSelectedType();
    },
  },
};
</script>