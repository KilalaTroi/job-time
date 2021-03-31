<template>
  <div id="timetable" class="content">
    <div class="container-fluid">
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
                <slot name="title">{{ $ml.with('VueJS').get('txtCreate') }}</slot>
              </button>
            </div>
            <template slot="header">
              <h4 class="card-title">{{ $ml.with('VueJS').get('txtTimetableList') }}</h4>
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
              <h4 class="card-title">{{ $ml.with('VueJS').get('txtSchedulesTimetableList') }}</h4>
            </template>
            <button-create>
              <template slot="title">{{ $ml.with('VueJS').get('txtCreate') }}</template>
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