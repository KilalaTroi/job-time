<template>
  <div>
    <modal
      id="processDetailModal"
      :sizeClasses="modalLg"
      v-on:reset-validation="resetValidate"
    >
      <template slot="title">{{ $ml.with("VueJS").get("txtDetails") }}</template>
      <div v-if="currentProcess">
		<div class="form-group border p-3">
			<h5 class="mt-0 mb-1">{{ $ml.with("VueJS").get("txtProject") }}: {{ currentProcess.project }}</h5>
			<h5 class="mt-0 mb-1">{{ $ml.with("VueJS").get("txtIssue") }}: {{ currentProcess.issue ? currentProcess.issue : '--' }}</h5>
			<h5 class="m-0">{{ $ml.with("VueJS").get("txtPhase") }}: {{ currentProcess.phase ? currentProcess.phase : '--' }}</h5>
		</div>
        <hr />
        <div class="table-responsive">
          <table-no-action
            class="table-hover table-striped"
            :columns="columns"
            :data="dataProcess"
          ></table-no-action>
        </div>
        <hr />
        <div class="form-group d-flex justify-content-center">
          <button type="button" class="btn btn-second" @click="resetValidate">
            Close
          </button>
        </div>
      </div>
    </modal>
  </div>
</template>

<script>
import TableNoAction from "../../components/TableNoAction";
import Modal from "../../components/Modals/Modal";

export default {
  name: "process-modal",
  components: {
    Modal,
    TableNoAction,
  },
  props: ["currentProcess", "arrCurrentProcess"],
  data() {
    return {
      columns: [
        {
          id: "date",
          value: this.$ml.with("VueJS").get("lblDate"),
          width: "",
          class: "",
        },
        {
          id: "user_name",
          value: this.$ml.with("VueJS").get("txtReporter"),
          width: "",
          class: "",
        },
        {
          id: "page",
          value: this.$ml.with("VueJS").get("txtPagesWorked"),
          width: "",
          class: "",
        },
        {
          id: "status",
          value: this.$ml.with("VueJS").get("txtStatus"),
          width: "120",
          class: "",
        },
      ],
      dataProcess: [],
      modalLg: "modal-lg",
    };
  },
  mounted() {
    let _this = this;

    $(document).on("click", ".languages button", function () {
      _this.columns = [
        {
          id: "date",
          value: _this.$ml.with("VueJS").get("lblDate"),
          width: "",
          class: "",
        },
        {
          id: "user_name",
          value: _this.$ml.with("VueJS").get("txtReporter"),
          width: "",
          class: "",
        },
        {
          id: "page",
          value: _this.$ml.with("VueJS").get("txtPagesWorked"),
          width: "",
          class: "",
        },
        {
          id: "status",
          value: _this.$ml.with("VueJS").get("txtStatus"),
          width: "120",
          class: "",
        },
      ];
    });
  },
  methods: {
    getDataProcess() {
      if (this.arrCurrentProcess.length) {
        this.dataProcess = this.arrCurrentProcess.map((item, index) => {
          return item;
        });
      } else {
        this.dataProcess = [];
      }
    },
    resetValidate() {
      $("#processDetailModal").modal("hide");
      this.$emit("reset-validation");
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
#processDetailModal {
  .modal-dialog {
    .modal-content {
      .modal-body {
        .table-responsive {
          td {
            padding: 5px 8px;
          }
        }
      }
    }
  }
}
</style>