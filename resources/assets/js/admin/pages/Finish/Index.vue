<template>
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col">
          <card>
            <template slot="header">
              <div class="d-flex justify-content-between">
                <h4 class="card-title">
                  {{ $ml.with("VueJS").get("txtFinish") }}
                </h4>

                <div
                  class="form-group mb-0 d-flex justify-content-between align-items-center"
                  style="min-width: 160px"
                >
                  <select-2
                    :options="currentTeamOption"
                    v-model="selectTeam"
                    class="select2"
                  ></select-2>
                  <div class="ml-3"></div>
                  <select-2
                    v-model="showFilter"
                    :options="optionsFilter"
                    class="select2"
                  ></select-2>
                  <div style="min-width: 150px" class="ml-3 datepicker--custom">
                    <datepicker
                      input-class="form-control"
                      name="startDate"
                      v-model="start_date"
											:disabled="'showSchedule' == showFilter ? false : true"
                      :format="customFormatter"
                      :inline="false"
                      :disabled-dates="disabledEndDates()"
                      :language="getLanguage(this.$ml)"
                    >
                    </datepicker>
                  </div>
                  <button-view-table-option class="mt-0 ml-2" />
                </div>
              </div>
            </template>

            <div class="table-responsive">
              <table-finish
                v-show="!loading"
                class="table-hover table-striped"
                :columns="columns"
                :data="items"
                v-on:get-process="getProcess"
                v-on:update-process="getProcess"
              ></table-finish>
              <div v-if="loading" class="text-center mt-3">
                <img src="https://i.imgur.com/JfPpwOA.gif" />
              </div>
            </div>

            <process-modal
              :currentProcess="currentProcess"
              :arrCurrentProcess="arrCurrentProcess"
              v-on:reset-validation="resetValidate"
            ></process-modal>

            <process-detail-modal
              :currentProcess="currentProcess"
              :arrCurrentProcess="arrCurrentProcess"
              v-on:reset-validation="resetValidate"
            ></process-detail-modal>

            <pagination
              :data="issueProcesses"
              :show-disabled="jShowDisabled"
              :limit="jLimit"
              :align="jAlign"
              :size="jSize"
              @pagination-change-page="fetchData"
            ></pagination>
          </card>
        </div>
      </div>
       <view-table-option
         dataTable="finsh"
        :dataItems="items"
        :dataCols="columns"
      />
    </div>
  </div>
</template>

<script>
import TableFinish from "../../components/TableFinish";
import ProcessModal from "./ProcessModal";
import ProcessDetailModal from "./ProcessDetailModal";
import Card from "../../components/Cards/Card";
import Datepicker from "vuejs-datepicker";
import { vi, ja, en } from "vuejs-datepicker/dist/locale";
import moment from "moment";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import ButtonViewTableOption from "../../components/Buttons/ViewTableOption";
import ViewTableOption from "../../components/ModalViewTableOption";
import { mapGetters, mapActions } from "vuex";

export default {
  components: {
    TableFinish,
    Card,
    Datepicker,
    Select2,
    ProcessModal,
    ProcessDetailModal,
    ButtonViewTableOption,
    ViewTableOption,
  },

  computed: {
    ...mapGetters({
      currentTeamOption: "currentTeamOption",
      currentTeam: "currentTeam",
      dateFormat: "dateFormat",
      disabledEndDates: "disabledEndDates",
    }),
  },

  data() {
    return {
      columns: [
        {
          id: "d_name",
          value: this.$ml.with("VueJS").get("txtDepartment"),
          width: "120",
          class: "",
          filter: true
        },
        {
          id: "t_name",
          value: this.$ml.with("VueJS").get("txtJobType"),
          width: "120",
          class: "",
          filter: true
        },
        {
          id: "t_value",
          value: this.$ml.with("VueJS").get("txtColor"),
          width: "120",
          class: "text-center",
          filter: true
        },
        {
          id: "p_name",
          value: this.$ml.with("VueJS").get("txtProject"),
          width: "",
          class: "",
          filter: true
        },
        {
          id: "i_name",
          value: this.$ml.with("VueJS").get("txtIssue"),
          width: "120",
          class: "",
          filter: true
        },
        {
          id: "phase",
          value: this.$ml.with("VueJS").get("txtPhase"),
          width: "",
          class: "",
          filter: true
        },
        {
          id: "date",
          value: this.$ml.with("VueJS").get("txtDateTime"),
          width: "160",
          class: "",
          filter: true
        },
        {
          id: "user_name",
          value: this.$ml.with("VueJS").get("txtReporter"),
          width: "",
          class: "",
          filter: true
        },
        {
          id: "page",
          value: this.$ml.with("VueJS").get("txtPages"),
          width: "",
          class: "",
          filter: true
        },
        {
          id: "file",
          value: this.$ml.with("VueJS").get("txtFiles"),
          width: "",
          class: "",
          filter: true
        },
        {
          id: "status",
          value: this.$ml.with("VueJS").get("txtStatus"),
          width: "135",
          class: "",
          filter: true
        },
      ],
      loading: true,
      start_date: new Date(),
      selectTeam: "",
      txtAll: this.$ml.with("VueJS").get("txtSelectAll"),

      issueProcesses: {},
      processDetails: [],
      items: [],

      jLimit: 2,
      jShowDisabled: true,
      jAlign: "right",
      jSize: "small",

      showFilter: "showSchedule",
      optionsFilter: [],

      currentProcess: {},
      arrCurrentProcess: [],
      dataLang: {
        vi: vi,
        ja: ja,
        en: en,
      },

      page: 1,
    };
  },
  mounted() {
    const _this = this;
    _this.getOptions();
    $(document).on("click", ".languages button", function () {
      _this.txtAll = _this.$ml.with("VueJS").get("txtSelectAll");
      _this.columns = [
        {
          id: "d_name",
          value: _this.$ml.with("VueJS").get("txtDepartment"),
          width: "120",
          class: "",
          filter: true
        },
        {
          id: "t_name",
          value: _this.$ml.with("VueJS").get("txtJobType"),
          width: "120",
          class: "",
          filter: true
        },
        {
          id: "t_value",
          value: _this.$ml.with("VueJS").get("txtColor"),
          width: "120",
          class: "text-center",
          filter: true
        },
        {
          id: "p_name",
          value: _this.$ml.with("VueJS").get("txtProject"),
          width: "",
          class: "",
          filter: true
        },
        {
          id: "i_name",
          value: _this.$ml.with("VueJS").get("txtIssue"),
          width: "120",
          class: "",
          filter: true
        },
        {
          id: "phase",
          value: _this.$ml.with("VueJS").get("txtPhase"),
          width: "",
          class: "",
          filter: true
        },
        {
          id: "date",
          value: _this.$ml.with("VueJS").get("txtDateTime"),
          width: "160",
          class: "",
          filter: true
        },
        {
          id: "user_name",
          value: _this.$ml.with("VueJS").get("txtReporter"),
          width: "",
          class: "",
          filter: true
        },
        {
          id: "page",
          value: _this.$ml.with("VueJS").get("txtPages"),
          width: "",
          class: "",
          filter: true
        },
        {
          id: "file",
          value: _this.$ml.with("VueJS").get("txtFiles"),
          width: "",
          class: "",
          filter: true
        },
        {
          id: "status",
          value: _this.$ml.with("VueJS").get("txtStatus"),
          width: "135",
          class: "",
          filter: true
        },
      ];
      _this.getOptions();
      _this.showFilter = "showSchedule";
    });
  },
  methods: {
    ...mapActions({
      setCurrentTeam: "setCurrentTeam",
    }),
    getProcessObjectValue(data, id, phase) {
      const arrProcess = data.filter((elem) => {
        if (elem.issue_id === id && elem.phase === phase) return elem;
      });

      return arrProcess;
    },
    fetchData(page = 1, loading = true) {
      this.page = page;
      const uri = "/data/finish/data?page=" + page;
      this.loading = loading;

      axios
        .post(uri, {
          start_date: this.dateFormat(this.start_date, "YYYY-MM-DD"),
          selectTeam: this.selectTeam,
          showFilter: this.showFilter,
        })
        .then((res) => {
          this.issueProcesses = res.data.issueProcesses;
          this.processDetails = res.data.processDetails;

          if (res.data.issueProcesses.data.length) {
            this.items = res.data.issueProcesses.data.map((item, index) => {
              // Get processes of issue
              const arrProcess = this.processDetails.length
                ? this.getProcessObjectValue(
                    this.processDetails,
                    item.id,
                    item.phase
                  )
                : [];
              // Get last process of issue
              const lastProcess = arrProcess[arrProcess.length - 1];

              return Object.assign({}, item, {
                status: arrProcess.length ? lastProcess.status : "",
                page: arrProcess.length ? arrProcess.reduce((total, item) => { return total + item.page * 1; }, 0) : "",
                file: arrProcess.length ? arrProcess.reduce((total, item) => { return total + item.file * 1; }, 0) : "",
                user_name: arrProcess.length ? lastProcess.user_name : "",
                date: arrProcess.length ? this.dateFormat(lastProcess.date, "MMM DD, YYYY HH:mm") : "",
              });
            });
          } else {
            this.items = [];
          }
        })
        .catch((err) => {
          console.log(err);
          alert("Could not load data");
        });

      this.loading = false;
    },
    getOptions() {
      const arr = [
        {
          id: "showSchedule",
          text: this.$ml.with("VueJS").get("txtShowBySchedule"),
        },
        { id: "all", text: this.$ml.with("VueJS").get("txtShowAll") },
        { id: "notFinished", text: this.$ml.with("VueJS").get("txtNotFinished") },
      ];
      this.optionsFilter = [...arr];
    },
    getProcess(item) {
      this.currentProcess = Object.assign({}, item, { status: null });
      this.arrCurrentProcess = this.processDetails.length ? this.getProcessObjectValue(this.processDetails, this.currentProcess.id, this.currentProcess.phase) : [];
    },
    customFormatter(date) {
      return moment(date).format("YYYY/MM/DD");
    },
    resetValidate() {
      this.currentProcess = { status: null };
      this.fetchData(this.page, false);
    },
    getLanguage(data) {
      return this.dataLang[data.current];
    },
  },
  async created() {
    // this.selectTeam = this.currentTeam.id;
    this.selectTeam = 2;
    // if(this.currentTeam.id == 2) await this.fetchData();
  },
  watch: {
    selectTeam: [
      {
        handler: function (value) {
          this.fetchData();
          // if (value != this.currentTeam.id) {
          //   // this.setCurrentTeam(value);
          //   this.fetchData();
          // }
        },
      },
    ],
    start_date: [
      {
        handler: "fetchData",
      },
    ],
    showFilter: [
      {
        handler: "fetchData",
      },
    ],
  },
};
</script>
<style lang="scss">
@import "~vue-multiselect/dist/vue-multiselect.min.css";
.datepicker--custom {
  .vdp-datepicker__calendar {
    left: -150px;
  }
	.vdp-datepicker{
	.form-control[disabled]{
			background-color: #F5F5F5;
		}
	}
}
.type-color {
  width: 60px !important;
  height: 20px;
  margin-right: 5px;
  display: inline-block;
  vertical-align: middle;
}
.message-content {
  > span {
    display: block;
    height: 38px;
    position: relative;
    overflow: hidden;
  }
  button {
    position: absolute;
    right: 0;
    top: 0;
    width: 110px;
    .less {
      display: none;
    }
  }
  &.active {
    > span {
      height: auto;
    }
    button {
      .less {
        display: inline;
      }
      .more {
        display: none;
      }
    }
  }
}
</style>
