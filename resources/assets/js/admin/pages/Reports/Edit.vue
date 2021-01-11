<template>
  <card>
    <template slot="header">
      <div class="d-flex justify-content-between">
        <h4 class="card-title">{{ $ml.with("VueJS").get("txtEdit") }}</h4>
        <div class="align-self-end">
          <button
            v-if="checkTranslate()"
            @click="translateContent()"
            class="btn btn-success mr-3"
          >
            {{ $ml.with("VueJS").get("txtTranslate") }}
          </button>
            <!-- @click="$emit('delete-report', currentReport.id)" -->

          <button @click="deleteReport(selectedItem)" class="btn btn-danger mr-3">
            {{ $ml.with("VueJS").get("txtDelete") }}
          </button>
          <button  @click="backToList()" class="btn btn-primary">
            {{ $ml.with("VueJS").get("txtBack") }}
          </button>
        </div>
      </div>
    </template>
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <label class=""
            ><strong>{{ $ml.with("VueJS").get("txtTitle") }}</strong></label
          >
          <input
            v-if="selectedItem.language == 'vi'"
            v-model="selectedItem.title"
            type="text"
            class="form-control"
          />
          <input
            v-if="selectedItem.language == 'ja'"
            v-model="selectedItem.title_ja"
            type="text"
            class="form-control"
          />
        </div>
      </div>

      <div class="col-sm-3">
        <div class="form-group">
          <label class=""
            ><strong>{{
              $ml.with("VueJS").get("txtReportType")
            }}</strong></label
          >
          <select-2 v-model="filters.type" class="select2">
            <option value="Notice">
              {{ $ml.with("VueJS").get("txtNotice") }}
            </option>
            <option value="Trouble">
              {{ $ml.with("VueJS").get("txtTrouble") }}
            </option>
            <option value="Meeting">
              {{ $ml.with("VueJS").get("txtMeeting") }}
            </option>
          </select-2>
        </div>
      </div>

      <div class="col-sm-3">
        <div class="form-group">
          <label class=""
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
            v-model="selectedItem.date"
            :format="customFormatter"
            :disabled-dates="disabledEndDates()"
            :language="getLangCode(this.$ml)"
          ></datepicker>
        </div>
      </div>

      <div class="col-sm-3" v-if="'Meeting' == filters.type || 'Notice' == filters.type">
        <label><strong>{{ $ml.with("VueJS").get("lblTime") }}</strong></label>
        <vue-timepicker
          input-class="form-control"
          v-model="selectedItem.time"
          hide-disabled-items
          :minute-range="[0, 10, 20, 30, 40, 50]"
          :hour-range="[[8, 17]]"
          input-width="100%"
          close-on-complete
          required
        ></vue-timepicker>
      </div>

      <div :class="'Meeting' == filters.type || 'Notice' == filters.type ? 'col-sm-6' : 'col-sm-9'">
        <div class="form-group">
          <label class
            ><strong>{{ $ml.with("VueJS").get("txtReporter") }}</strong></label
          >
          <div>
            <multiselect
              :multiple="true"
              v-model="filters.user_id"
              :options="options.users"
              :clear-on-select="false"
              :preserve-search="true"
              :placeholder="$ml.with('VueJS').get('txtPickSome')"
              label="text"
              track-by="text"
            ></multiselect>
          </div>
        </div>
      </div>

      <div class="col-sm-12" v-if="'Meeting' == filters.type || 'Notice' == filters.type">
        <div class="form-group">
          <label v-if="'Notice' == filters.type"><strong>{{ $ml.with("VueJS").get("txtDestination") }}</strong></label>
          <label v-else><strong>{{ $ml.with("VueJS").get("txtAttendPerson") }}</strong></label>
          <div>
            <multiselect
              :multiple="true"
              v-model="selectedItem.attendPerson"
              :options="options.users"
              :clear-on-select="false"
              :preserve-search="true"
              :placeholder="$ml.with('VueJS').get('txtPickSome')"
              label="text"
              track-by="text"
            ></multiselect>
          </div>
        </div>
      </div>

      <div class="col-sm-9" v-if="'Meeting' == filters.type || 'Notice' == filters.type">
        <div class="form-group">
          <label><strong>{{ $ml.with("VueJS").get("txtAttendPerson") }} (Other)</strong></label>
          <input v-model="selectedItem.attend_other_person" type="text" class="form-control" />
        </div>
      </div>

      <div class="col-sm-3" v-if="'Meeting' != filters.type && 'Notice' != filters.type">
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
      <div class="col-sm-3" v-if="'Meeting' != filters.type && 'Notice' != filters.type">
        <div class="form-group">
          <label><strong>{{ $ml.with("VueJS").get("txtProjects") }}</strong></label>
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
      <div class="col-sm-2" v-if="'Meeting' != filters.type && 'Notice' != filters.type">
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
      <div class="col-sm-2" v-if="'Meeting' != filters.type && 'Notice' != filters.type">
        <div class="form-group">
          <label><strong>{{ $ml.with("VueJS").get("txtIssue") }}</strong></label>
          <div>
            <multiselect
              :multiple="false"
              v-model="filters.issue"
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
          <label class=""><strong>{{ $ml.with("VueJS").get("txtLang") }}</strong></label>
          <select-2 v-model="selectedItem.language" class="select2">
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
          v-if="selectedItem.language == 'vi'"
          :editor="editor"
          v-model="selectedItem.content"
          :config="editorConfig"
          @ready="onReady"
        ></ckeditor>
        <ckeditor
          v-if="selectedItem.language == 'ja'"
          :editor="editor"
          v-model="selectedItem.content_ja"
          :config="editorConfig"
          @ready="onReady"
        ></ckeditor>
      </div>
    </div>

    <error-item :errors="validationErrors"></error-item>

    <div class="form-group text-right">
      <!-- @click="updateReport" -->
      <button @click="updateReport()" class="btn btn-primary">
        {{ $ml.with("VueJS").get("txtUpdate") }}
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
  name: "edit",
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
      disabledEndDates: "disabledEndDates"
    }),
    ...mapGetters('reports',{
      filters: "filters",
      selectedItem: "selectedItem",
      options: "options",
      action: "action",
      validationErrors: "validationErrors"
    }),
  },
  data() {
    return {
      editLanguage: '',
      isEditing: false,
      editor: DecoupledEditor,
      editorConfig: {
        // The configuration of the editor.
        // language: 'ja'
        extraPlugins: [MyCustomUploadAdapterPlugin],
      },

    };
  },

  beforeMount() {
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
    _this.filters.user_id = this.getReporter(_this.selectedItem.reporter);
    if(_this.selectedItem.attend_person) _this.selectedItem.attendPerson = this.getReporter(_this.selectedItem.attend_person);
    _this.page = -1;
    _this.updateSeen();
  },

  mounted() {
    this.editLanguage = this.selectedItem.language;
  },

  methods: {
    ...mapActions('reports', {
      translateContent: "translateContent",
      backToList: "backToList",
      resetValidate : "resetValidate",
      updateReport: "updateReport",
      updateSeen: "updateSeen",
      deleteReport: "deleteReport",
    }),
    checkTranslate() {
      return (!this.selectedItem.translatable && (this.selectedItem.language != this.editLanguage));
    },

    getObjectValue(data, id) {
      let obj = data.filter((elem) => {
        if (elem.id == id) return elem;
      });

      if (obj.length > 0) return obj[0];
    },
    getReporter(data) {
      if (!data) return [];

      let result = [];
      let arrData = data.split(",");
      result = arrData.map((item, index) => {
        return this.getObjectValue(this.options.users, item);
      });
      return result;
    },

    onReady(editor) {
      const toolbarContainer = document.querySelector("#toolbar-container");
      toolbarContainer.appendChild(editor.ui.view.toolbar.element);
    },
    preventNav(event) {
      if (!this.isEditing) return;
      event.preventDefault();
      event.returnValue = "";
    },
  },
  watch: {
    selectedItem: [
      {
        handler: function (value,valueOld) {
          const _this = this;
          if(value.content || value.content_ja) _this.isEditing = true;
        },
        deep: true
      },
    ],
    filters: [
      {
        handler: function (value) {
          // if(value.type != this.typeOld){
          //   this.typeOld = value.type;
          //   this.typeReportChange();
          // }
        },
        deep: true
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