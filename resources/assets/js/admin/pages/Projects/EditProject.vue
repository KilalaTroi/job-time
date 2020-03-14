<template>
  <modal id="editProject" :sizeClasses="modalLg" v-on:reset-validation="$emit('reset-validation')">
    <template slot="title">
      {{$ml.with('VueJS').get('txtEditProject')}}     
    </template>
    <div v-if="currentItem">
      <div class="row">
        <div class="col-sm-6">
          <div class="form-group">
            <label class>
              {{$ml.with('VueJS').get('txtTypes')}}              
            </label>
            <div>
              <select2-type :options="typeOptions" v-model="currentItem.type_id" class="select2">
                <option disabled value="0">Select one</option>
              </select2-type>
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            <label class>{{$ml.with('VueJS').get('txtDepartments')}}</label>
            <div>
              <select-2 :options="departments" v-model="currentItem.dept_id" class="select2">
                <option disabled value="0">Select one</option>
              </select-2>
            </div>
          </div>
        </div>
      </div>
      <hr />
      <div class="row">
        <div class="col-sm-4">
          <div class="form-group">
            <label class>{{$ml.with('VueJS').get('txtName')}}</label>
            <input
              v-model="currentItem.p_name"
              type="text"
              name="name"
              class="form-control"
              required
            />
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
            <label class>{{$ml.with('VueJS').get('txtNameVi')}}</label>
            <input v-model="currentItem.p_name_vi" type="text" name="name_vi" class="form-control" />
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
            <label class>{{$ml.with('VueJS').get('txtNameJa')}}</label>
            <input v-model="currentItem.p_name_ja" type="text" name="name_ja" class="form-control" />
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <div class="form-group">
            <label class>{{$ml.with('VueJS').get('txtLineRoomId')}}</label>
            <input v-model="currentItem.room_id" type="text" name="room_id" class="form-control" />
          </div>
        </div>
      </div>
      <error-item :errors="errors"></error-item>
      <success-item :success="success"></success-item>
      <hr />
      <div class="form-group text-right">
        <button
          @click="$emit('update-project', currentItem)"
          type="button"
          class="btn btn-primary"
        >{{$ml.with('VueJS').get('txtUpdate')}}</button>
      </div>
    </div>
  </modal>
</template>
<script>
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import Select2Type from "../../components/SelectTwo/SelectTwoType.vue";
import Modal from "../../components/Modals/Modal";
import ErrorItem from "../../components/Validations/Error";
import SuccessItem from "../../components/Validations/Success";

export default {
  name: "EditProject",
  components: {
    Select2,
    Select2Type,
    ErrorItem,
    SuccessItem,
    Modal
  },
  props: ["currentItem", "departments", "types", "errors", "success"],
  data() {
    return {
      departmentOptions: [],
      typeOptions: [],
      modalLg: "modal-lg"
    };
  },
  methods: {
    getDataTypes(data) {
      if (data.length) {
        let dataTypes = [];
        let obj = {
          id: 0,
          text: "<div>Select one</div>"
        };
        dataTypes.push(obj);

        for (let i = 0; i < data.length; i++) {
          let obj = {
            id: data[i].id,
            text:
              '<div><span class="type-color" style="background: ' +
              data[i].value +
              '"></span>' +
              data[i].slug +
              "</div>"
          };
          dataTypes.push(obj);
        }
        this.typeOptions = dataTypes;
      }
    }
  },
  watch: {
    types: [
      {
        handler: "getDataTypes"
      }
    ]
  }
};
</script>