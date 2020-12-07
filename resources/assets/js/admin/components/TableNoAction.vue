<template>
    <table class="table">
        <thead>
            <slot name="columns">
                <tr>
                    <th v-for="(column, index) in columns" :key="index" :width="column.width" :class="column.class">{{ column.value }}</th>
                </tr>
            </slot>
        </thead>
        <tbody>
            <tr v-for="(item, index) in data" :key="index">
                <slot :row="item">
                    <td v-for="(column, index) in columns" :key="index" :class="column.class">
                        <span :class="getStatusClass(item, column)" v-html="itemValue(item, column)"></span>
                    </td>
                </slot>
            </tr>
        </tbody>
    </table>
</template>
<script>
export default {
    name: 'table-no-action',
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
        getStatusClass(item, column) {
            if ( column.id.toLowerCase() === 'status' && item[column.id.toLowerCase()] ) {
                return item[column.id.toLowerCase()].toLowerCase().replace(' ', '-');
            }

            return 'cl-' + column.id;
        }
    }
}
</script>
<style>
</style>