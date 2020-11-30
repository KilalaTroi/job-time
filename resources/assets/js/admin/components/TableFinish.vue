<template>
    <table class="table">
        <thead>
            <slot name="columns">
                <tr>
                    <!-- <th width="110" class="text-center">{{$ml.with('VueJS').get('txtFinish')}}</th> -->
                    <th v-for="(column, index) in columns" :key="index" :width="column.width" :class="column.class">{{ column.value }}</th>
                    <th width="110" class="text-center">{{$ml.with('VueJS').get('txtDetails')}}</th>
                    <th width="110" class="text-center">{{$ml.with('VueJS').get('txtAction')}}</th>
                </tr>
            </slot>
        </thead>
        <tbody>
            <tr v-for="(item, index) in data" :key="index">
                <!-- <td class="text-center"><i @click="$emit('change-status-process', item)" :class="itemClassActive(item)"></i></td> -->
                <slot :row="item">
                    <td v-for="(column, index) in columns" :key="index" :class="column.class">
                        <span :class="getStatusClass(item, column)" v-html="itemValue(item, column)"></span>
                    </td>
                </slot>
                <td class="text-center">
                    <button @click="$emit('get-process', item)" class="btn text-white bg-secondary" data-toggle="modal" data-target="#processDetailModal" data-backdrop="static" data-keyboard="false">{{$ml.with('VueJS').get('txtDetails')}}</button> 
                </td>
                <td class="text-center">
                    <i @click="$emit('update-process', item)" class="fa fa-paper-plane btn-process" data-toggle="modal" data-target="#processModal" data-backdrop="static" data-keyboard="false"></i> 
                </td>
            </tr>
        </tbody>
    </table>
</template>
<script>
export default {
    name: 'table-finish',
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
        },
        getStatusClass(item, column) {
            if ( column.id.toLowerCase() === 'status' && item[column.id.toLowerCase()] ) {
                return item[column.id.toLowerCase()].toLowerCase().replace(' ', '-');
            }
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
.start-working, .finished-work, .start-uploading, .finished-upload {
    padding: 5px 10px;
    border: 1px solid #231f20;
    font-size: 12px;
}
.start-working {
    background-color: #6dcff6;
}
.finished-work {
    background-color: #a5ce9a;
}
.start-uploading {
    background-color: #fff799;
}
.finished-upload {
    background-color: #f49ac1;
}
</style>