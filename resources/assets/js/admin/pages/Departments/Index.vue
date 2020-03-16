<template>
    <div class="content">
        <div class="container-fluid">
            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-auto">
                        <create-button>
                            <template slot="title">{{$ml.with('VueJS').get('txtCreateDept')}}</template>
                        </create-button>
                    </div>
                </div>
            </div>

            <card class="strpied-tabled-with-hover">
                <template slot="header">
                    <h4 class="card-title">{{$ml.with('VueJS').get('txtDeptList')}}</h4>
                </template>
                <div class="table-responsive">
                    <action-table
                            class="table-hover table-striped"
                            :columns="columns"
                            :data="departments"
                            v-on:get-item="getItem"
                            v-on:delete-item="deleteItem">
                    </action-table>
                </div>
            </card>

            <create-item
                    :errors="validationErrors"
                    :success="validationSuccess"
                    v-on:create-item="createItem"
                    v-on:reset-validation="resetValidate">
            </create-item>

            <edit-item
                    :errors="validationErrors"
                    :success="validationSuccess"
                    :currentItem="currentItem"
                    v-on:update-item="updateItem"
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
                columns: [
                    { id: "name", value: this.$ml.with('VueJS').get('txtName'), width: "", class: "" },
                    { id: "name_vi", value: this.$ml.with('VueJS').get('txtNameVi'), width: "", class: "" },
                    { id: "name_ja", value: this.$ml.with('VueJS').get('txtNameJa'), width: "", class: "" }
                ],
                departments: [],
                currentItem: null,
                validationErrors: "",
                validationSuccess: ""
            };
        },
        mounted() {
            let _this = this;
            _this.fetchItems();
            $(document).on('click', '.languages button', function() {
                _this.columns = [
                    { id: "name", value: _this.$ml.with('VueJS').get('txtName'), width: "", class: "" },
                    { id: "name_vi", value: _this.$ml.with('VueJS').get('txtNameVi'), width: "", class: "" },
                    { id: "name_ja", value: _this.$ml.with('VueJS').get('txtNameJa'), width: "", class: "" }
                ];
            });
        },
        methods: {
            fetchItems() {
                let uri = "/data/departments";
                axios
                    .get(uri)
                    .then(res => {
                        this.departments = res.data;
                    })
                    .catch(err => {
                        console.log(err);
                        alert("Could not load departments");
                    });
            },
            createItem(newItem) {
                // Reset validate
                this.validationErrors = "";
                this.validationSuccess = "";

                let uri = "/data/departments";
                axios
                    .post(uri, newItem)
                    .then(res => {
                        let addIdItem = Object.assign({}, {id: res.data.id}, newItem);
                        this.departments = [...this.departments, addIdItem];
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
            deleteItem(id) {
                if (confirm(this.$ml.with('VueJS').get('msgConfirmDelete'))) {
                    let uri = "/data/departments/" + id;
                    axios
                        .delete(uri)
                        .then(res => {
                            this.departments = this.departments.filter(item => item.id !== id);
                            console.log(res.data);
                        })
                        .catch(err => console.log(err));
                }
            },
            getItem(id) {
                let uri = "/data/departments/" + id;
                axios.get(uri).then(response => {
                    this.currentItem = response.data;
                });
            },
            updateItem(item) {
                // Reset validate
                this.validationErrors = "";
                this.validationSuccess = "";

                let uri = "/data/departments/" + item.id;
                axios
                    .patch(uri, item)
                    .then(res => {
                        let foundIndex = this.departments.findIndex(x => x.id == item.id);
                        this.departments[foundIndex] = item;
                        this.departments = [...this.departments];
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
