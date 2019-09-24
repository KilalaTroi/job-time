<template>
  <table class="table">
    <thead>
    <slot name="columns">
      <tr>
        <th v-for="(column, index) in columns" :key="index" :width="column.width" :class="column.class">{{ column.value }}</th>
        <th width="110" class="text-center">Action</th>
      </tr>
    </slot>
    </thead>
    <tbody>
    <tr v-for="(item, index) in data" :key="index">
      <slot :row="item">
        <td v-for="(column, index) in columns" :key="index" v-if="hasValue(item, column)">{{itemValue(item, column)}}</td>
        <td v-else>--</td>
      </slot>
      <td class="text-center">
        <button @click="$emit('get-item', item.id)" type="button" class="btn btn-xs btn-default"
                data-toggle="modal" data-target="#itemDetail" data-backdrop="static" data-keyboard="false">
          <i class="fa fa-pencil" aria-hidden="true"></i>
        </button>
        <button @click="$emit('delete-item', item.id)" type="button"
                class="btn btn-xs btn-danger ml-2">
          <i class="fa fa-times" aria-hidden="true"></i>
        </button>
      </td>
    </tr>
    </tbody>
  </table>
</template>
<script>
    export default {
        name: 'action-table',
        props: {
            columns: Array,
            data: Array
        },
        methods: {
            hasValue (item, column) {
                return item[column.id.toLowerCase()]
            },
            itemValue (item, column) {
                return item[column.id.toLowerCase()]
            }
        }
    }
</script>
<style>
</style>
