<template>
    <div class="content">
        <div class="container-fluid">
            <create-button>
                <template slot="title">Create new client</template>
            </create-button>

            <card class="strpied-tabled-with-hover">
                <template slot="header">
                    <h4 class="card-title">Clients list</h4>
                </template>
                <action-table
                    class="table-hover table-bordered table-striped"
                    :columns="columns"
                    :data="clients"
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
    import ActionTable from '../../components/TableAction'
    import Card from '../../components/Cards/Card'
    import CreateItem from './Create'
    import EditItem from './Edit'
    import CreateButton from '../../components/Buttons/Create'

    const tableColumns = [
        {
            id: 'name',
            value: 'Name',
            width: '',
            class: ''
        },
        {
            id: 'name_vi',
            value: 'Name VI',
            width: '',
            class: ''
        },
        {
            id: 'name_ja',
            value: 'Name JA',
            width: '',
            class: ''
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
                clients: [],
                currentItem: null,
                validationErrors: '',
                validationSuccess: ''
            }
        },
        mounted() {
            this.fetchItems();
        },
        methods: {
            fetchItems() {
                let uri = '/api/v1/clients';
                axios.get(uri)
                    .then(res => {
                        this.clients = res.data;
                    })
                    .catch(err => {
                        console.log(err);
                        alert("Could not load clients");
                    });
            },
            createItem(newItem) {
                // Reset validate
                this.validationErrors = '';
                this.validationSuccess = '';

                let uri = '/api/v1/clients';
                axios.post(uri, newItem)
                    .then(res => {
                        let addIdItem = Object.assign({}, {id: res.data.id}, newItem);
                        this.clients = [...this.clients, addIdItem];
                        this.validationSuccess = res.data.message;
                    })
                    .catch(err => {
                        console.log(err);
                        if (err.response.status == 422){
                            this.validationErrors = err.response.data;
                        }
                    });
            },
            deleteItem(id) {
                if (confirm("Are you sure want to delete this record?")) {
                    let uri = '/api/v1/clients/' + id;
                    axios.delete(uri).then((res) => {
                        this.clients = this.clients.filter(item => item.id !== id);
                        console.log(res.data.message);
                    }).catch(err => console.log(err));
                }
            },
            getItem(id) {
                let uri = '/api/v1/clients/' + id;
                axios.get(uri).then((response) => {
                    this.currentItem = response.data;
                });
            },
            updateItem(item) {
                // Reset validate
                this.validationErrors = '';
                this.validationSuccess = '';

                let uri = '/api/v1/clients/' + item.id;
                console.log(item);
                axios.patch(uri, item)
                    .then((res) => {
                        let foundIndex = this.clients.findIndex(x => x.id == item.id);
                        this.clients[foundIndex] = item;
                        this.clients = [...this.clients];
                        this.validationSuccess = res.data.message;
                    })
                    .catch(err => {
                        console.log(err);
                        if (err.response.status == 422){
                            this.validationErrors = err.response.data;
                        }
                    });
            },
            resetValidate() {
                this.validationSuccess = '';
                this.validationErrors = '';
            }
        }
    }

</script>
<style lang="scss">

</style>
