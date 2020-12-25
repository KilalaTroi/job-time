<template>
  <div class="content">
    <div class="container-fluid">
      <card>
        <div class="row">
          <div class="col-sm-3">
            <div class="form-group">
              <label class>{{ $ml.with("VueJS").get("txtUsers") }}</label>
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
          <div class="col-sm-3">
            <div class="form-group">
              <label class>{{ $ml.with("VueJS").get("txtStartDate") }}</label>
              <datepicker
                name="startDate"
                input-class="form-control"
                placeholder="Select Date"
                v-model="filters.start_date"
                :format="dateFormat(filters.start_date,'YYYY-MM-DD')"
                :disabled-dates="disabledEndDates(filters.end_date)"
                :language="getLangCode(this.$ml)"
              ></datepicker>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="form-group">
              <label class>{{ $ml.with("VueJS").get("txtEndDate") }}</label>
              <datepicker
                name="endDate"
                input-class="form-control"
                placeholder="Select Date"
                v-model="filters.end_date"
                :format="dateFormat(filters.end_date,'YYYY-MM-DD')"
                :disabled-dates="disabledStartDates(filters.start_date)"
                :language="getLangCode(this.$ml)"
              ></datepicker>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="form-group">
              <label class>{{ $ml.with("VueJS").get("txtDepts") }}</label>
              <div>
                <multiselect
                  :multiple="true"
                  v-model="filters.departments"
                  :options="options.departments"
                  :clear-on-select="false"
                  :preserve-search="true"
                  :placeholder="$ml.with('VueJS').get('txtPickSome')"
                  label="text"
                  track-by="text"
                ></multiselect>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="form-group">
              <label class>{{ $ml.with("VueJS").get("txtProjects") }}</label>
              <div>
                <multiselect
                  :multiple="true"
                  v-model="filters.projects"
                  :options="options.projects"
                  :clear-on-select="false"
                  :preserve-search="true"
                  :placeholder="$ml.with('VueJS').get('txtPickSome')"
                  label="text"
                  track-by="text"
                ></multiselect>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="form-group">
              <label class>
                {{ $ml.with("VueJS").get("txtIssue") }}
              </label>
              <input
                v-model="filters.issue"
                type="text"
                name="issue"
                class="form-control"
              />
            </div>
          </div>
          <div class="col-sm-3">
            <div class="form-group">
              <label class>{{ $ml.with("VueJS").get("txtTypes") }}</label>
              <div>
                <multiselect
                  :multiple="true"
                  v-model="filters.types"
                  :options="options.types"
                  :clear-on-select="false"
                  :preserve-search="true"
                  :placeholder="$ml.with('VueJS').get('txtPickSome')"
                  label="slug"
                  track-by="slug"
                >
                  <template slot="option" slot-scope="props">
                    <div>
                      <span
                        class="type-color"
                        :style="optionStyle(props.option.value)"
                      ></span>
                      {{ props.option.slug }}
                    </div>
                  </template>
                </multiselect>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
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
      </card>

      <card class="strpied-tabled-with-hover">
        <template slot="header">
          <div class="d-flex justify-content-between">
            <h4 class="card-title">
              {{ $ml.with("VueJS").get("txtTimeRecord") }}
            </h4>
            <div class="align-self-end">
              <button @click="exportExcel" class="btn btn-primary">
                <i class="fa fa-download"></i>
                {{ $ml.with("VueJS").get("txtExportExcel") }}
              </button>
              <button-view-table-option class="mt-0" />
            </div>
          </div>
        </template>
        <div
          class="table-responsive"
        >
          <tbl-default
            :dataItems="totalingData"
            :dataCols="columns"
            dataPath="totaling"
          />
          <!-- <table-no-action class="table-hover table-striped" :columns="columns" :data="logTime.data"></table-no-action> -->
          <div v-if="!totalingData" class="text-center mt-3">
            <img src="https://i.imgur.com/JfPpwOA.gif" />
          </div>
        </div>
				<pagination
          :data="totalingData"
          :show-disabled="true"
          :limit="2"
          align="right"
          size="small"
          @pagination-change-page="getAll"
        ></pagination>
      </card>
      <view-table-option
        dataTable="totaling"
        :dataItems="totalingData"
        :dataCols="columns"
      />
    </div>
  </div>
</template>
<script>
import TblDefault from "../../components/Table";
// import TableNoAction from "../../components/TableNoAction";
import Card from "../../components/Cards/Card";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import Multiselect from "vue-multiselect";
import Datepicker from "vuejs-datepicker";
import { vi, ja, en } from "vuejs-datepicker/dist/locale";
import moment from "moment";
import ButtonViewTableOption from "../../components/Buttons/ViewTableOption";
import ViewTableOption from "../../components/ModalViewTableOption";
import { mapGetters, mapActions } from "vuex";

// TableNoAction,
export default {
  components: {
    TblDefault,
    Card,
    Datepicker,
    Select2,
    Multiselect,
    ButtonViewTableOption,
    ViewTableOption,
  },

  computed: {
    ...mapGetters({
      currentTeamOption: "currentTeamOption",
      currentTeam: "currentTeam",
      getObjectByID: "getObjectByID",
			getTeamText: "getTeamText",
			dateFormat: "dateFormat",
      getLangCode: "getLangCode",
      disabledStartDates: "disabledStartDates",
      disabledEndDates: "disabledEndDates"
    }),
    ...mapGetters("totaling", {
			columns: "columns",
			filters: "filters",
			totalingData: "data",
			options: "options",
    }),
  },

  methods: {
    ...mapActions({
      setCurrentTeam: "setCurrentTeam",
		}),

		...mapActions("totaling", {
			setColumns: "setColumns",
			getAll: "getAll",
			exportExcel: "exportExcel",
    }),

    optionStyle(color) {
      return {
        backgroundColor: color,
      };
    },
	},
	async created(){
		const _this = this;
		_this.filters.team = _this.currentTeam.id;
		_this.setColumns();
    $(document).on("click", ".languages button", function () {
			_this.setColumns();
    });
  },
  watch: {
    filters: [
      {
        handler: function(value,valueOld){
          console.log(value,valueOld)
					if (value.team != this.currentTeam.id) {
						this.setCurrentTeam(value.team);
					}
					this.getAll()
				},
				deep: true,
      },
    ],
  },
};
</script>
<style lang="scss">
@import "~vue-multiselect/dist/vue-multiselect.min.css";
.type-color {
  width: 30px;
  height: 20px;
  margin-right: 5px;
  display: inline-block;
  vertical-align: middle;
}
</style>
