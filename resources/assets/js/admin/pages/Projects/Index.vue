<template>
    <div class="content projects">
        <div class="container-fluid">
            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-auto">
                        <create-button>
                            <template slot="title">Create new project</template>
                        </create-button>
                    </div>
                    <div class="col-12 col-sm-auto ml-auto">
                        <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#issueCreate">
                            <i class="fa fa-plus"></i>
                            Add new issue
                        </button>
                    </div>
                </div>
            </div>
            <card class="strpied-tabled-with-hover">
                <template slot="header">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title">Projects list</h4>
                        <base-checkbox v-model="showArchive" class="align-self-end">View archive</base-checkbox>
                    </div>
                </template>
                <div class="table-responsive" v-if="projects">
                    <action-table class="table-hover table-striped" :columns="columns" :data="projectData" v-on:get-item="getItem" v-on:delete-item="deleteItem" v-on:archive-item="archiveItem">
                    </action-table>
                </div>
                <pagination :data="projects" :show-disabled="showDisabled" :limit="limit" :align="align" :size="size" @pagination-change-page="getResults"></pagination>
            </card>
            <CreateItem :departments="departments" :types="types" :errors="validationErrors" :success="validationSuccess" v-on:create-item="createItem" v-on:reset-validation="resetValidate">
            </CreateItem>
            <EditItem :currentItem="currentItem" :departments="departments" :types="types" :errors="validationErrors" :success="validationSuccess" v-on:update-item="updateItem" v-on:reset-validation="resetValidate">
            </EditItem>
            <AddIssue :projects="projects.data" :errors="validationErrors" :success="validationSuccess" v-on:add-issue="AddIssueFunc" v-on:reset-validation="resetValidate">
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
    { id: 'department', value: 'Department', width: '', class: '' },
    { id: 'project', value: 'Project', width: '', class: '' },
    { id: 'issue', value: 'Issue', width: '60', class: 'text-center' },
    { id: 'type', value: 'Type', width: '', class: '' },
    { id: 'value', value: 'Color', width: '110', class: 'text-center' },
    { id: 'start_date', value: 'Start date', width: '', class: '' },
    { id: 'end_date', value: 'End date', width: '', class: '' }
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
            departments: [],
            types: [],
            projects: {},
            projectData: [],
            currentItem: null,
            showArchive: false,
            validationErrors: '',
            validationSuccess: '',
            limit: 2,
            showDisabled: true,
            align: 'right',
            size: 'small'
        }
    },
    mounted() {
        this.fetchItems();
    },
    methods: {
        getObjectValue(data, id) {
            let obj = data.filter(function(elem) {
                if (elem.id == id) return elem;
            });

            if (obj.length > 0)
                return obj[0];
        },
        getDataProjects(projects) {
            if (projects.data.length) {
                let dataProjects = [];

                for (let i = 0; i < projects.data.length; i++) {
                    let checkArchive = projects.data[i].status === "archive" ? " <i style='color: #FF4A55;'>(Archived)</i>" : "";
                    let obj = {
                        id: projects.data[i].id,
                        department: this.getObjectValue(this.departments, projects.data[i].dept_id).text != 'All' ? this.getObjectValue(this.departments, projects.data[i].dept_id).text : '',
                        project: projects.data[i].p_name + checkArchive,
                        issue: projects.data[i].i_name,
                        issue_id: projects.data[i].issue_id,
                        status: projects.data[i].status,
                        type: this.getObjectValue(this.types, projects.data[i].type_id).slug,
                        value: this.getObjectValue(this.types, projects.data[i].type_id).value,
                        start_date: this.customFormatter(projects.data[i].start_date),
                        end_date: this.customFormatter(projects.data[i].end_date)
                    };
                    dataProjects.push(obj);
                }
                this.projectData = dataProjects;
            } else {
                this.projectData = [];
            }
        },
        fetchItems() {
            let uri = '/data/projects';
            axios.get(uri)
                .then(res => {
                    this.departments = res.data.departments;
                    this.types = res.data.types;
                    this.projects = res.data.projects;
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not load projects");
                });
        },
        getResults(page = 1) {
            axios.get('/data/projects?page=' + page + '&archive=' + this.showArchive)
                .then(response => {
                    this.projects = response.data.projects; 
                });
        },
        getProjects(archive) {
            let uri = '/data/projects/?archive=' + archive;
            axios.get(uri)
                .then(res => {
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

            let uri = '/data/projects';
            axios.post(uri, newItem)
                .then(res => {
                    let addIdItem = Object.assign({}, {
                        id: res.data.id,
                        issue_id: res.data.issue_id,
                        status: 'publish',
                    }, newItem);
                    // if ( !this.showArchive ) this.projects.data = [addIdItem, ...this.projects.data];
                    this.validationSuccess = res.data.message;
                })
                .catch(err => {
                    console.log(err);
                    if (err.response.status == 422) {
                        this.validationErrors = err.response.data;
                    }
                });
        },
        AddIssueFunc(newIssue) {
            // Reset validate
            this.validationErrors = '';
            this.validationSuccess = '';

            let uri = '/data/issues';
            axios.post(uri, newIssue)
                .then(res => {
                    let addIdItem = Object.assign({}, {
                        id: res.data.id,
                        issue_id: res.data.issue_id,
                        dept_id: res.data.dept_id,
                        type_id: res.data.type_id,
                        p_name: res.data.p_name,
                        p_name_vi: res.data.p_name_vi,
                        p_name_ja: res.data.p_name_ja,
                        status: 'publish',
                    }, newIssue);
                    // if ( !this.showArchive ) this.projects.data = [addIdItem, ...this.projects.data];
                    this.validationSuccess = res.data.message;
                })
                .catch(err => {
                    console.log(err);
                    if (err.response.status == 422) {
                        this.validationErrors = err.response.data;
                    }
                });
        },
        deleteItem(issue_id) {
            if (confirm("Are you sure want to delete this record?")) {
                let uri = '/data/issues/' + issue_id;
                axios.delete(uri).then((res) => {
                    // this.projects.data = this.projects.data.filter(item => item.issue_id !== issue_id);
                    this.getProjects(this.showArchive);
                    console.log(res.data.message);
                }).catch(err => console.log(err));
            }
        },
        archiveItem(data) {
            let uri = '/data/issues/archive/' + data.id + '/' + data.status;
            axios.get(uri).then((response) => {
                // this.projects.data = this.projects.data.filter(item => item.issue_id !== data.id);
                this.getProjects(this.showArchive);
            }).catch(err => console.log(err));
        },
        getItem(id, issue_id) {
            let uri = '/data/projects/' + id + '?issue_id=' + issue_id;
            axios.get(uri).then((response) => {
                this.currentItem = response.data;
                this.currentItem.no_period = false;
            }).catch(err => console.log(err));
        },
        updateItem(item) {
            // Reset validate
            this.validationErrors = '';
            this.validationSuccess = '';

            let uri = '/data/projects/' + item.id;
            axios.patch(uri, item).then((res) => {
                    let foundIndex = this.projects.data.findIndex(x => x.issue_id == item.issue_id);
                    this.projects.data[foundIndex] = item;
                    this.projects.data = [...this.projects.data];
                    this.validationSuccess = res.data.message;

                    // Update issue
                    let uri_issue = '/data/issues/' + item.issue_id;
                    axios.patch(uri_issue, item).then((res) => {
                        console.log(res.data.message);
                    })
                    .catch(err => {
                        if (err.response.status == 422) {
                            this.validationSuccess = '';
                            err.response.data.name = ["The issue has already been taken."];
                            this.validationErrors = err.response.data;
                        }
                    });
                })
                .catch(err => {
                    if (err.response.status == 422) {
                        this.validationErrors = err.response.data;
                    }
                });
        },
        customFormatter(date) {
            return moment(date).format('DD-MM-YYYY') !== 'Invalid date' ? moment(date).format('YYYY/MM/DD') : '--';
        },
        resetValidate() {
            this.getProjects(this.showArchive);
            this.validationSuccess = '';
            this.validationErrors = '';
        }
    },
    watch: {
        projects: [{
            handler: 'getDataProjects',
            deep: true
        }],
        showArchive: [{
            handler: 'getProjects'
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
.projects thead th:last-child {
    width: 150px;
}
</style>