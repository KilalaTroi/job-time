<template>
  <div class="content">
    <div class="container-fluid">
      <div class="form-group">
        <div class="row">
          <div class="col-12">
            <button-create>
              <template slot="title">{{
                $ml.with("VueJS").get("txtCreateType")
              }}</template>
            </button-create>
             <button-view-table-option class="mt-0 float-right" />
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
      <view-table-option dataTable="types" :dataItems="typeData" :dataCols="columns" />

    </div>
  </div>
</template>
<script>
import TblDefault from "../../components/Table";
import Card from "../../components/Cards/Card";
import CreateItem from "./Create";
import EditItem from "./Edit";
import ButtonCreate from "../../components/Buttons/Create";
import ButtonViewTableOption from "../../components/Buttons/ViewTableOption";
import ViewTableOption from "../../components/ModalViewTableOption";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    TblDefault,
    Card,
    CreateItem,
    ViewTableOption,
    EditItem,
    ButtonCreate,
    ButtonViewTableOption,
  },
  computed: {
    ...mapGetters("types", {
      columns: "columns",
      typeData: "data",
      deptOptions: "options"
    }),
  },

  methods: {
    ...mapActions("departments", {
      getOtionsDept: "getOptions",
    }),

    ...mapActions("types", {
      setColumns: "setColumns",
      getAll: "getAll",
    })
  },

  async created(){
    const _this = this;
    _this.setColumns();
    if ( !_this.deptOptions.length ) await _this.getOtionsDept(true);
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
