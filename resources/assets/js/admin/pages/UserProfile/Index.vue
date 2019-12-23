<template>
    <div class="content">
        <div class="container-fluid">
            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-auto">
                        <create-button>
                            <template slot="title">Create new user</template>
                        </create-button>
                    </div>
                </div>
            </div>

            <card class="strpied-tabled-with-hover">
                <template slot="header">
                    <h4 class="card-title">Users list</h4>
                </template>
                <div class="table-responsive">
                    <action-table
                            class="table-hover table-striped"
                            :columns="columns"
                            :data="users"
                            v-on:get-item="getUser"
                            v-on:delete-item="deleteUser">
                    </action-table>
                </div>
            </card>

            <create-item
                    :errors="validationErrors"
                    :success="validationSuccess"
                    :roles="roles"
                    v-on:create-user="createUser"
                    v-on:reset-validation="resetValidate">
            </create-item>

            <edit-item
                    :errors="validationErrors"
                    :success="validationSuccess"
                    :currentUser="currentUser"
                    :roles="roles"
                    v-on:update-user="updateUser"
                    v-on:reset-validation="resetValidate">
            </edit-item>
        </div>
    </div>
</template>
<script>
    import ActionTable from "../../components/TableAction";
    import Card from "../../components/Cards/Card";
    import CreateItem from "./Create";
    import EditItem from "./Edit";
    import CreateButton from "../../components/Buttons/Create";

    const tableColumns = [
        {
            id: "username",
            value: "Username",
            width: "120",
            class: ""
        },
        {
            id: "r_name",
            value: "Role",
            width: "120",
            class: ""
        },
        {
            id: "name",
            value: "Name",
            width: "",
            class: ""
        },
        {
            id: "email",
            value: "Email",
            width: "",
            class: ""
        }
    ];

    export default {
        components: {
            ActionTable,
            Card,
            CreateItem,
            EditItem,
            CreateButton
        },

        data() {
            return {
                columns: [...tableColumns],
                users: [],
                roles: [],
                currentUser: {},
                currentRole: {},
                validationErrors: "",
                validationSuccess: ""
            };
        },
        mounted() {
            this.fetchUsers();
        },
        methods: {
            fetchUsers() {
                let uri = "/data/users";
                axios
                    .get(uri)
                    .then(res => {
                        this.users = res.data.users;
                        this.roles = res.data.roles;
                    })
                    .catch(err => {
                        console.log(err);
                        alert("Could not load users");
                    });
            },
            getObjectValue(data, id) {
                let obj = data.filter(function(elem) {
                    if (elem.id === id) return elem;
                });

                if (obj.length > 0)
                    return obj[0];
            },
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
            },
            deleteUser(id) {
                if (confirm("Are you sure want to delete this record?")) {
                    let uri = "/data/users/" + id;
                    axios
                        .delete(uri)
                        .then(res => {
                            this.users = this.users.filter(item => item.id !== id);
                            console.log(res.data);
                        })
                        .catch(err => console.log(err));
                }
            },
            getUser(id) {
                this.currentUser = this.getObjectValue(this.users, id);
            },
            updateUser(user) {
                // Reset validate
                this.validationErrors = "";
                this.validationSuccess = "";

                let uri = "/data/users/" + user.id;
                axios
                    .patch(uri, user)
                    .then(res => {
                        let foundIndex = this.users.findIndex(x => x.id == user.id);
                        this.users[foundIndex] = user;
                        this.validationSuccess = res.data.message;
                    })
                    .catch(err => {
                        console.log(err);
                        if (err.response.status == 422) {
                            this.validationErrors = err.response.data;
                        }
                    });
            },
            resetValidate() {
                this.validationSuccess = "";
                this.validationErrors = "";
            }
        }
    };
</script>
