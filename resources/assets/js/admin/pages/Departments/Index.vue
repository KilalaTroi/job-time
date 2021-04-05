<template>
  <div class="content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="my-0 mb-1">
            <b>{{ $ml.with("VueJS").get("sbSettings") }}</b>
          </h4>
          <div class="lsub-menu">
            <router-link class="active" to="/departments">{{ $ml.with("VueJS").get("sbDepartments") }}</router-link>
            <router-link class="" to="/teams">{{ $ml.with("VueJS").get("sbTeams") }}</router-link>
          </div>
        </div>
        <div class="form-group mb-0">
          <div class="row">
            <div class="col-12 col-sm-auto">
              <button-create>
                <template slot="title">{{
                  $ml.with("VueJS").get("txtCreateDept")
                }}</template>
              </button-create>
            </div>
          </div>
        </div>
      </div>

      <card class="strpied-tabled-with-hover">
        <template slot="header">
          <h4 class="card-title">{{ $ml.with("VueJS").get("txtDeptList") }}</h4>
        </template>
        <tbl-default
          :dataItems="departmentData"
          :dataCols="columns"
          dataAction="all"
          dataPath="departments"
        />
        <div v-if="!departmentData.data" class="text-center mt-3">
          <img src="https://i.imgur.com/JfPpwOA.gif" />
        </div>
        <pagination
          :data="departmentData"
          :show-disabled="true"
          :limit="2"
          align="right"
          size="small"
          @pagination-change-page="getAll"
        />
      </card>
      <create-item />
      <edit-item />
    </div>
  </div>
</template>

<script>
import TblDefault from "../../components/Table";
import Card from "../../components/Cards/Card";
import CreateItem from "./Create";
import EditItem from "./Edit";
import ButtonCreate from "../../components/Buttons/Create";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    TblDefault,
    Card,
    CreateItem,
    EditItem,
    ButtonCreate,
  },

  computed: {
    ...mapGetters("departments", {
      columns: "columns",
      departmentData: "data",
    }),
  },

  methods: {
    ...mapActions("departments", {
      setColumns: "setColumns",
      getAll: "getAll",
    }),
  },

  mounted() {
    const _this = this;
    _this.setColumns();
    _this.getAll();
    $(document).on("click", ".languages button", function () {
      _this.setColumns();
    });
  },
};
</script>