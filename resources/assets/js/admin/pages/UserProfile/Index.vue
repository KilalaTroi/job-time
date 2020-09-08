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
                    <table-user class="table-hover table-striped" />
                </div>
            </card>

            <create-item
                    :errors="validationErrors"
                    :success="validationSuccess"
                    :roles="roles"
                    v-on:create-user="createUser">
            </create-item>

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
                columns: 'users/columns',
                users: 'users/items',
                roles: 'users/roles',
                selectedUser: 'users/selectedUser',
                validationErrors: 'users/validationErrors',
                validationSuccess: 'users/validationSuccess'
            })
        },

        methods: {
            createUser(newUser) {
                // Reset validate
                this.validationErrors = "";
                this.validationSuccess = "";

                let uri = "/data/users";
                axios
                    .post(uri, newUser)
                    .then(res => {
                        newUser.r_name = newUser.role;
                        let addIdItem = Object.assign({}, {id: res.data.id}, newUser);
                        this.users = [...this.users, addIdItem];
                        this.validationSuccess = res.data.message;
                    })
                    .catch(err => {
                        console.log(err);
                        if (err.response.status == 422) {
                            this.validationErrors = err.response.data;
                            console.log(this.validationErrors)
                        }
                    });
            }
        },
    };
</script>
