<template>
  <div class="table-responsive">
    <table class="table table-hover table-striped">
      <thead>
        <tr>
          <th
            v-for="(column, index) in dataCols"
            :key="index"
            :width="column.width"
            :class="column.class"
          >
            {{ column.value }}
          </th>
          <th v-if="dataAction && dataPath" width="110" class="text-center">
            {{ $ml.with("VueJS").get("txtAction") }}
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
          >
            <span v-html="itemValue(item, column)"></span>
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
