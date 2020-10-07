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
        
        <table-1
          :dataItems="items"
          :dataCols="columns"
          dataAction="all"
          dataPath="teams"
        />

        <pagination
          :data="items"
          :show-disabled="true"
          :limit="2"
          align="right"
          size="small"
          @pagination-change-page="getItems"
        />
      </card>
      <create-item />
      <edit-item />
    </div>
  </div>
</template>
<script>
import Table1 from "../../components/Table";
import Card from "../../components/Cards/Card";
import CreateItem from "./Create";
import EditItem from "./Edit";
import ButtonCreate from "../../components/Buttons/Create";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    Table1,
    Card,
    CreateItem,
    EditItem,
    ButtonCreate,
  },
  computed: {
    ...mapGetters("teams", {
      columns: "columns",
      items: "items",
    }),
  },

  methods: {
    ...mapActions("teams", {
      setColumns: "setColumns",
      getItems: "getAll",
    }),
  },

  mounted() {
    const _this = this;
    _this.setColumns();
    _this.getItems();
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
