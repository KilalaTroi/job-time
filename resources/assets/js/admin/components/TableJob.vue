<template>
    <table class="table">
        <thead>
            <slot name="columns">
                <tr>
                    <th v-for="(column, index) in columns" :key="index" :width="column.width" :class="column.class">{{ column.value }}</th>
                    <th width="110" class="text-center">{{ $ml.with('VueJS').get('txtAction') }}</th>
                </tr>
            </slot>
        </thead>
        <tbody>
            <tr v-for="(item, index) in data" :key="index">
                <slot :row="item">
                    <td v-for="(column, index) in columns" :key="index" :class="column.class">
                        <span>{{itemValue(item, column)}}</span>
                    </td>
                </slot>
                <td class="text-center d-flex justify-content-center">
                    <button type="button" @click="$emit('get-job', item.id)" class="btn btn-xs d-flex btn-primary" data-toggle="modal" data-target="#addTime" data-backdrop="static" data-keyboard="false">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </button>
                    <!-- <button type="button" class="btn btn-xs d-flex btn-default ml-2">
                        <i class="fa fa-history" aria-hidden="true"></i>
                    </button> -->
                </td>
            </tr>
        </tbody>
    </table>
</template>
<script>
export default {
    name: 'table-job',
    props: {
        columns: Array,
        data: Array
    },
    methods: {
        hasValue(item, column) {
            return item[column.id.toLowerCase()]
        },
        itemValue(item, column) {
            return item[column.id.toLowerCase()] ? item[column.id.toLowerCase()] : '--'
        }
    }
}
</script>
<style>
</style>