<template>
    <div class="content">
        <div class="container-fluid">
            <card>
                <div class="form-group">
                    <label class="">Users</label>
                    <div>
                        <select2 :options="userOptions" v-model="user_id" class="select2">
                            <option disabled value="0">All</option>
                        </select2>
                    </div>
                </div>
            </card>

            <card class="strpied-tabled-with-hover">
                <template slot="header">
                    <h4 class="card-title">Users list</h4>
                </template>
                <div class="table-responsive">
                    <!-- <no-action-table
                            class="table-hover table-striped"
                            :columns="columns"
                            :data="users">
                    </no-action-table> -->
                </div>
            </card>
        </div>
    </div>
</template>
<script>
    import NoActionTable from "../../components/TableNoAction";
    import Card from "../../components/Cards/Card";
    import CreateButton from "../../components/Buttons/Create";
    import Select2 from '../../components/SelectTwo/SelectTwo.vue';

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
            NoActionTable,
            Card,
            CreateButton,
            Select2
        },

        data() {
            return {
                user_id: 0,
                columns: [...tableColumns],
                users: [],
                userOptions: [],
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
                let uri = "/data/statistic/totaling";
                axios
                    .get(uri)
                    .then(res => {
                        this.users = res.data.users;
                    })
                    .catch(err => {
                        console.log(err);
                        alert("Could not load users");
                    });
            },
            getUserOptions(data) {
                if (data.length) {
                    let dataUsers = [];
                    let obj = {
                        id: 0,
                        text: 'All'
                    };
                    dataUsers.push(obj);

                    for (let i = 0; i < data.length; i++) {
                        let obj = {
                            id: data[i].id,
                            text: data[i].name
                        };
                        dataUsers.push(obj);
                    }
                    this.userOptions = dataUsers;
                }
            },
            getObjectValue(data, id) {
                let obj = data.filter(function(elem) {
                    if (elem.id === id) return elem;
                });

                if (obj.length > 0)
                    return obj[0];
            },
            resetValidate() {
                this.validationSuccess = "";
                this.validationErrors = "";
            }
        },
        watch: {
            users: [{
                handler: 'getUserOptions'
            }]
        }
    };
</script>
