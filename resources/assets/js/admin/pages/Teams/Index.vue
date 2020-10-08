<template>
  <div class="content">
    <div class="container-fluid">
      <div class="form-group">
        <div class="row">
          <div class="col-12 col-sm-auto">
            <button-create>
              <template slot="title">{{ $ml.with("VueJS").get("txtCreateTeam") }}</template>
            </button-create>
          </div>
        </div>
      </div>

      <card class="strpied-tabled-with-hover">
        <template slot="header">
          <h4 class="card-title">
             {{ $ml.with("VueJS").get("txtTeamList") }}
          </h4>
        </template>
        
        <tbl-default
          :dataItems="data" 
          :dataCols="columns"
          dataAction="all"
          dataPath="teams"
        />

        <pagination
          :data="data"
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
    ...mapGetters("teams", {
      columns: "columns",
      data: "data",
    }),
  },

  methods: {
    ...mapActions("teams", {
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
<style lang="scss">
.type-color {
  width: 60px;
  height: 20px;
  display: inline-block;
  vertical-align: middle;
}
</style>
