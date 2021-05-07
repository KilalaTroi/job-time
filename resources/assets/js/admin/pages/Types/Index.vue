<template>
  <div class="content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="my-0 mb-1">
            <b>{{ $ml.with("VueJS").get("sbSettings") }}</b>
          </h4>
          <div class="lsub-menu">
            <router-link class="" to="/settings/departments">{{ $ml.with("VueJS").get("sbDepartments") }}</router-link>
            <router-link class="" to="/settings/teams">{{ $ml.with("VueJS").get("sbTeams") }}</router-link>
            <router-link class="active" to="/settings/types">{{ $ml.with("VueJS").get("sbJobTypes") }}</router-link>
          </div>
        </div>
        <div class="form-group mb-0">
          <div class="row">
            <div class="col-12 col-sm-auto">
              <button-create>
                <template slot="title">{{
                  $ml.with("VueJS").get("txtCreateType")
                }}</template>
              </button-create>
            </div>
          </div>
        </div>
      </div>
      <card class="strpied-tabled-with-hover">
        <template slot="header">
          <h4 class="card-title">
            {{ $ml.with("VueJS").get("txtJobTypeList") }}
          </h4>
        </template>

        <tbl-default
          :dataItems="typeData"
          :dataCols="columns"
          dataAction="all"
          dataPath="types"
        />

        <div v-if="!typeData.data" class="text-center mt-3">
          <img src="https://i.imgur.com/JfPpwOA.gif">
        </div>

        <pagination
          :data="typeData"
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
    ...mapGetters({
      columns: "types/columns",
      typeData: "types/data"
    }),
  },

  methods: {
    ...mapActions("types", {
      setColumns: "setColumns",
      getAll: "getAll",
    })
  },

  async created(){
    const _this = this;
    _this.setColumns();

    await _this.getAll();

    $(document).on("click", ".languages button", function () {
      _this.setColumns();
    });
  },
};
</script>
<style lang="scss">
.type-color {
  width: 60px;
  height: 20px;
  display: inline-block;
  vertical-align: middle;
}
</style>
