<template>
    <div class="content">
        <div class="container-fluid">
            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-auto">
                        <button-create>
                            <template slot="title">{{$ml.with('VueJS').get('txtCreateType')}}</template>
                        </button-create>
                    </div>
                </div>
            </div>

            <card class="strpied-tabled-with-hover">
                <template slot="header">
                    <h4 class="card-title">{{$ml.with('VueJS').get('txtJobTypeList')}}</h4>
                </template>
                <div class="table-responsive">
                    <table-action
                            class="table-hover table-striped"
                            :columns="columns"
                            :data="types"
                            v-on:get-item="getItem"
                            v-on:delete-item="deleteItem">
                    </table-action>
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
    import TableAction from "../../components/TableAction";
    import Card from "../../components/Cards/Card";
    import CreateItem from "./Create";
    import EditItem from "./Edit";
    import CreateButton from "../../components/Buttons/Create";

    const langDefault = document.querySelector("meta[name='user-language']").getAttribute('content');

    export default {
        components: {
            TableAction,
            Card,
            CreateItem,
            EditItem,
            CreateButton
        },
        data() {
            return {
                columns: [
                    {id: 'slug', value: this.$ml.with('VueJS').get('txtSlug'), width: '200', class: ''},
                    {id: 'value', value: this.$ml.with('VueJS').get('txtColor'), width: '110', class: 'text-center'},
                    {id: 'slug_' + langDefault, value: this.$ml.with('VueJS').get('txtName'), width: '200', class: ''},
                    {id: 'description_' + langDefault, value: this.$ml.with('VueJS').get('txtDesc'), width: '', class: ''}
                ],
                types: [],
                langSlug: langDefault,
                currentItem: null,
                validationErrors: "",
                validationSuccess: ""
            };
        },
        mounted() {
            let _this = this;
            _this.fetchItems();
            
            $(document).on('click', '.languages button', function() {
                _this.langSlug = _this.$ml.current;
                _this.columns = [
                    {id: 'slug', value: _this.$ml.with('VueJS').get('txtSlug'), width: '200', class: ''},
                    {id: 'value', value: _this.$ml.with('VueJS').get('txtColor'), width: '110', class: 'text-center'},
                    {id: 'slug_' + _this.langSlug, value: _this.$ml.with('VueJS').get('txtName'), width: '200', class: ''},
                    {id: 'description_' + _this.langSlug, value: _this.$ml.with('VueJS').get('txtDesc'), width: '', class: ''}
                ];
            });
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
                if (confirm(this.$ml.with('VueJS').get('msgConfirmDelete'))) {
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
