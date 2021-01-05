<template>
  <div class="content projects">
    <div class="container-fluid">
      <card>
        <template slot="header">
          <div class="d-flex justify-content-between">
            <h4 class="card-title">{{ $ml.with("VueJS").get("txtFilter") }}</h4>
          </div>
        </template>
        <div class="row">
          <div class="col-sm-5">
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group">
                  <label class="">{{
                    $ml.with("VueJS").get("txtKeyword")
                  }}</label>
                  <input
                    v-model="filters.keyword"
                    :placeholder="$ml.with('VueJS').get('txtKeyword')"
                    type="text"
                    class="form-control"
                    v-on:keyup="getAllProject"
                    v-on:keyup.enter="getAllProject"
                  />
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group">
                  <label class="">{{ $ml.with("VueJS").get("txtTeam") }}</label>
                  <div>
                    <select-2
                      :options="currentTeamOption"
                      v-model="filters.team"
                      class="select2"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-sm-5">
            <div class="row">
              <div class="col-sm-6">
                <div class="form-group">
                  <label class="">{{
                    $ml.with("VueJS").get("txtTypes")
                  }}</label>
                  <div>
                    <select2-type
                      :options="typeOptions"
                      v-model="filters.type_id"
                      class="select2"
                    ></select2-type>
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="form-group">
                  <label class="">{{
                    $ml.with("VueJS").get("txtDepartments")
                  }}</label>
                  <div>
                    <select-2
                      :options="deptOptions"
                      v-model="filters.dept_id"
                      class="select2"
                    ></select-2>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-sm-2 align-self-end">
            <div class="form-group d-flex">
              <button
                class="btn btn-primary btn-block"
                @click="getAllProject(1)"
              >
                <i class="fa fa-search"></i>
                Search
              </button>
              <button
                class="btn btn-secondary btn-block mt-0 ml-2"
                @click="resetFilter"
              >
                <i class="fa fa fa-refresh"></i>
                Reset
              </button>
            </div>
          </div>
        </div>
      </card>
      <div class="form-group">
        <div class="row">
          <div class="col-12 col-sm-auto">
            <button-create>
              <template slot="title">{{
                $ml.with("VueJS").get("txtCreateProject")
              }}</template>
            </button-create>
          </div>
          <div class="col-12 col-sm-auto ml-auto">
            <button-view-table-option class="mt-0 mr-3" />
            <button
              type="button"
              class="btn btn-primary mr-3"
              data-toggle="modal"
              data-target="#issueImport"
              data-backdrop="static"
              data-keyboard="false"
            >
              <i class="fa fa-plus"></i>
              {{ $ml.with("VueJS").get("txtImportIssue") }}
            </button>
            <button
              type="button"
              class="btn btn-danger"
              data-toggle="modal"
              data-target="#issueCreate"
              data-backdrop="static"
              data-keyboard="false"
            >
              <i class="fa fa-plus"></i>
              {{ $ml.with("VueJS").get("txtAddIssue") }}
            </button>
          </div>
        </div>
      </div>
      <card class="strpied-tabled-with-hover">
        <template slot="header">
          <div class="d-flex justify-content-between">
            <h4 class="card-title">
              {{ $ml.with("VueJS").get("txtProjectsList") }}
            </h4>
            <div class="align-self-end d-flex align-items-center">
              <button
                v-show="flagCheck"
                type="button"
                class="btn btn-xs btn-second"
                @click="
                  archiveAllItem({
                    issues: checkItem,
                    status: !filters.showArchive,
                  })
                "
              >
                <i
                  aria-hidden="true"
                  title="archive"
                  :class="{
                    'fa fa-archive': !filters.showArchive,
                    'fa fa-unlock': filters.showArchive,
                  }"
                ></i>
              </button>
              <button
                v-show="flagCheck"
                type="button"
                class="btn btn-xs btn-danger mx-sm-3"
                @click="deleteAllItem(checkItem)"
              >
                <i aria-hidden="true" class="fa fa-trash"></i>
              </button>
              <base-checkbox
                @input="getAllProject(1)"
                v-model="filters.showArchive"
                >{{ $ml.with("VueJS").get("txtViewArchive") }}</base-checkbox
              >
            </div>
          </div>
        </template>
        <div class="table-responsive" :class="{'path-team': filters.team == 2 || filters.team == 3}">
          <table-project
            class="table-hover table-striped"
            v-on:check-item="showhideActionAll"
          >
          </table-project>
          <div v-if="!projectData.data" class="text-center mt-3">
            <img src="https://i.imgur.com/JfPpwOA.gif" />
          </div>
        </div>
        <pagination
          :data="projectData"
          :show-disabled="true"
          :limit="2"
          align="right"
          size="small"
          @pagination-change-page="getAllProject"
        ></pagination>
      </card>
      <view-table-option
        dataTable="project"
        :dataItems="projectData"
        :dataCols="columns"
      />
      <create-item />
      <edit-project />
      <edit-issue />
      <add-issue />
      <import-issue v-on:reset-import="resetImport" />
    </div>
  </div>
</template>

<script>
import Card from "../../components/Cards/Card";
import CreateItem from "./Create";
import EditProject from "./EditProject";
import EditIssue from "./EditIssue";
import ImportIssue from "./ImportIssue";
import AddIssue from "./AddIssue";
import ButtonCreate from "../../components/Buttons/Create";
import TableProject from "../../components/TableProject";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import Select2Type from "../../components/SelectTwo/SelectTwoType.vue";
import ButtonViewTableOption from "../../components/Buttons/ViewTableOption";
import ViewTableOption from "../../components/ModalViewTableOption";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    Select2,
    Select2Type,
    Card,
    CreateItem,
    EditProject,
    EditIssue,
    AddIssue,
    ButtonCreate,
    TableProject,
    ImportIssue,
    ButtonViewTableOption,
    ViewTableOption,
  },
  computed: {
    ...mapGetters({
      currentTeamOption: "currentTeamOption",
      currentTeam: "currentTeam",
      deptOptions: "departments/options",
      typeOptions: "types/options",
      filters: "projects/filters",
      projectData: "projects/data",
      columns: "projects/columns",
      validationErrors: "projects/validationErrors",
      validationSuccess: "projects/validationSuccess",
    }),
  },
  data() {
    return {
      flagCheck: false,
      checkItem: [],
    };
  },
  methods: {
    ...mapActions({
      getOptionsDept: "departments/getOptions",
      getOptionsType: "types/getOptions",
      setColumns: "projects/setColumns",
      getAllProject: "projects/getAll",
      deleteAllItem: "projects/deleteAllItem",
      archiveAllItem: "projects/archiveAllItem",
      setCurrentTeam: 'setCurrentTeam'
    }),

    showhideActionAll(data) {
      this.flagCheck = false;
      if (data.length > 0) this.flagCheck = true;
      this.checkItem = data;
    },

    resetFilter() {
      this.filters.keyword = "";
      this.filters.team = this.currentTeam.id;
      this.filters.type_id = 0;
      this.filters.dept_id = 0;
      this.filters.showArchive = false;
      this.getAllProject(1);
    },

    resetImport() {
      this.getAllProject(1);
    },
  },
  async created() {
    let _this = this;
    _this.setColumns();
    if (!_this.deptOptions.length) await _this.getOptionsDept(true);
    if (!_this.typeOptions.length) await _this.getOptionsType(true);
    _this.filters.team = _this.currentTeam.id;
    await _this.getAllProject();
    $(document).on("click", ".languages button", function () {
      _this.setColumns();
    });
  },

  watch: {
    filters: [
      {
        handler: function (value) {
          if ( value.team != this.currentTeam.id ) {
            this.setCurrentTeam(value.team);
          }
        },
        deep: true
      },
    ],
  },
};
</script>

<style lang="scss">
@import "~vue-multiselect/dist/vue-multiselect.min.css";
.type-color {
  width: 60px;
  height: 20px;
  display: inline-block;
  vertical-align: middle;
}
.table-responsive.path-team {
    .year-of-issue {
        display: none;
    }
}
</style>