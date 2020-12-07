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
            <tr v-for="(item, index) in users" :key="index">
                <slot :row="item">
                    <td v-for="(column, index) in columns" :key="index" :class="column.class">
                        <button v-if="index === 0" @click="getUserById(item.id)" type="button" class="btn btn-xs btn-default"
                                data-toggle="modal" data-target="#itemDetail" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </button>
                        <span class="team-list-text" v-if="column.id == 'team'" v-html="getTeamText(item[column.id.toLowerCase()])"></span>
                        <span v-else v-html="itemValue(item, column)"></span>
                    </td>
                </slot>
                <td class="text-center">
                    <button @click="getUserById(item.id)" type="button" class="btn btn-xs btn-default"
                            data-toggle="modal" data-target="#itemDetail" data-backdrop="static" data-keyboard="false">
                        <i class="fa fa-pencil" aria-hidden="true"></i>
                    </button>

                    <button @click="archiveUser(item)" type="button" class="btn btn-xs btn-second ml-sm-2">
                        <i :class="archiveClass(item.disable_date)" aria-hidden="true" title="archive"></i>
                    </button>

                    <button @click="deleteUser({id: item.id, msgText: $ml.with('VueJS').get('msgConfirmDelete')})" type="button"
                            class="btn btn-xs btn-danger ml-sm-2">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

export default {
    name: 'table-user',

    computed: {
        ...mapGetters({
            getTeamText: 'getTeamText',
            columns: 'users/columns',
            users: 'users/items',
            itemValue: 'table/itemValue',
            archiveClass: 'table/archiveClass',
        })
    },

    methods: {
        ...mapActions({
            setColumns: 'users/setColumns',
            deleteUser: 'users/deleteUser',
            archiveUser: 'users/archiveUser',
            getUserById: 'users/getUserById'
        })
    },

    mounted() {
        const _this = this
        _this.setColumns()
        $(document).on('click', '.languages button', function() {
            _this.setColumns()
        })
    }
}
</script>
<style>
</style>
