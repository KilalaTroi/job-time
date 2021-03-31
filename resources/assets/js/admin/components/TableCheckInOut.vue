<template>
  <div class="table-responsive">
    <table class="table table-hover table-striped" id="checkinout">
      <thead>
        <tr>
          <th
            v-for="(column, index) in dataCols"
            :key="index"
            :width="column.width"
            :class="column.class"
            :data-filter="column.id"
            :style="{
              minWidth: column.width + 'px',
              maxWidth: column.width + 'px',
            }"
          >
            {{ column.value }}
          </th>
          <slot name="th"></slot>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in dataItems.data" :key="index">
          <td
            v-for="(column, index) in dataCols"
            :key="index"
            :class="column.class"
            :data-filter="column.id"
          >
            <button
              v-if="'reason' == column.id"
              @click="
                getItemReason({
                  id: item.userid,
                  date: item.date,
                })
              "
              type="button"
              class="btn btn-xs btn-default mr-1"
              data-toggle="modal"
              data-target="#UpdateReason"
              data-backdrop="static"
              data-keyboard="false"
            >
              <i class="fa fa-pencil" aria-hidden="true"></i>
            </button>
            <span
              :class="'cl-' + column.id"
              v-html="itemValue(item, column)"
            ></span>
          </td>
        </tr>
        <slot name="tr"></slot>
      </tbody>
    </table>
  </div>
</template>
<script>
import { mapGetters, mapActions } from "vuex";

export default {
  name: "tbl-checkinout",

  props: ["dataItems", "dataCols"],

  computed: {
    ...mapGetters({
      itemValue: "table/itemValue",
    }),
  },

  methods: {
    ...mapActions("checkinout", {
      getItemReason: "getItemReason",
    }),
  },
};
</script>
<style>
</style>
