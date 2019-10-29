<template>
    <div class="content">
        <div class="container-fluid">
            <div class="form-group">
                <div class="row">
                    <div class="col-auto">
                        <create-button>
                            <template slot="title">Create new project</template>
                        </create-button>
                    </div>
                    <div class="col-auto ml-auto">
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#issueCreate">
                            <i class="fa fa-plus"></i>
                            Add new issue
                        </button>
                    </div>
                </div>
            </div>

            <card class="strpied-tabled-with-hover">
                <template slot="header">
                    <h4 class="card-title">Projects list</h4>
                </template>
                <action-table
                    class="table-hover table-bordered table-striped"
                    :columns="columns"
                    :data="projectData"
                    v-on:get-item="getItem"
                    v-on:delete-item="deleteItem">
                </action-table>
            </card>

            <CreateItem
                :clients="clients"
                :departments="departments"
                :types="types"
                :errors="validationErrors"
                :success="validationSuccess"
                v-on:create-item="createItem"
                v-on:reset-validation="resetValidate">
            </CreateItem>

            <EditItem
                :currentItem="currentItem"
                :clients="clients"
                :departments="departments"
                :types="types"
                :errors="validationErrors"
                :success="validationSuccess"
                v-on:update-item="updateItem"
                v-on:reset-validation="resetValidate">
            </EditItem>

            <AddIssue
                :projects="projects"
                :errors="validationErrors"
                :success="validationSuccess"
                v-on:add-issue="AddIssueFunc"
                v-on:reset-validation="resetValidate">
            </AddIssue>
        </div>
    </div>
</template>
<script>
    import Card from '../../components/Cards/Card'
    import CreateItem from './Create'
    import EditItem from './Edit'
    import AddIssue from './AddIssue'
    import CreateButton from '../../components/Buttons/Create'
    import ActionTable from '../../components/TableAction'
    import moment from 'moment'

    const tableColumns = [
        {id: 'client', value: 'Client', width: '', class: '' },
        {id: 'department', value: 'Department', width: '', class: '' },
        {id: 'project', value: 'Project', width: '', class: '' },
        {id: 'issue', value: 'Issue', width: '', class: '' },
        {id: 'type', value: 'Type', width: '', class: '' },
        {id: 'value', value: 'Type color', width: '110', class: 'text-center' },
        {id: 'start_date', value: 'Start date', width: '', class: '' },
        {id: 'end_date', value: 'End date', width: '', class: '' }
    ];

    export default {
        components: {
            Card,
            CreateItem,
            EditItem,
            AddIssue,
            CreateButton,
            ActionTable
        },
        data() {
            return {
                columns: [...tableColumns],
                clients: [],
                departments: [],
                types: [],
                projects: [],
                projectData: [],
                currentItem: null,
                validationErrors: '',
                validationSuccess: ''
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
            getDataProjects(data) {
                if ( data.length ) {
                    let dataProjects = [];

                    for (let i = 0; i < data.length; i++) {
                        let obj = {
                            id: data[i].id,
                            client: this.getObjectValue(this.clients, data[i].client_id).text,
                            department: this.getObjectValue(this.departments, data[i].dept_id).text,
                            project: data[i].p_name,
                            issue: data[i].i_name,
                            issue_id: data[i].issue_id,
                            type: this.getObjectValue(this.types, data[i].type_id).slug,
                            value: this.getObjectValue(this.types, data[i].type_id).value,
                            start_date: this.customFormatter(data[i].start_date),
                            end_date: this.customFormatter(data[i].end_date)
                        };
                        dataProjects.push(obj);
                    }
                    this.projectData = dataProjects;
                }
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
                // Reset validate
                this.validationErrors = '';
                this.validationSuccess = '';

                let uri = '/api/v1/projects';
                axios.post(uri, newItem)
                    .then(res => {
                        let addIdItem = Object.assign({}, {
                            id: res.data.id,
                            issue_id: res.data.issue_id
                        }, newItem);
                        this.projects = [...this.projects, addIdItem];
                        this.validationSuccess = res.data.message;
                    })
                    .catch(err => {
                        console.log(err);
                        if (err.response.status == 422){
                            this.validationErrors = err.response.data;
                        }
                    });
            },
            AddIssueFunc(newIssue) {
                // Reset validate
                this.validationErrors = '';
                this.validationSuccess = '';

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
                        this.validationSuccess = res.data.message;
                    })
                    .catch(err => {
                        console.log(err);
                        if (err.response.status == 422){
                            this.validationErrors = err.response.data;
                        }
                    });
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
                // Reset validate
                this.validationErrors = '';
                this.validationSuccess = '';

                let uri = '/api/v1/projects/' + item.id;
                axios.patch(uri, item).then((res) => {
                    let foundIndex = this.projects.findIndex(x => x.issue_id == item.issue_id);
                    this.projects[foundIndex] = item;
                    this.projects = [...this.projects];
                    this.validationSuccess = res.data.message;
                })
                .catch(err => {
                    console.log(err);
                    if (err.response.status == 422){
                        this.validationErrors = err.response.data;
                    }
                });

                let uri_issue = '/api/v1/issues/' + item.issue_id;
                axios.patch(uri_issue, item).then((res) => {
                    console.log(res.data);
                }).catch(err => console.log(err));
            },
            customFormatter(date) {
                return moment(date).format('DD-MM-YYYY') !== 'Invalid date' ? moment(date).format('DD-MM-YYYY') : '--';
            },
            resetValidate() {
                this.validationSuccess = '';
                this.validationErrors = '';
            }
        },
        watch: {
            projects: [{
                handler: 'getDataProjects'
            }]
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
