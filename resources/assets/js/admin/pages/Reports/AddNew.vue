<template>
  <card>
    <template slot="header">
      <div class="d-flex justify-content-between">
        <h4 class="card-title">
          {{ $ml.with("VueJS").get("txtReportCreate") }}
        </h4>
        <div class="align-self-end">
          <button @click="$emit('back-to-list')" class="btn btn-primary">
            Back
          </button>
        </div>
      </div>
    </template>
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <label class="text-uppercase"
            ><strong>{{ $ml.with("VueJS").get("txtTitle") }}</strong></label
          >
          <input
            v-if="language == 'vi'"
            v-model="title"
            type="text"
            class="form-control"
          />
          <input
            v-if="language == 'ja'"
            v-model="titleJA"
            type="text"
            class="form-control"
          />
        </div>
      </div>

      <div class="col-sm-3">
        <div class="form-group">
          <label class="text-uppercase"
            ><strong>{{
              $ml.with("VueJS").get("txtReportType")
            }}</strong></label
          >
          <select-2 v-model="reportType" class="select2">
            <option value="Trouble">
              {{ $ml.with("VueJS").get("txtTrouble") }}
            </option>
            <option value="Meeting">
              {{ $ml.with("VueJS").get("txtMeeting") }}
            </option>
            <option value="Notice">
              {{ $ml.with("VueJS").get("txtNotice") }}
            </option>
          </select-2>
        </div>
      </div>

      <div class="col-sm-3">
        <div class="form-group">
          <label class="text-uppercase"
            ><strong>{{ $ml.with("VueJS").get("txtTeam") }}</strong></label
          >
          <select-2
            :options="currentTeamOption"
            v-model="filters.team"
            class="select2"
          />
        </div>
      </div>
    </div>
    <div class="row form-group">
      <div class="col-sm-3">
        <div class="form-group">
          <label
            ><strong>{{ $ml.with("VueJS").get("lblDate") }}</strong></label
          >
          <datepicker
            name="date"
            input-class="form-control"
            placeholder=""
            v-model="date"
            :format="customFormatter"
            :disabled-dates="disabledEndDates()"
            :language="getLangCode(this.$ml)"
          ></datepicker>
        </div>
      </div>

      <div class="col-sm-3" v-if="isMeeting() || isNotice()">
        <label
          ><strong>{{ $ml.with("VueJS").get("lblTime") }}</strong></label
        >
        <vue-timepicker
          input-class="form-control"
          v-model="time"
          hide-disabled-items
          :minute-range="MinuteRange"
          :hour-range="HourRange"
          input-width="100%"
          close-on-complete
          required
        ></vue-timepicker>
      </div>

      <div
        :class="[
          { 'col-sm-6': isMeeting() || isNotice() },
          { 'col-sm-9': !isMeeting() && !isNotice() },
        ]"
      >
        <div class="form-group">
          <label
            ><strong>{{ $ml.with("VueJS").get("txtReporter") }}</strong></label
          >
          <div>
            <multiselect
              :multiple="true"
              v-model="user_id"
              :options="userOptions"
              :clear-on-select="false"
              :preserve-search="true"
              :placeholder="$ml.with('VueJS').get('txtPickSome')"
              label="text"
              track-by="text"
            ></multiselect>
          </div>
        </div>
      </div>

      <div class="col-sm-12" v-if="isMeeting() || isNotice()">
        <div class="form-group">
          <label v-if="isNotice()"
            ><strong>{{
              $ml.with("VueJS").get("txtDestination")
            }}</strong></label
          >
          <label v-else
            ><strong>{{
              $ml.with("VueJS").get("txtAttendPerson")
            }}</strong></label
          >
          <div>
            <multiselect
              :multiple="true"
              v-model="attendPerson"
              :options="userOptions"
              :clear-on-select="false"
              :preserve-search="true"
              :placeholder="$ml.with('VueJS').get('txtPickSome')"
              label="text"
              track-by="text"
            ></multiselect>
          </div>
        </div>
      </div>

      <div class="col-sm-9" v-if="isMeeting() || isNotice()">
        <div class="form-group">
          <label class
            ><strong
              >{{ $ml.with("VueJS").get("txtAttendPerson") }} (Other)</strong
            ></label
          >
          <input v-model="attendPersonOther" type="text" class="form-control" />
        </div>
      </div>

      <div class="col-sm-3" v-if="!isMeeting() && !isNotice()">
        <div class="form-group">
          <label class
            ><strong>{{ $ml.with("VueJS").get("txtDepts") }}</strong></label
          >
          <div>
            <multiselect
              :multiple="false"
              v-model="filters.department"
              :options="options.departments"
              :clear-on-select="false"
              :preserve-search="true"
              :placeholder="$ml.with('VueJS').get('txtSelectOne')"
              label="text"
              track-by="text"
            ></multiselect>
          </div>
        </div>
      </div>
      <div class="col-sm-3" v-if="!isMeeting() && !isNotice()">
        <div class="form-group">
          <label class
            ><strong>{{ $ml.with("VueJS").get("txtProjects") }}</strong></label
          >
          <div>
            <multiselect
              :multiple="false"
              v-model="filters.project"
              :options="options.projects"
              :clear-on-select="false"
              :preserve-search="true"
              :placeholder="$ml.with('VueJS').get('txtSelectOne')"
              label="text"
              track-by="text"
            ></multiselect>
          </div>
        </div>
      </div>
      <div class="col-sm-2" v-if="!isMeeting() && !isNotice()">
        <div class="form-group">
          <label class>{{ $ml.with("VueJS").get("txtYearOfIssue") }}</label>
          <div>
            <multiselect
              :multiple="false"
              v-model="filters.issue_year"
              :options="options.issues_year"
              :clear-on-select="true"
              :preserve-search="false"
              :placeholder="$ml.with('VueJS').get('txtSelectOne')"
              label="text"
              track-by="text"
            ></multiselect>
          </div>
        </div>
      </div>
      <div class="col-sm-2" v-if="!isMeeting() && !isNotice()">
        <div class="form-group">
          <label class
            ><strong>{{ $ml.with("VueJS").get("txtIssue") }}</strong></label
          >
          <div>
            <multiselect
              :multiple="false"
              v-model="filters.issues"
              :options="options.issues"
              :clear-on-select="true"
              :preserve-search="false"
              :placeholder="$ml.with('VueJS').get('txtSelectOne')"
              label="text"
              track-by="text"
            ></multiselect>
          </div>
        </div>
      </div>
      <div class="col-sm-2">
        <div class="form-group">
          <label class=""
            ><strong>{{ $ml.with("VueJS").get("txtLang") }}</strong></label
          >
          <select-2 v-model="language" class="select2">
            <option value="vi">{{ $ml.with("VueJS").get("txtVi") }}</option>
            <option value="ja">{{ $ml.with("VueJS").get("txtJa") }}</option>
          </select-2>
        </div>
      </div>
    </div>

    <div class="form-group">
      <div id="toolbar-container"></div>
      <div id="ck-editor">
        <ckeditor
          v-if="language == 'vi'"
          :editor="editor"
          v-model="editorData"
          :config="editorConfig"
          @ready="onReady"
        ></ckeditor>
        <ckeditor
          v-if="language == 'ja'"
          :editor="editor"
          v-model="editorDataJA"
          :config="editorConfig"
          @ready="onReady"
        ></ckeditor>
      </div>
    </div>

    <error-item :errors="errors"></error-item>

    <div class="form-group text-right">
      <button @click="emitCreateReport" class="btn btn-primary">
        {{ $ml.with("VueJS").get("txtCreate") }}
      </button>
    </div>
  </card>
</template>
<script>
import Datepicker from "vuejs-datepicker";
import VueTimepicker from "vue2-timepicker";
import Multiselect from "vue-multiselect";
import { vi, ja, en } from "vuejs-datepicker/dist/locale";
import moment from "moment";
import DecoupledEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import Card from "../../components/Cards/Card";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import ErrorItem from "../../components/Validations/Error";
import { mapGetters, mapActions } from "vuex";

class MyUploadAdapter {
  constructor(loader) {
    // The file loader instance to use during the upload.
    this.loader = loader;
  }

  // Starts the upload process.
  upload() {
    return this.loader.file.then(
      (file) =>
        new Promise((resolve, reject) => {
          this._initRequest();
          this._initListeners(resolve, reject, file);
          this._sendRequest(file);
        })
    );
  }

  // Aborts the upload process.
  abort() {
    if (this.xhr) {
      this.xhr.abort();
    }
  }

  // Initializes the XMLHttpRequest object using the URL passed to the constructor.
  _initRequest() {
    const xhr = (this.xhr = new XMLHttpRequest());

    // Note that your request may look different. It is up to you and your editor
    // integration to choose the right communication channel. This example uses
    // a POST request with JSON as a data structure but your configuration
    // could be different.
    xhr.open("POST", "/data/upload/report", true);
    xhr.responseType = "json";
    xhr.setRequestHeader(
      "X-CSRF-TOKEN",
      document.head.querySelector('meta[name="csrf-token"]').content
    );
  }

  // Initializes XMLHttpRequest listeners.
  _initListeners(resolve, reject, file) {
    const xhr = this.xhr;
    const loader = this.loader;
    const genericErrorText = `Couldn't upload file: ${file.name}.`;

    xhr.addEventListener("error", () => reject(genericErrorText));
    xhr.addEventListener("abort", () => reject());
    xhr.addEventListener("load", () => {
      const response = xhr.response;

      // This example assumes the XHR server's "response" object will come with
      // an "error" which has its own "message" that can be passed to reject()
      // in the upload promise.
      //
      // Your integration may handle upload errors in a different way so make sure
      // it is done properly. The reject() function must be called when the upload fails.
      if (!response || response.error) {
        return reject(
          response && response.error ? response.error.message : genericErrorText
        );
      }

      // If the upload is successful, resolve the upload promise with an object containing
      // at least the "default" URL, pointing to the image on the server.
      // This URL will be used to display the image in the content. Learn more in the
      // UploadAdapter#upload documentation.
      resolve({
        default: response.url,
      });
    });

    // Upload progress when it is supported. The file loader has the #uploadTotal and #uploaded
    // properties which are used e.g. to display the upload progress bar in the editor
    // user interface.
    if (xhr.upload) {
      xhr.upload.addEventListener("progress", (evt) => {
        if (evt.lengthComputable) {
          loader.uploadTotal = evt.total;
          loader.uploaded = evt.loaded;
        }
      });
    }
  }

  // Prepares the data and sends the request.
  _sendRequest(file) {
    // Prepare the form data.
    const data = new FormData();

    data.append("upload", file);

    // Important note: This is the right place to implement security mechanisms
    // like authentication and CSRF protection. For instance, you can use
    // XMLHttpRequest.setRequestHeader() to set the request headers containing
    // the CSRF token generated earlier by your application.

    // Send the request.
    this.xhr.send(data);
  }
}

// ...

function MyCustomUploadAdapterPlugin(editor) {
  editor.plugins.get("FileRepository").createUploadAdapter = (loader) => {
    // Configure the URL to the upload script in your back-end here!
    return new MyUploadAdapter(loader);
  };
}

export default {
  name: "add-new",
  components: {
    Select2,
    Datepicker,
    Multiselect,
    Card,
    ErrorItem,
    VueTimepicker,
  },
  computed: {
    ...mapGetters({
      currentTeamOption: "currentTeamOption",
      currentTeam: "currentTeam",
      getLangCode: "getLangCode",
      customFormatter: "customFormatter",
      disabledStartDates: "disabledStartDates",
      disabledEndDates: "disabledEndDates"
    }),

    ...mapGetters('reports',{
      filters: "filters",
      options: "options",
      action: "action",
    }),
  },
  props: ["userID", "actionNewReport"],
  data() {
    return {
      title: "",
      titleJA: "",
      date: "",
      time: "",
      HourRange: [[8, 17]],
      MinuteRange: [0, 10, 20, 30, 40, 50],
      dataLang: {
        vi: vi,
        ja: ja,
      },
      user_id: [],
      attendPerson: [],
      attendPersonOther: "",
      deptSelects: null,
      projectSelects: null,
      issueSelects: null,
      issueYearSelects: null,
      reportType: "Trouble",
      txtAll: this.$ml.with("VueJS").get("txtSelectAll"),
      userOptions: [],
      departments: [],
      projects: [],
      issues: [],
      issuesYear: [],
      isEditing: false,
      editor: DecoupledEditor,
      editorData: "",
      editorDataJA: "",
      editorConfig: {
        // The configuration of the editor.
        // language: 'ja'
        extraPlugins: [MyCustomUploadAdapterPlugin],
      },

      language: this.$ml.current,
      translatable: 0,
      errors: [],
      team: 0,
    };
  },
  beforeMount() {
    this.defaultContent();

    window.addEventListener("beforeunload", this.preventNav);
    this.$once("hook:beforeDestroy", () => {
      window.removeEventListener("beforeunload", this.preventNav);
    });
  },
  beforeRouteLeave(to, from, next) {
    if (this.isEditing) {
      if (!window.confirm("Leave without saving?")) {
        return;
      }
    }
    next();
  },
  async created() {
    const _this = this;
    _this.filters.team = _this.currentTeam.id;
    _this.filters.page = -1;
	},
  methods: {
     ...mapActions({
      setCurrentTeam: "setCurrentTeam",
    }),
    ...mapActions('reports',{
      getAll: "getAll",
      resetFilters: "resetFilters",
    }),

    onReady(editor) {
      // Insert the toolbar before the editable area.
      // editor.ui.getEditableElement().parentElement.insertBefore(
      //     editor.ui.view.toolbar.element,
      //     editor.ui.getEditableElement()
      // );

      const toolbarContainer = document.querySelector("#toolbar-container");
      toolbarContainer.appendChild(editor.ui.view.toolbar.element);
    },
    preventNav(event) {
      if (!this.isEditing) return;
      event.preventDefault();
      event.returnValue = "";
    },
    contentChange() {
      this.isEditing = true;
    },
    emitCreateReport() {
      this.errors = [];

      if (!this.title && !this.titleJA) {
        this.errors = [["Please typing the title"], ...this.errors];
      }

      if (!this.date) {
        this.errors = [["Please choosing the date"], ...this.errors];
      }

      if (!this.user_id.length) {
        this.errors = [["Please choosing the user report"], ...this.errors];
      }

      if (this.isMeeting() || this.isNotice()) {
        if (!this.attendPerson.length) {
          this.errors = [["Please choosing the user attend"], ...this.errors];
        }
      } else {
        if (!this.filters.department) {
          this.errors = [["Please choosing the department"], ...this.errors];
        }

        if (!this.filters.project) {
          this.errors = [["Please choosing the project"], ...this.errors];
        }

        if (!this.filters.issue) {
          this.errors = [["Please choosing the issue"], ...this.errors];
        }

        if (!this.filters.issue_year) {
          this.errors = [["Please choosing the issue year"], ...this.errors];
        }
      }

      if (!this.editorData && !this.editorDataJA) {
        this.errors = [["Please typing the content"], ...this.errors];
      }

      if (!this.errors.length) {
        let uri = "/data/reports-action";
        let newItem = {
          language: this.language,
          translatable: this.translatable,
          type: this.reportType,
          seen: this.userID.toString(),
          author: this.user_id
            .map((item, index) => {
              return item.id;
            })
            .toString(),
          team_id: this.team,
        };

        if (this.language == "vi") {
          newItem.title = this.title;
          newItem.content = this.editorData;
          newItem.title_ja = this.title;
          newItem.content_ja = this.editorData;
        } else {
          newItem.title = this.titleJA;
          newItem.content = this.editorDataJA;
          newItem.title_ja = this.titleJA;
          newItem.content_ja = this.editorDataJA;
        }

        if (this.isMeeting() || this.isNotice()) {
          newItem.attend_person = this.attendPerson
            .map((item, index) => {
              return item.id;
            })
            .toString();
          newItem.attend_other_person = this.attendPersonOther;
          newItem.date_time =
            moment(this.date).format("YYYY-MM-DD") + " " + this.time;
        } else {
          newItem.date_time = moment(this.date).format("YYYY-MM-DD HH:mm");
          newItem.projects = this.projectSelects.id;
          newItem.issue = this.issueSelects.id;
          newItem.issueYear = this.issueYearSelects.id;
        }

        axios
          .post(uri, newItem)
          .then((res) => {
            this.title = "";
            this.titleJA = "";
            this.date = "";
            this.time = "";
            this.attendPerson = [];
            this.attendPersonOther = "";
            this.user_id = [];
            this.deptSelects = null;
            this.projectSelects = null;
            this.issueSelects = null;
            this.issueYearSelects = null;
            this.reportType = "Trouble";
            this.editorData = "";
            this.editorDataJA = "";
            this.errors = [];
            this.$emit("back-to-list", true);
          })
          .catch((err) => {
            console.log(err);
            if (err.response.status == 422) {
              this.errors = err.response.data;
            }
          });
      }
    },
    defaultContent() {
      if (this.isMeeting()) {
        this.editorData =
          "<h4>議事内容</h4><ol><li>会議の内容や決定事項を記入</li><li>会議の内容や決定事項を記入</li></ol><h4>次回の予定</h4><ul><li>次回のミーティング内容、やるべきことを記入</li></ul>";
        this.editorDataJA =
          "<h4>議事内容</h4><ol><li>会議の内容や決定事項を記入</li><li>会議の内容や決定事項を記入</li></ol><h4>次回の予定</h4><ul><li>次回のミーティング内容、やるべきことを記入</li></ul>";
      } else {
        if (this.isNotice()) {
          this.editorData = "<h4>お知らせ</h4>";
          this.editorDataJA = "<h4>お知らせ</h4>";
        } else {
          this.editorData =
            '<h4>トラブルの内容</h4><ol><li>「いつ」「誰が」「何をした」を時間順に記入</li><li>「いつ」「誰が」「何をした」を時間順に記入</li></ol><h4>参考画像</h4><p style="margin-left:40px;">&nbsp;</p><h4>トラブルの原因</h4><ul><li>トラブルの「原因」を記入</li></ul><h4>改善方法</h4><ul><li>トラブル防止の「改善方法」を記入</li></ul>';
          this.editorDataJA =
            '<h4>トラブルの内容</h4><ol><li>「いつ」「誰が」「何をした」を時間順に記入</li><li>「いつ」「誰が」「何をした」を時間順に記入</li></ol><h4>参考画像</h4><p style="margin-left:40px;">&nbsp;</p><h4>トラブルの原因</h4><ul><li>トラブルの「原因」を記入</li></ul><h4>改善方法</h4><ul><li>トラブル防止の「改善方法」を記入</li></ul>';
        }
      }
    },
    isMeeting() {
      return this.reportType == "Meeting";
    },
    isNotice() {
      return this.reportType == "Notice";
    },
    typeReportChange() {
      this.errors = [];

      this.defaultContent();

      if (this.isMeeting() || this.isNotice()) {
        this.deptSelects = [];
      } else {
        this.attendPerson = [];
        this.attendPersonOther = "";
      }
    },
    languageChange() {
      if (this.action.new) {
        this.errors = [];
        this.title = "";
        this.titleJA = "";
        this.defaultContent();
      }
    },
  },
  watch: {
    editorData: [
      {
        handler: "contentChange",
      },
    ],
    editorDataJA: [
      {
        handler: "contentChange",
      },
    ],

    reportType: [
      {
        handler: "typeReportChange",
      },
    ],
    language: [
      {
        handler: "languageChange",
      },
    ],
  },
};
</script>

<style lang="scss">
@import "~vue-multiselect/dist/vue-multiselect.min.css";
@import "~vue2-timepicker/dist/VueTimepicker.css";

.multiselect__single {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.vue__time-picker input.display-time {
  height: 40px;
  background-color: transparent;
  cursor: pointer;
}
</style>