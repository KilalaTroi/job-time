<template>
  <modal id="viewTableOption" sizeClasses="modal-lg" v-on:reset-validation="resetValidation">
    <template slot="title">Columns Filter</template>
    <ul class="tableColumns">
      <li v-for="(column, index) in dataCols" :key="index" :class="true === column.filter || false === column.filter ? '' : 'd-none'">
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
    const _this = this;

    setTimeout(function(){
      _this.resetValidation();
      _this.checkTableColumns();
      _this.hanldeFliterColumns(_this.checkColumns);
    },100)
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
    },
    checkTableColumns(){
      const _this = this;
      let colsNew = '', colsOld = localStorage.getItem("cols_" + this.dataTable);
      if(!colsOld) colsOld = '';
      this.dataCols.map(function (value) { colsNew += value.id+','; });
      if(colsOld != colsNew){
        this.dataCols.map(function (value, index) {
          if(-1 == colsOld.indexOf(value.id) && -1 == _this.checkColumns.indexOf(value.id))  _this.setFilterColumns(index, value);
        });
        localStorage.setItem("cols_" + _this.dataTable, colsNew)
      }else{
        const fliterColumns = localStorage.getItem("filter_" + this.dataTable);
        if(fliterColumns) this.checkColumns = fliterColumns.split(",");
        else _this.dataCols.map(function (value, index) { _this.setFilterColumns(index, value); });
      }
      localStorage.setItem("filter_" + _this.dataTable, _this.checkColumns);
    },
    setFilterColumns(index, value){
      this.checkColumns.push(value.id)
      if(false === value.filter) this.checkColumns.splice(index, 1);
    }
  },
  watch: {
    dataCols: [
      {
        handler: function (value) { this.hanldeFliterColumns(this.checkColumns); },
        deep: true,
      }
    ],
    dataItems: [
      {
        handler: function (value) { this.hanldeFliterColumns(this.checkColumns); },
        deep: true,
      },
		],
  },
};
</script>
