<template>
    <div class="content">
        <div class="container-fluid">
            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-auto">
                        <button-create>
                            <template slot="title">{{$ml.with('VueJS').get('txtCreateUser')}}</template>
                        </button-create>
                    </div>
                </div>
            </div>

            <card class="strpied-tabled-with-hover">
                <template slot="header">
                    <h4 class="card-title">{{$ml.with('VueJS').get('txtUserList')}}</h4>
                </template>
                <div class="table-responsive">
                    <table-user class="table-hover table-striped"/>
                </div>
            </card>

            <create-item/>
            <edit-item/>
        </div>
    </div>
</template>
<script>
    import TableUser from "../../components/TableUser"
    import Card from "../../components/Cards/Card"
    import CreateItem from "./Create"
    import EditItem from "./Edit"
    import ButtonCreate from "../../components/Buttons/Create"
    import { mapGetters, mapActions } from 'vuex'

    export default {
        components: {
            TableUser,
            Card,
            CreateItem,
            EditItem,
            ButtonCreate
        },

        computed: {
            ...mapGetters({
                roles: 'users/roles'
            })
        },

        methods: {
            ...mapActions({
                getAllUser: 'users/getAllUser',
                getRoleOptions: 'users/getRoleOptions'
            })
        },

        mounted() {
            const _this = this
            _this.getAllUser()
        },

        watch: {
            roles: [{
                handler: 'getRoleOptions'
            }]
        }
    };
</script>
