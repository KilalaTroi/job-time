<template>
  <div class="content">
    <div class="container-fluid">
      <div class="form-group">
        <div class="row">
          <div class="col-12 col-sm-auto">
            <button-create>
              <template slot="title">{{$ml.with('VueJS').get('txtCreateDept')}}</template>
            </button-create>
          </div>
        </div>
      </div>

      <card class="strpied-tabled-with-hover">
        <template slot="header">
          <h4 class="card-title">{{$ml.with('VueJS').get('txtDeptList')}}</h4>
        </template>
        <div class="table-responsive">
          <table-department class="table-hover table-striped" />
        </div>
      </card>
      <create-item />
      <edit-item />
    </div>
  </div>
</template>

<script>
import TableDepartment from "../../components/TableDepartment";
import Card from "../../components/Cards/Card";
import CreateItem from "./Create";
import EditItem from "./Edit";
import ButtonCreate from "../../components/Buttons/Create";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    TableDepartment,
    Card,
    CreateItem,
    EditItem,
    ButtonCreate,
  }
};
</script>
