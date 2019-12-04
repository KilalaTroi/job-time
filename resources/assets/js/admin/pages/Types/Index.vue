<template>
    <div class="content">
        <div class="container-fluid">
            <div class="form-group">
                <create-button>
                    <template slot="title">Create new type</template>
                </create-button>
            </div>

            <card class="strpied-tabled-with-hover">
                <template slot="header">
                    <h4 class="card-title">Types list</h4>
                </template>
                <action-table
                        class="table-hover table-bordered table-striped"
                        :columns="columns"
                        :data="types"
                        v-on:get-item="getItem"
                        v-on:delete-item="deleteItem">
                </action-table>
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

    const tableColumns = [
        {id: 'slug', value: 'Slug', width: '', class: ''},
        {id: 'value', value: 'Type color', width: '110', class: 'text-center'},
        {id: 'slug_vi', value: 'Slug VI', width: '', class: ''},
        {id: 'slug_ja', value: 'Slug JA', width: '', class: ''}
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
                types: [],
                currentItem: null,
                validationErrors: "",
                validationSuccess: ""
            };
        },
        mounted() {
            this.fetchItems();
        },
        methods: {
            fetchItems() {
                let uri = "/data/types";
                axios
                    .get(uri)
                    .then(res => {
                        this.types = res.data;
                    })
                    .catch(err => {
                        console.log(err);
                        alert("Could not load types");
                    });
            },
            createItem(newItem) {
                // Reset validate
                this.validationErrors = "";
                this.validationSuccess = "";

                let uri = "/data/types";
                axios
                    .post(uri, newItem)
                    .then(res => {
                        let addIdItem = Object.assign({}, {id: res.data.id}, newItem);
                        this.types = [...this.types, addIdItem];
                        this.validationSuccess = res.data.message;
                    })
                    .catch(err => {
                        console.log(err);
                        if (err.response.status == 422) {
                            this.validationErrors = err.response.data;
                        }
                    });
            },
            deleteItem(id) {
                if (confirm("Are you sure want to delete this record?")) {
                    let uri = "/data/types/" + id;
                    axios
                        .delete(uri)
                        .then(res => {
                            this.types = this.types.filter(item => item.id !== id);
                            console.log(res.data);
                        })
                        .catch(err => console.log(err));
                }
            },
            getItem(id) {
                let uri = "/data/types/" + id;
                axios.get(uri).then(response => {
                    this.currentItem = response.data;
                });
            },
            updateItem(item) {
                // Reset validate
                this.validationErrors = "";
                this.validationSuccess = "";

                let uri = "/data/types/" + item.id;
                axios
                    .patch(uri, item)
                    .then(res => {
                        let foundIndex = this.types.findIndex(x => x.id == item.id);
                        this.types[foundIndex] = item;
                        this.types = [...this.types];
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
<style lang="scss">
    .type-color {
        width: 60px;
        height: 20px;
        display: inline-block;
        vertical-align: middle;
    }
</style>
