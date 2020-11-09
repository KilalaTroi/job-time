<template>
    <table class="table">
        <thead>
            <slot name="columns">
                <tr>
                    <th width="110" class="text-center">{{$ml.with('VueJS').get('txtFinish')}}</th>
                    <th v-for="(column, index) in columns" :key="index" :width="column.width" :class="column.class">{{ column.value }}</th>
                    <th width="110" class="text-center">{{$ml.with('VueJS').get('txtAction')}}</th>
                </tr>
            </slot>
        </thead>
        <tbody>
            <tr v-for="(item, index) in data" :key="index">
                <td class="text-center"><i @click="$emit('change-status-process', item)" :class="itemClassActive(item)"></i></td>
                <slot :row="item">
                    <td v-for="(column, index) in columns" :key="index" :class="column.class">
                        <span v-html="itemValue(item, column)"></span>
                    </td>
                </slot>
                <td class="text-center">
                    <i @click="$emit('get-process', item)" class="fa fa-paper-plane btn-process" data-toggle="modal" data-target="#processModal" data-backdrop="static" data-keyboard="false"></i> 
                </td>
            </tr>
        </tbody>
    </table>
</template>
<script>
export default {
    name: 'table-upload',
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
        },
        itemClassActive(item) {
            return item.status ? 'fa fa-flag btn-flag active' : 'fa fa-flag btn-flag'
        }
    }
}
</script>
<style lang="scss">
.btn-process, .btn-flag {
    font-size: 24px;
    width: 24px;
    color: #6c757d;
    cursor: pointer;

    &.active {
        color: #dc3545;
    }
}
.btn-process {
    &:hover {
        color: #dc3545;
    }
}
</style>