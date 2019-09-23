<template>
    <div class="content">
        <div class="container-fluid">

            <div class="form-group">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#itemCreate">
                    <i class="fa fa-plus"></i>
                    Create new type
                </button>
            </div>

            <div class="card">
                <div class="card-header">Types list</div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Slug</th>
                            <th width="110" class="text-center">Type Color</th>
                            <th>Slug VI</th>
                            <th>Slug JA</th>
                            <th width="110" class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-bind:key="type.id" v-for="type in types">
                            <td>{{ type.slug }}</td>
                            <td class="text-center"><span :style="setBackground(type.value)" class="type-color"></span></td>
                            <td>{{ type.slug_vi }}</td>
                            <td>{{ type.slug_ja }}</td>
                            <td class="text-center">
                                <button v-on:click="getItem(type.id)" type="button" class="btn btn-xs btn-default" data-toggle="modal" data-target="#itemDetail">
                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                </button>
                                <button v-on:click="deleteItem(type.id)" type="button" class="btn btn-xs btn-danger ml-2">
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

    import CreateItem from './Types/Create'
    import EditItem from './Types/Edit'

    export default {
        components: {
            Card,
            CreateItem,
            EditItem
        },
        data () {
            return {
                types: [],
                currentItem: null,
            }
        },
        mounted() {
            this.fetchItems();
        },
        methods: {
            fetchItems() {
                let uri = '/api/v1/types';
                axios.get(uri)
                    .then(res => {
                        this.types = res.data;
                    })
                    .catch(err => {
                        console.log(err);
                        alert("Could not load types");
                    });
            },
            createItem(newItem) {
                let uri = '/api/v1/types';
                axios.post(uri, newItem)
                    .then(res => {
                        let addIdItem = Object.assign({}, {id: res.data.id}, newItem);
                        this.types = [...this.types, addIdItem];
                        console.log(res.data.message);
                    })
                    .catch(err => console.log(err));
            },
            deleteItem(id) {
                if (confirm("Are you sure want to delete this record?")) {
                    let uri = '/api/v1/types/' + id;
                    axios.delete(uri).then((res) => {
                        this.types = this.types.filter(item => item.id !== id);
                        console.log(res.data);
                    }).catch(err => console.log(err));
                }
            },
            getItem(id) {
                let uri = '/api/v1/types/'+id;
                axios.get(uri).then((response) => {
                    this.currentItem = response.data;
                });
            },
            updateItem(item) {
                let uri = '/api/v1/types/'+item.id;
                axios.patch(uri, item).then((res) => {
                    let foundIndex = this.types.findIndex(x => x.id == item.id);
                    this.types[foundIndex] = item;
                    this.types = [...this.types];
                    console.log(res.data);
                }).catch(err => console.log(err));
            },
            setBackground(color) {
                return {
                    background: color
                };
            },
        }
    }

</script>
<style lang="scss">
    .type-color {
        width: 60px;
        height: 20px;
        display: inline-block;
        vertical-align: middle;
    }
</style>
