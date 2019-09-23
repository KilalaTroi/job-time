<template>
    <div class="content">
        <div class="container-fluid">

            <div class="form-group">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#itemCreate">
                    <i class="fa fa-plus"></i>
                    Create new client
                </button>
            </div>

            <div class="card">
                <div class="card-header">Clients list</div>
                <div class="card-body">
                    <table class="table table-bordered table-striped" cellspacing="0">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Name VI</th>
                            <th>Name JA</th>
                            <th width="110" class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-bind:key="client.id" v-for="client in clients">
                            <td>{{ client.name }}</td>
                            <td>{{ client.name_vi }}</td>
                            <td>{{ client.name_ja }}</td>
                            <td class="text-center">
                                <button v-on:click="getItem(client.id)" type="button" class="btn btn-xs btn-default"
                                        data-toggle="modal" data-target="#itemDetail">
                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                </button>
                                <button v-on:click="deleteItem(client.id)" type="button"
                                        class="btn btn-xs btn-danger ml-2">
                                    <i class="fa fa-times" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <CreateItem v-on:create-item="createItem"></CreateItem>
            <EditItem v-bind:currentItem="currentItem" v-on:update-item="updateItem"></EditItem>

        </div>
    </div>
</template>
<script>
    import Card from '../components/Cards/Card.vue'

    import CreateItem from './Clients/Create'
    import EditItem from './Clients/Edit'

    export default {
        components: {
            Card,
            CreateItem,
            EditItem
        },
        data() {
            return {
                clients: [],
                currentItem: null,
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
                let uri = '/api/v1/clients';
                axios.post(uri, newItem)
                    .then(res => {
                        let addIdItem = Object.assign({}, {id: res.data.id}, newItem);
                        this.clients = [...this.clients, addIdItem];
                        console.log(res.data.message);
                    })
                    .catch(err => console.log(err));
            },
            deleteItem(id) {
                if (confirm("Are you sure want to delete this record?")) {
                    let uri = '/api/v1/clients/' + id;
                    axios.delete(uri).then((res) => {
                        this.clients = this.clients.filter(item => item.id !== id);
                        console.log(res.data);
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
                let uri = '/api/v1/clients/' + item.id;
                axios.patch(uri, item).then((res) => {
                    let foundIndex = this.clients.findIndex(x => x.id == item.id);
                    this.clients[foundIndex] = item;
                    this.clients = [...this.clients];
                    console.log(res.data);
                }).catch(err => console.log(err));
            },
        }
    }

</script>
<style lang="scss">

</style>
