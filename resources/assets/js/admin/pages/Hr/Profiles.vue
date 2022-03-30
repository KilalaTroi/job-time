<template>
  <div class="content" id="hrprofile">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="my-0 mb-1">
            <b>Human Resources Management</b>
          </h4>
          <div class="lsub-menu">
            <router-link class="active" to="/hrprofiles">Profiles</router-link>
            <!-- <router-link class="" to="/teams">{{
              $ml.with("VueJS").get("sbTeams")
            }}</router-link> -->
          </div>
        </div>
        <div class="form-group mb-0">
          <div class="row">
            <div class="col-12 col-sm-auto">
              <button-create>
                <template slot="title">Create Profile</template>
              </button-create>
            </div>
          </div>
        </div>
      </div>
      <card class="strpied-tabled-with-hover">
        <template slot="header">
          <div class="d-flex justify-content-between">
            <h4 class="card-title">Profiles</h4>
          </div>
        </template>
        <tbl-default
          :dataItems="data"
          :dataCols="columns"
          dataAction="all"
          dataPath="hrprofiles"
        />

        <div v-if="!data.data" class="text-center mt-3">
          <img src="/images/loading.gif">
        </div>

        <pagination
          :data="data"
          :show-disabled="true"
          :limit="2"
          align="right"
          size="small"
          @pagination-change-page="getAll"
        />
      </card>
      <create-profile />
      <edit-profile />
    </div>
  </div>
</template>

<script>
import TblDefault from "../../components/Table";
import Multiselect from "vue-multiselect";
import { mapGetters, mapActions } from "vuex";
import ButtonCreate from "../../components/Buttons/Create";
import CreateProfile from "./CreateProfile";
import EditProfile from "./EditProfile";

export default {
  components: {
    TblDefault,
    Multiselect,
    CreateProfile,
    EditProfile,
    ButtonCreate,
  },

  computed: {
    ...mapGetters("hrprofiles", {
      columns: "columns",
      data: "data",
      filters: "filters",
      options: "options",
    }),

    ...mapGetters({
      getLanguage: "getLanguage",
      getLangCode: "getLangCode",
      loginUser: "loginUser",
      currentFullTeamOption: "currentFullTeamOption",
      currentTeam: "currentTeam",
    }),
  },

  data() {
    return {};
  },

  methods: {
    ...mapActions({
      setCurrentTeam: "setCurrentTeam",
    }),
    ...mapActions("hrprofiles", {
      setColumns: "setColumns",
      getAll: "getAll",
      getOptions: "getOptions",
      // resetFilter: "resetFilter",
    }),
  },

  async created() {
    const _this = this;
    // _this.resetFilter();
    _this.setColumns();
    await _this.getAll();
    // _this.options.teams = [{ id: "", text: "ALL" }].concat(
    //   _this.currentFullTeamOption
    // );
    // _this.filters.team_id = _this.filtersAll.team_id = _this.currentTeam.id;
    // _this.setTeam();
    // _this.getOptions();
    $(document).on("click", ".languages button", function () {
      _this.setColumns();
    });
  },

  watch: {
    // filters: [
    //   {
    //     handler: function (value) {
    //       const _this = this;
    //     },
    //     deep: true,
    //   },
    // ],
  },
};
</script>
<style lang="scss">
// @import "~vue-multiselect/dist/vue-multiselect.min.css";
#hrprofile {
  tbody {
    .cl-havatar:not(:empty) {
      display: inline-flex;
      width: 100%;
      align-items: center;
      justify-content: center;
    }
  }
}
</style>