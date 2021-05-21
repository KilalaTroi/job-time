<template>
  <div id="timetable" class="content">
    <div class="container-fluid">
      <card class="mb-0">
        <template slot="header">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h4 class="card-title my-0 mb-1">
                {{ $ml.with("VueJS").get("sbAttendance") }}
              </h4>
              <nav>
                <div class="nav" id="nav-tab" role="tablist">
                  <router-link to="/checkinout#table-tab-calendar">{{
                    $ml.with("VueJS").get("txtCalendar")
                  }}</router-link>
                  <router-link class="active" to="/checkinout/timetable">{{
                    $ml.with("VueJS").get("txtShiftsManagement")
                  }}</router-link>
                </div>
              </nav>
            </div>
          </div>
        </template>
      </card>

      <div class="row">
        <div class="col-sm-4">
          <card class="strpied-tabled-with-hover">
            <div class="col-12 col-sm-auto">
              <button
                type="button"
                class="btn btn-primary"
                data-toggle="modal"
                data-target="#timeTableCreate"
                data-backdrop="static"
                data-keyboard="false"
              >
                <i class="fa fa-plus"></i>
                <slot name="title">{{
                  $ml.with("VueJS").get("txtCreate")
                }}</slot>
              </button>
            </div>
            <template slot="header">
              <h4 class="card-title">
                {{ $ml.with("VueJS").get("txtTimetable") }}
              </h4>
            </template>
            <tbl-default
              :dataItems="data.timetable"
              :dataCols="columns.timetable"
              dataAction="edit-delete"
              dataPath="checkinout"
            >
              <template v-slot:action="slotAction">
                <button
                  @click="getItemt(slotAction.item.id)"
                  type="button"
                  class="btn btn-xs btn-default"
                  data-toggle="modal"
                  data-target="#timeTableDetail"
                  data-backdrop="static"
                  data-keyboard="false"
                >
                  <i class="fa fa-pencil" aria-hidden="true"></i>
                </button>
                <button
                  type="button"
                  class="btn btn-xs btn-danger ml-sm-2"
                  @click="
                    deleteItemt({
                      msgText: $ml.with('VueJS').get('msgConfirmDelete'),
                      id: slotAction.item.id,
                    })
                  "
                >
                  <i class="fa fa-trash" aria-hidden="true"></i>
                </button>
              </template>
            </tbl-default>
            <pagination
              :data="data.timetable"
              :show-disabled="true"
              :limit="2"
              align="right"
              size="small"
              @pagination-change-page="getAll"
            />
            <div v-if="!data.timetable.data" class="text-center mt-3">
              <img src="https://i.imgur.com/JfPpwOA.gif" />
            </div>
          </card>
        </div>
        <div class="col-sm-8">
          <card class="strpied-tabled-with-hover">
            <template slot="header">
              <h4 class="card-title">
                {{ $ml.with("VueJS").get("txtShiftsManagement") }}
              </h4>
            </template>
            <button-create>
              <template slot="title">{{
                $ml.with("VueJS").get("txtCreate")
              }}</template>
            </button-create>
            <tbl-default
              :dataItems="data.stimetable"
              :dataCols="columns.stimetable"
              dataPath="timetable"
              dataAction="all"
            />
            <div v-if="!data.stimetable.data" class="text-center mt-3">
              <img src="https://i.imgur.com/JfPpwOA.gif" />
            </div>
          </card>
        </div>
      </div>
    </div>
    <create-timetable />
    <edit-timetable />
    <create-schedules-timetable :options="options" />
    <edit-schedules-timetable :options="options" />
  </div>
</template>

<script>
import TblDefault from "../../components/Table";
import { mapGetters, mapActions } from "vuex";
import ButtonCreate from "../../components/Buttons/Create";
import CreateTimetable from "./CreateTimetable";
import EditTimetable from "./EditTimetable";
import CreateSchedulesTimetable from "./CreateSchedulesTimetable";
import EditSchedulesTimetable from "./EditSchedulesTimetable";

export default {
  components: {
    TblDefault,
    ButtonCreate,
    CreateTimetable,
    EditTimetable,
    CreateSchedulesTimetable,
    EditSchedulesTimetable,
  },

  computed: {
    ...mapGetters("timetable", {
      columns: "columns",
      data: "data",
      options: "options",
    }),

    ...mapGetters({
      getLangCode: "getLangCode",
    }),
  },

  methods: {
    ...mapActions("timetable", {
      setColumns: "setColumns",
      getAll: "getAll",
      getAllSchedules: "getAllSchedules",
      getItemt: "getItemt",
      getOptions: "getOptions",
      deleteItemt: "deleteItemt",
    }),
  },

  async created() {
    const _this = this;
    _this.getOptions();
    _this.setColumns();
    await _this.getAll();
    await _this.getAllSchedules();
    $(document).on("click", ".languages button", function () {
      _this.setColumns();
    });
  },
};
</script>
<style lang="scss">
#timetable {
  #nav-tab {
    a {
      position: relative;
      padding: {
        top: 0 !important;
        left: 0 !important;
        bottom: 0 !important;
      }
      &:not(:last-child) {
        margin-right: 10px;
        padding-right: 10px;
        &::after {
          content: "";
          height: 100%;
          width: 1px;
          background-color: #006b82;
          display: block;
          position: absolute;
          top: 0;
          right: 0;
        }
      }
      &.active {
        color: #006b82;
      }
    }
  }
  [data-target="#timeTableCreate"],
  [data-target="#itemCreate"] {
    position: absolute;
  }
  [data-target="#timeTableCreate"] {
    right: 0;
    top: -50px;
  }
  [data-target="#itemCreate"] {
    top: 8px;
    right: 15px;
  }
}
</style>