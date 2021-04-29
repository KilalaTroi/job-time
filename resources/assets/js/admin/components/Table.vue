<template>
  <div class="table-responsive">
    <table class="table table-hover table-striped" :id="dataPath">
      <thead>
        <tr>
          <th
            v-for="(column, index) in dataCols"
            :key="index"
            :width="column.width"
            :class="column.class"
            :data-filter="column.id"
            :style="{minWidth: column.width+'px', maxWidth: column.width+'px'}"
          >
            {{ column.value }}
          </th>
          <th v-if="dataAction && dataPath" width="110" class="text-center" :style="{minWidth: '110px', maxWidth: '110px'}">
            {{ $ml.with("VueJS").get("txtAction") }}
          </th>
          <slot name="th"></slot>
        </tr>
      </thead>
      <tbody v-if="dataItems.data && dataItems.data.length > 0">
        <tr v-for="(item, index) in dataItems.data" :key="index">
          <td
            v-for="(column, index) in dataCols"
            :key="index"
            :class="column.class"
            :data-filter="column.id"
          >
            <span
              :class="'cl-'+column.id"
              v-html="itemValue(item, column)"
            ></span>
          </td>
          <td v-if="dataAction && dataPath" class="text-center">
            <action
              :dataItem="item"
              :dataPath="dataPath"
              v-if="'all' == dataAction"
            />
            <slot :item="item" name="action"></slot>
          </td>
        </tr>
        <slot name="tr"></slot>
      </tbody>
      <tbody v-else>
        <tr :style="{ textAlign: 'center' }" v-if="dataItems.data">
          <td v-if="dataAction && dataPath" :colspan="dataCols.length + 1" class="text-center">No Data</td>
          <td v-else :colspan="dataCols.length">No Data</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
<script>
import Action from "./Actions/All";
import { mapGetters, mapActions } from "vuex";

export default {
  name: "tbl-default",

  components: {
    Action,
  },

  props: ["dataItems", "dataCols", "dataAction", "dataPath"],

  computed: {
    ...mapGetters({
      itemValue: "table/itemValue",
    }),
  },
};
</script>
<style>
</style>
