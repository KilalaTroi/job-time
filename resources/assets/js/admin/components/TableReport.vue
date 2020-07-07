<template>
    <table class="table">
        <thead>
            <slot name="columns">
                <tr>
                    <th v-for="(column, index) in columns" :key="index" :width="column.width" :class="column.class">{{ column.value }}</th>
                    <th width="120" class="text-center">{{$ml.with('VueJS').get('txtAction')}}</th>
                </tr>
            </slot>
        </thead>
        <tbody>
            <tr v-for="(item, index) in data" :key="index">
                <slot :row="item">
                    <td v-for="(column, index) in columns" :key="index" :class="column.class">
                        <span v-html="itemValue(item, column)"></span>
                    </td>
                </slot>
                <td class="text-center">
                    <i @click="$emit('edit-report', item.id)" class="fa fa-pencil btn-process" aria-hidden="true"></i>  
                    <i @click="$emit('send-report', item.id)" class="ml-1 fa fa-paper-plane btn-process" aria-hidden="true"></i> 
                    <i @click="$emit('view-report', item.id)" class="ml-1 fa fa-eye btn-process" aria-hidden="true"></i>  
                </td>
            </tr>
        </tbody>
    </table>
</template>
<script>
export default {
    name: 'table-report',
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
.btn-process {
    font-size: 20px;
    width: 24px;
    color: #6c757d;
    cursor: pointer;

    &:hover {
        color: #dc3545;
    }
}
</style>