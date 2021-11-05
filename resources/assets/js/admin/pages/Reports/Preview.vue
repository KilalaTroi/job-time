<template>
  <card class="preview">
    <template slot="header">
      <div class="d-flex justify-content-between">
        <h4 class="card-title">{{ $ml.with("VueJS").get("txtPreview") }}</h4>
        <div class="align-self-end">
          <!-- <button @click="$emit('send-report')" class="btn btn-success btn-process mr-3"><i class="fa fa-paper-plane" aria-hidden="true"></i></button> -->
          <button @click="exportPDF()"  class="btn btn-danger mr-3">
            {{ $ml.with("VueJS").get("txtExportPDF") }}
          </button>
          <button @click="backToList()" class="btn btn-primary">
            {{ $ml.with("VueJS").get("txtBack") }}
          </button>
        </div>
      </div>
    </template>
    <div class="row">
      <div class="col-sm-6">
        <div class="form-group">
          <label><strong>{{ $ml.with("VueJS").get("txtTitle") }}</strong></label>
          <input
            v-if="selectedItem.language == 'vi'"
            :value="selectedItem.title"
            type="text"
            class="form-control"
            :disabled="true"
          />
          <input
            v-if="selectedItem.language == 'ja'"
            :value="selectedItem.title_ja"
            type="text"
            class="form-control"
            :disabled="true"
          />
        </div>
      </div>

      <div class="col-sm-3">
        <div class="form-group">
          <label><strong>{{ $ml.with("VueJS").get("txtReportType") }}</strong></label>
          <input
            :value="filters.type"
            type="text"
            class="form-control"
            :disabled="true"
          />
        </div>
      </div>

      <div class="col-sm-3">
        <div class="form-group">
          <label><strong>{{ $ml.with("VueJS").get("txtTeam") }}</strong></label>
					 <input :value="selectedItem.team_name" type="text" class="form-control" :disabled="true">
        </div>
      </div>
    </div>
    <div class="row form-group">
      <div class="col-sm-3">
        <div class="form-group">
          <label><strong>{{ $ml.with("VueJS").get("lblDate") }}</strong></label>
          <input
            :value="selectedItem.date_time"
            type="text"
            class="form-control"
            :disabled="true"
          />
        </div>
      </div>

      <div class="col-sm-9">
        <div class="form-group">
          <label><strong>{{ $ml.with("VueJS").get("txtReporter") }}</strong></label>
          <input
            :value="selectedItem.reporter"
            type="text"
            class="form-control"
            :disabled="true"
          />
        </div>
      </div>

      <div class="col-sm-12" v-if="'Meeting' == filters.type || 'Notice' == filters.type">
        <div class="form-group">
          <label v-if="'Notice' == filters.type"><strong>{{ $ml.with("VueJS").get("txtDestination") }}</strong></label>
          <label v-else><strong>{{ $ml.with("VueJS").get("txtAttendPerson") }}</strong></label>
          <input
            :value="selectedItem.attend_person"
            type="text"
            class="form-control"
            :disabled="true"
          />
        </div>
      </div>

      <div class="col-sm-9" v-if="'Meeting' == filters.type || 'Notice' == filters.type">
        <div class="form-group">
          <label><strong>{{ $ml.with("VueJS").get("txtAttendPerson") }} ({{ $ml.with("VueJS").get("txtOther") }})</strong></label>
          <input
            :value="selectedItem.attend_other_person"
            type="text"
            class="form-control"
            :disabled="true"
          />
        </div>
      </div>

      <div class="col-sm-3" v-if="'Meeting' != filters.type && 'Notice' != filters.type">
        <div class="form-group">
          <label><strong>{{ $ml.with("VueJS").get("txtDepts") }}</strong></label>
          <input
            :value="filters.department.text"
            type="text"
            class="form-control"
            :disabled="true"
          />
        </div>
      </div>
      <div class="col-sm-3" v-if="'Meeting' != filters.type && 'Notice' != filters.type">
        <div class="form-group">
          <label><strong>{{ $ml.with("VueJS").get("txtProjects") }}</strong></label>
          <input
            :value="filters.project.text"
            type="text"
            class="form-control"
            :disabled="true"
          />
        </div>
      </div>
      <div class="col-sm-2" v-if="'Meeting' != filters.type && 'Notice' != filters.type">
        <div class="form-group">
          <label><strong>{{ $ml.with("VueJS").get("txtYearOfIssue") }}</strong></label>
          <input
            :value="filters.issue_year.text"
            type="text"
            class="form-control"
            :disabled="true"
          />
        </div>
      </div>
      <div class="col-sm-2" v-if="'Meeting' != filters.type && 'Notice' != filters.type">
        <div class="form-group">
          <label><strong>{{ $ml.with("VueJS").get("txtIssue") }}</strong></label>
          <input
            :value="filters.issue.text"
            type="text"
            class="form-control"
            :disabled="true"
          />
        </div>
      </div>
      <div class="col-sm-2">
        <div class="form-group">
          <label><strong>{{ $ml.with("VueJS").get("txtLang") }}</strong></label>
          <select-2 v-model="selectedItem.language" class="select2">
            <option value="vi">{{ $ml.with("VueJS").get("txtVi") }}</option>
            <option value="ja">{{ $ml.with("VueJS").get("txtJa") }}</option>
          </select-2>
        </div>
      </div>
      <div class="col-sm-12" v-if="attachFile">
        <div class="form-group">
          <label><strong>{{ $ml.with("VueJS").get("txtAttachFile") }}</strong></label>
          <div class="input-group mb-3">
            <input v-model="attachFile" type="text" disabled class="form-control" />
            <div class="input-group-append">
              <a :href="getUrlFile(attachFile)" class="input-group-text" target="_blank">
                <i class="fa fa-eye"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="form-group">
      <div id="toolbar-container"></div>
      <div id="ck-editor">
        <ckeditor
          v-if="selectedItem.language == 'vi'"
          :editor="editor"
          :value="selectedItem.content"
          :disabled="true"
        ></ckeditor>
        <ckeditor
          v-if="selectedItem.language == 'ja'"
          :editor="editor"
          :value="selectedItem.content_ja"
          :disabled="true"
        ></ckeditor>
      </div>
    </div>
  </card>
</template>
<script>
import moment from "moment";
import DecoupledEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import Card from "../../components/Cards/Card";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "preview",
  components: {
		Select2,
    Card,
	},

	computed: {
      ...mapGetters('reports',{
        filters: "filters",
        selectedItem: "selectedItem",
        options: "options",
      }),

      attachFile: function () {
        return this.selectedItem.language == 'vi' ? this.selectedItem.attach_file : this.selectedItem.attach_file_ja;
      }
  },

  data() {
    return {
      editor: DecoupledEditor,
    };
	},

  async created() {
		const _this = this;
		_this.selectedItem.reporter = this.getReporter(_this.selectedItem.reporter);
    _this.selectedItem.attend_person = this.getReporter(_this.selectedItem.attend_person);
    _this.selectedItem.language = _this.$ml.current;
    if (_this.selectedItem.isSeen) _this.updateSeen();
  },

  methods: {
    ...mapActions("reports", {
      backToList: "backToList",
			updateSeen: "updateSeen",
			exportPDF: "exportPDF"
    }),

    getObjectValue(data, id) {
      let obj = data.filter((elem) => {
        if (elem.id == id) return elem;
      });

      if (obj.length > 0) return obj[0];
    },

    getUrlFile(attachFile) {
      return 'https://docs.google.com/gview?url=' + window.location.origin + '/attach-file/' + encodeURIComponent(attachFile);
    },

    getReporter(data) {
      let result = [];
      if(data){
        let arrData = data.split(',');
        result = arrData.map((item, index) => {
            return this.getObjectValue(this.options.users, item).text;
        });
        return result.join(', ');
      }
		},
  },
};
</script>

<style lang="scss">
.preview #ck-editor {
  border: 1px solid rgba(0, 0, 0, 0.15);
}
.btn-process {
  color: #6c757d;
  cursor: pointer;

  &:hover {
    color: #dc3545;
  }
}
</style>