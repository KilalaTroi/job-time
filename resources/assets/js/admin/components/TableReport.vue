<template>
    <table class="table">
        <thead>
            <slot name="columns">
                <tr>
                    <th width="50"><i class="nc-icon nc-bell-55"></i></th>
                    <th v-for="(column, index) in columns" :key="index" :width="column.width" :data-filter="column.id" :class="column.class">{{ column.value }}</th>
                    <th width="120" class="text-center">{{$ml.with('VueJS').get('txtAction')}}</th>
                </tr>
            </slot>
        </thead>
        <tbody>
            <tr v-for="(item, index) in data.data" :key="index">
                <td><i class="nc-icon nc-bell-55" v-if="checkSeen(item.seen)"></i></td>
                <slot :row="item">
                    <td v-for="(column, index) in columns" :key="index" :class="column.class" :data-filter="column.id">
                        <span v-if="checkDateColumn(column)" v-html="removeTime(item[column.id])"></span>
                        <span v-else v-html="itemValue(item, column)"></span>
                    </td>
                </slot>
                <td class="text-center">
                    <i @click="$emit('edit-report', { 'id': item.id, 'seen': checkSeen(item.seen) })" class="fa fa-pencil btn-process" aria-hidden="true"></i>
                    <!--<i @click="$emit('send-report')" class="ml-1 fa fa-paper-plane btn-process" aria-hidden="true"></i>-->
                    <i @click="$emit('view-report', { 'id': item.id, 'seen': checkSeen(item.seen) })" class="ml-1 fa fa-eye btn-process" aria-hidden="true"></i>
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
        data: {},
        userID: String,
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
        },
        checkSeen(data) {
            let arrSeen = data.split(',');
            return !arrSeen.includes(this.userID)
        },
        removeTime(date) {
            return date.split(" ")[0];
        },
        checkDateColumn(column) {
            return column.id == "date_time";
        }
    }
}
</script>
<style lang="scss">
.table .btn-process {
    font-size: 20px;
    width: 24px;
    color: #6c757d;
    cursor: pointer;

    &:hover {
        color: #dc3545;
    }
}

.nc-bell-55 {
    font-size: 20px;
    font-weight: 700;

    th & {
        color: #000;
    }

    td & {
        color: #dc3545;
    }
}
</style>