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
                    <table-user
                            class="table-hover table-striped"
                            :columns="columns"
                            :data="users"
                            v-on:get-item="getUser"
                            v-on:archive-user="archiveUser"
                            v-on:delete-item="deleteUser">
                    </table-user>
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
    import TableUser from "../../components/TableUser";
    import Card from "../../components/Cards/Card";
    import CreateItem from "./Create";
    import EditItem from "./Edit";
    import ButtonCreate from "../../components/Buttons/Create";
    import moment from 'moment';

    export default {
        components: {
            TableUser,
            Card,
            CreateItem,
            EditItem,
            ButtonCreate
        },

        data() {
            return {
                columns: [
                    { id: "username", value: this.$ml.with('VueJS').get('lblUsername'), width: "120", class: "" },
                    { id: "r_name", value: this.$ml.with('VueJS').get('txtRole'), width: "120", class: "" },
                    { id: "name", value: this.$ml.with('VueJS').get('txtName'), width: "120", class: "" },
                    { id: "email", value: this.$ml.with('VueJS').get('txtEmail'), width: "120", class: "" }
                ],
                users: [],
                roles: [],
                currentUser: {},
                currentRole: {},
                validationErrors: "",
                validationSuccess: ""
            };
        },
        mounted() {
            let _this = this;
            _this.fetchUsers();
            $(document).on('click', '.languages button', function() {
                _this.columns = [
                    { id: "username", value: _this.$ml.with('VueJS').get('lblUsername'), width: "120", class: "" },
                    { id: "r_name", value: _this.$ml.with('VueJS').get('txtRole'), width: "120", class: "" },
                    { id: "name", value: _this.$ml.with('VueJS').get('txtName'), width: "120", class: "" },
                    { id: "email", value: _this.$ml.with('VueJS').get('txtEmail'), width: "120", class: "" }
                ];
            });
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
                if (confirm(this.$ml.with('VueJS').get('msgConfirmDelete'))) {
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
            archiveUser(item) {
                let disable_date = !item.disable_date ? moment().format('YYYY-MM-DD') : null;
                let uri = '/data/users/archive/' + item.id + '/' + disable_date;

                axios.get(uri).then((response) => {
                    let foundIndex = this.users.findIndex(x => x.id == item.id);
                    this.users[foundIndex].disable_date = disable_date;
                    this.users = [...this.users];
                }).catch(err => console.log(err));
            },
            resetValidate() {
                this.validationSuccess = "";
                this.validationErrors = "";
                this.currentUser = {};
            }
        }
    };
</script>
