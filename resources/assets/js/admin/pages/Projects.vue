<template>
    <div class="content">
        <div class="container-fluid">

            <div class="form-group">
                <div class="row">
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#itemCreate">
                            <i class="fa fa-plus"></i>
                            Create new project
                        </button>
                    </div>
                    <div class="col-auto ml-auto">
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#issueCreate">
                            <i class="fa fa-plus"></i>
                            Add new issue
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Projects list</div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Clients</th>
                            <th>Departments</th>
                            <th>Project</th>
                            <th>Issue</th>
                            <th>Type</th>
                            <th width="110" class="text-center">Type Color</th>
                            <th>Start date</th>
                            <th>End date</th>
                            <th width="110" class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-bind:key="project.issue_id" v-for="project in projects">
                            <td>{{ getObjectValue(clients, project.client_id).text }}</td>
                            <td>{{ getObjectValue(departments, project.dept_id).text }}</td>
                            <td>{{ project.p_name }}</td>
                            <td v-if="project.i_name">{{ project.i_name }}</td>
                            <td v-else>--</td>
                            <td>{{ getObjectValue(types, project.type_id).slug }}</td>
                            <td class="text-center"><span
                                    :style="setBackground( getObjectValue(types, project.type_id).value )"
                                    class="type-color"></span></td>
                            <td v-if="project.start_date">{{ customFormatter(project.start_date) }}</td>
                            <td v-else>--</td>
                            <td v-if="project.end_date">{{ customFormatter(project.end_date) }}</td>
                            <td v-else>--</td>
                            <td class="text-center">
                                <button v-on:click="getItem(project.id, project.issue_id)" type="button"
                                        class="btn btn-xs btn-default" data-toggle="modal" data-target="#itemDetail">
                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                </button>
                                <button v-on:click="deleteItem(project.issue_id)" type="button"
                                        class="btn btn-xs btn-danger ml-2">
                                    <i class="fa fa-times" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <CreateItem v-on:create-item="createItem" :clients="clients" :departments="departments"
                        :types="types"></CreateItem>
            <EditItem v-on:update-item="updateItem" :currentItem="currentItem" :clients="clients"
                      :departments="departments" :types="types"></EditItem>
            <AddIssue v-on:add-issue="AddIssueFunc" :projects="projects"></AddIssue>

        </div>
    </div>
</template>
<script>
    import Card from '../components/Cards/Card.vue'

    import CreateItem from './Projects/Create'
    import EditItem from './Projects/Edit'
    import AddIssue from './Projects/AddIssue'

    import moment from 'moment'

    export default {
        components: {
            Card,
            CreateItem,
            EditItem,
            AddIssue
        },
        data() {
            return {
                clients: [],
                departments: [],
                types: [],
                projects: [],
                currentItem: null,
            }
        },
        mounted() {
            this.fetchItems();
        },
        methods: {
            getObjectValue(data, id) {
                let obj = data.filter(function (elem) {
                    if (elem.id == id) return elem;
                });

                if (obj.length > 0)
                    return obj[0];
            },
            fetchItems() {
                let uri = '/api/v1/projects';
                axios.get(uri)
                    .then(res => {
                        this.clients = res.data.clients;
                        this.departments = res.data.departments;
                        this.types = res.data.types;
                        this.projects = res.data.projects;
                    })
                    .catch(err => {
                        console.log(err);
                        alert("Could not load projects");
                    });
            },
            createItem(newItem) {
                let uri = '/api/v1/projects';
                axios.post(uri, newItem)
                    .then(res => {
                        let addIdItem = Object.assign({}, {
                            id: res.data.id,
                            issue_id: res.data.issue_id
                        }, newItem);
                        this.projects = [...this.projects, addIdItem];
                        console.log(res.data.message);
                    })
                    .catch(err => console.log(err));
            },
            AddIssueFunc(newIssue) {
                let uri = '/api/v1/issues';
                axios.post(uri, newIssue)
                    .then(res => {
                        let addIdItem = Object.assign({}, {
                            id: res.data.id,
                            issue_id: res.data.issue_id,
                            client_id: res.data.client_id,
                            dept_id: res.data.dept_id,
                            type_id: res.data.type_id,
                            p_name: res.data.p_name,
                            p_name_vi: res.data.p_name_vi,
                            p_name_ja: res.data.p_name_ja,
                            is_training: res.data.is_training
                        }, newIssue);
                        this.projects = [...this.projects, addIdItem];
                        console.log(res.data.message);
                    })
                    .catch(err => console.log(err));
            },
            deleteItem(issue_id) {
                if (confirm("Are you sure want to delete this record?")) {
                    let uri = '/api/v1/issues/' + issue_id;
                    axios.delete(uri).then((res) => {
                        this.projects = this.projects.filter(item => item.issue_id !== issue_id);
                        console.log(res.data);
                    }).catch(err => console.log(err));
                }
            },
            getItem(id, issue_id) {
                let uri = '/api/v1/projects/' + id + '?issue_id=' + issue_id;
                axios.get(uri).then((response) => {
                    this.currentItem = response.data;
                });
            },
            updateItem(item) {
                let uri = '/api/v1/projects/' + item.id;
                axios.patch(uri, item).then((res) => {
                    let foundIndex = this.projects.findIndex(x => x.issue_id == item.issue_id);
                    this.projects[foundIndex] = item;
                    this.projects = [...this.projects];
                    console.log(res.data);
                }).catch(err => console.log(err));

                let uri_issue = '/api/v1/issues/' + item.issue_id;
                axios.patch(uri_issue, item).then((res) => {
                    console.log(res.data);
                }).catch(err => console.log(err));
            },
            setBackground(color) {
                return {
                    background: color
                };
            },
            customFormatter(date) {
                return moment(date).format('DD-MM-YYYY');
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
