<template>
  <modal id="viewTableOption" sizeClasses="modal-lg" v-on:reset-validation="resetValidation">
    <template slot="title">View Table Option</template>
    <ul class="tableColumns">
      <li v-for="(column, index) in dataCols" :key="index">
        <input type="checkbox" :value="column.id" v-model="checkColumns" :id="'table_' + column.id" />
        <label :for="'table_' + column.id">{{ column.value }}</label>
      </li>
    </ul>
    <hr />
    <div class="form-group text-right"><button type="button" class="btn btn-primary" data-dismiss="modal" @click="fliterColumns()">Set</button></div>
  </modal>
</template>

<script>
import Modal from "./Modals/Modal.vue";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "view-table-option",
  props: ["dataCols", "dataTable", "dataItems"],
  components: {
    Modal,
  },
  data() {
    return {
      checkColumns: [],
    };
  },
  mounted() {
    this.resetValidation();
    this.checkTableColumns();
    this.hanldeFliterColumns(this.checkColumns);
  },
  methods: {
    fliterColumns() {
			if(this.checkColumns.length > 0){
				localStorage.setItem("filter_" + this.dataTable, this.checkColumns);
				this.hanldeFliterColumns(this.checkColumns);
			}else{
				const fliterColumns = localStorage.getItem("filter_" + this.dataTable);
				if(fliterColumns) this.checkColumns = fliterColumns.split(",");
			}
    },
    hanldeFliterColumns(colunms) {
			const table = "#" + this.dataTable;
			$(table).find("th[data-filter],td[data-filter]").addClass("d-none");
      colunms.map(function (value) { $(table).find("[data-filter='" + value + "']").removeClass("d-none"); });
		},
		resetValidation(){
			const fliterColumns = localStorage.getItem("filter_" + this.dataTable);
			if(fliterColumns) this.checkColumns = fliterColumns.split(",");
			else {
				const _this = this;
				this.dataCols.map(function (value) { _this.checkColumns.push(value.id) });
				localStorage.setItem("filter_" + this.dataTable, this.checkColumns);
			}
    },
    checkTableColumns(){
      let colsNew = '', colsOld = localStorage.getItem("cols_" + this.dataTable);
      if(!colsOld) colsOld = '';

      this.dataCols.map(function (value) { colsNew += value.id+','; });

      if(colsOld != colsNew){
        const _this = this;
        this.dataCols.map(function (value) { if(-1 == colsOld.indexOf(value.id) && -1 == _this.checkColumns.indexOf(value.id)) _this.checkColumns.push(value.id) });
        localStorage.setItem("filter_" + this.dataTable, this.checkColumns);
        localStorage.setItem("cols_" + this.dataTable, colsNew)
      }
    }
  },
  watch: {
    dataItems: [
      {
        handler: function (value) { this.hanldeFliterColumns(this.checkColumns); },
        deep: true,
      },
		],
  },
};
</script>
