<template>
  <div>
    <modal
      id="processModal"
      :sizeClasses="modalLg"
      v-on:reset-validation="resetValidate"
    >
      <template slot="title">{{
        $ml.with("VueJS").get("txtWorkReport")
      }}</template>

      <div v-if="currentProcess">
        <div class="table-responsive">
          <table-no-action
            class="table-hover table-striped"
            :columns="columns"
            :data="dataProcess"
          ></table-no-action>
        </div>
        <hr />

        <div class="row">
          <div class="col">
            <div class="form-group">
              <label class="">{{ $ml.with("VueJS").get("txtReporter") }}</label>
              <p>{{ loginUser.name }}</p>
            </div>
          </div>

          <div class="col">
            <div class="form-group">
              <label class="">{{ $ml.with("VueJS").get("txtStatus") }}</label>
              <select-2 v-model="currentProcess.status" class="select2">
                <option value="">--</option>
                <option value="Start Working">Start Working</option>
                <option value="Finished Work">Finished Work</option>
                <option value="Start Uploading">Start Uploading</option>
                <option value="Finished Upload">Finished Upload</option>
              </select-2>
            </div>
          </div>

          <div class="col">
            <div class="form-group">
              <label class="">{{
                $ml.with("VueJS").get("txtPagesWorked")
              }}</label>
              <input
                type="number"
                min="0"
                v-model="currentProcess.page"
                class="form-control"
                :disabled="currentProcess.status != 'Finished Work'"
              />
            </div>
          </div>
          <div class="col">
            <div class="form-group">
              <label class="">{{
                $ml.with("VueJS").get("txtFilesWorked")
              }}</label>
              <input
                type="number"
                min="0"
                v-model="currentProcess.file"
                class="form-control"
                :disabled="currentProcess.status != 'Finished Work'"
              />
            </div>
          </div>
        </div>
        <hr />

        <div v-if="!sendSuccess" class="form-group">
          <h5>{{ $ml.with("VueJS").get("txtMessage") }}</h5>
          <textarea
            v-model="newMessage"
            class="form-control"
            rows="8"
          ></textarea>
        </div>

        <error-item :errors="errors"></error-item>
        <success-item :success="success"></success-item>

        <div class="form-group d-flex justify-content-center">
          <button
            v-if="!sendSuccess"
            type="button"
            class="btn btn-primary mr-3"
            @click="sendProcess"
          >
            {{ $ml.with("VueJS").get("txtSend") }}
          </button>
          <button type="button" class="btn btn-second" @click="resetValidate">
            Cancel
          </button>
        </div>
      </div>
    </modal>
    <loading
      :active.sync="isLoading"
      :is-full-page="true"
      :can-cancel="true"
      :on-cancel="onCancel"
    ></loading>
  </div>
</template>

<script>
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import TableNoAction from "../../components/TableNoAction";
import ErrorItem from "../../components/Validations/Error";
import SuccessItem from "../../components/Validations/Success";
import Modal from "../../components/Modals/Modal";
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/vue-loading.css";
import { mapGetters } from "vuex";

export default {
  name: "process-modal",
  components: {
    Select2,
    Modal,
    ErrorItem,
    SuccessItem,
    TableNoAction,
    Loading,
  },
  props: ["currentProcess", "arrCurrentProcess"],
  computed: {
    ...mapGetters({
      loginUser: "loginUser",
      dateFormat: "dateFormat",
      currentTeam: "currentTeam",
    }),
  },
  data() {
    return {
      columns: [
        {
          id: "p_name",
          value: this.$ml.with("VueJS").get("txtProject"),
          width: "",
          class: "",
        },
        {
          id: "i_name",
          value: this.$ml.with("VueJS").get("txtIssue"),
          width: "",
          class: "",
        },
        {
          id: "phase",
          value: this.$ml.with("VueJS").get("txtPhase"),
          width: "",
          class: "",
        },
        {
          id: "status",
          value: this.$ml.with("VueJS").get("txtStatus"),
          width: "135",
          class: "",
        },
      ],
      dataProcess: [],
      newMessage: "",
      errors: "",
      success: "",
      sendSuccess: false,
      modalLg: "modal-lg",
      isLoading: false,
    };
  },
  mounted() {
    const _this = this;

    $(document).on("click", ".languages button", function () {
      _this.columns = [
        {
          id: "p_name",
          value: _this.$ml.with("VueJS").get("txtProject"),
          width: "",
          class: "",
        },
        {
          id: "i_name",
          value: _this.$ml.with("VueJS").get("txtIssue"),
          width: "",
          class: "",
        },
        {
          id: "phase",
          value: _this.$ml.with("VueJS").get("txtPhase"),
          width: "",
          class: "",
        },
        {
          id: "status",
          value: _this.$ml.with("VueJS").get("txtStatus"),
          width: "135",
          class: "",
        },
      ];
    });
  },
  methods: {
    async sendMessageLineWork(content) {
      if (this.currentProcess.room_id) {
        const uri = "/data/finish/submit-message";
        await axios
          .post(uri, {
            roomId: this.currentProcess.room_id,
            content: content,
            user: this.loginUser,
            p_name: this.currentProcess.project,
            i_name: this.currentProcess.i_name,
            page: this.currentProcess.page,
            page_number: this.currentProcess.page_number,
            phase: this.currentProcess.phase,
            status: this.currentProcess.status,
            team_id: this.currentTeam.id,
            email: this.currentProcess.email,
          })
          .then((res) => {
            console.log(res.data);
            if (res.data.code === 200) {
              this.success = "Successfully.";
            } else {
              this.errors = [[res.data.errorMessage]];
            }
          })
          .catch((err) => {
            console.log(err);
          });
      }
    },
    getDataProcess() {
      if (this.currentProcess.page_number && !this.currentProcess.page) {
        this.currentProcess.page = this.currentProcess.page_number;
      }

      this.newMessage =
        "[" +
        (this.currentProcess.status
          ? this.currentProcess.status
          : "null!") +
        "] \nReporter:  " +
        this.loginUser.name +
        " \nProject: " +
        this.currentProcess.p_name +
        " \nIssue: " +
        (this.currentProcess.i_name ? this.currentProcess.i_name : "--") +
        " \nPhase: " +
        (this.currentProcess.phase ? this.currentProcess.phase : "--") +
        " \n----------------------------  ";

      if (this.arrCurrentProcess.length) {
        this.dataProcess = this.arrCurrentProcess.map((item, index) => {
          return {
            p_name: this.currentProcess.project,
            i_name: this.currentProcess.issue,
            phase: this.currentProcess.phase,
            status: item.status,
          };
        });
      } else {
        this.dataProcess = [
          {
            p_name: this.currentProcess.project,
            i_name: this.currentProcess.issue,
            phase: this.currentProcess.phase,
            status: null,
          },
        ];
      }
    },
    async sendProcess() {
      // Reset validate
      this.errors = [];
      this.success = "";
      const checkStatus = this.currentProcess.status === "Finished Work" ? true : false;

      if (!this.currentProcess.status) {
        this.errors = [["Please choosing the status."], ...this.errors];
      }

      if (!this.newMessage) {
        this.errors = [["Please typing the massage."], ...this.errors];
      }

      if(!this.currentProcess.page || 0 == this.currentProcess.page){
        if(checkStatus) this.errors = [['Enter in "PAGES WORKS and FILES WORKED"'], ...this.errors];
      }

      if (0 == this.errors.length) {
        this.isLoading = true;

        const newProcess = {
          user_id: this.loginUser.id,
          issue_id: this.currentProcess.id,
          schedule_id: this.currentProcess.schedule_id,
          memo: this.currentProcess.phase,
          date: this.dateFormat(new Date(), "YYYY-MM-DD HH:mm"),
          page: checkStatus ? this.currentProcess.page : null,
          file: checkStatus ? this.currentProcess.file : null,
          status: this.currentProcess.status ? this.currentProcess.status : "null",
        };

        const uri = "/data/processes";

        await axios
          .post(uri, newProcess)
          .then((res) => {
            this.success = res.data.message;
          })
          .catch((err) => {
            console.log(err);
            if (err.response.status == 422) {
              this.errors = err.response.data;
            }
          });

        await this.sendMessageLineWork(this.newMessage).then((res) => {
          this.isLoading = false;
          this.newMessage = "";
          this.currentProcess.status = null;
        });
      }
    },
    resetValidate() {
      $("#processModal").modal("hide");
      this.errors = "";
      this.success = "";
      this.newMessage = "";
      this.$emit("reset-validation");
    },
    onCancel() {
      window.location.reload();
    },
  },
  watch: {
    currentProcess: [
      {
        handler: "getDataProcess",
        deep: true,
      },
    ],
  },
};
</script>
<style lang="scss">
#processModal {
  #selectDest .form-control {
    font-size: 14px;
    padding: 5px 12px;
    line-height: 20px;
    height: 30px;
  }
  .modal-dialog {
    .modal-content {
      .modal-body {
        h5 {
          font-weight: 600;
          margin-bottom: 10px;
        }
        .form-control {
          border-radius: 0;
        }
        #selectDest {
          h5 {
            position: relative;
            text-transform: uppercase;
          }
          p {
            margin: 0;
            font-size: 14px;
          }
          .form-group {
            background: #ffffff;
            label {
              cursor: pointer;
              font-size: 14px;
              font-weight: 600;
              position: absolute;
              right: 0;
              opacity: 0.8;
              &.dest-box {
                color: #005fdd;
              }
              &.dest-file {
                color: #f83f86;
              }
              &:hover {
                opacity: 1;
              }
            }
          }
        }
        .table-responsive {
          td {
            padding: 5px 8px;
          }
        }
        .form-check {
          .form-check-sign {
            &:before,
            &:after {
              top: 2px;
            }
          }
          span {
            color: #333333;
            font-size: 16px;
          }
        }
      }
    }
  }
}
</style>