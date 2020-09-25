<template>
  <table class="table">
    <thead>
      <slot name="columns">
        <tr>
          <th
            v-for="(column, index) in columns.time_record"
            :key="index"
            :width="column.width"
            :class="column.class"
          >{{ column.value }}</th>
          <th width="110" class="text-center">{{ $ml.with('VueJS').get('txtAction') }}</th>
        </tr>
      </slot>
    </thead>
    <tbody>
      <tr v-for="(item, index) in items.time_record" :key="index">
        <slot :row="item">
          <td v-for="(column, index) in columns" :key="index" :class="column.class">
            <span v-html="itemValue(item, column)"></span>
          </td>
        </slot>
        <td class="text-center">
          <button
            @click="getDepartmentById(item.id)"
            type="button"
            class="btn btn-xs btn-default"
            data-toggle="modal"
            data-target="#itemDetail"
            data-backdrop="static"
            data-keyboard="false"
          >
            <i class="fa fa-pencil" aria-hidden="true"></i>
          </button>

          <button
            @click="deleteDepartment({id: item.id, msgText: $ml.with('VueJS').get('msgConfirmDelete')})"
            type="button"
            class="btn btn-xs btn-danger ml-sm-2"
          >
            <i class="fa fa-trash" aria-hidden="true"></i>
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</template>
<script>
import { mapGetters, mapActions } from "vuex";

export default {
  name: "table-job-time-record",

  computed: {
    ...mapGetters({
      columns: "jobs/columns",
      items: "jobs/items",
      // itemValue: "table/itemValue"
    }),
  },

  methods: {
    ...mapActions({
      setColumns: "jobs/setColumns",
      getAllJob: "jobs/getAllJob",
      // deleteDepartment: "jobs/deleteDepartment",
      // getDepartmentById: "jobs/getDepartmentById",
    }),
  },

  mounted() {
    const _this = this;
    const _translate = _this.$ml.with("VueJS");
    _this.setColumns(_translate);
    _this.getAllJob();
    $(document).on("click", ".languages button", function () {
      _this.setColumns(_translate);
    });
  },
};
</script>
<style>
</style>
