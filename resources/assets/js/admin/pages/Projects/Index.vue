<template>
    <div class="content projects">
        <div class="container-fluid">
            <card>
                <template slot="header">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title">{{$ml.with('VueJS').get('txtFilter')}}</h4>
                    </div>
                </template>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="">{{$ml.with('VueJS').get('txtKeyword')}}</label>
                            <input v-model="search.keyword" :placeholder="$ml.with('VueJS').get('txtKeyword')" type="text" class="form-control" v-on:keyup="filterItems(search.keyword)" v-on:keyup.enter="searchItems(search)">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="">{{$ml.with('VueJS').get('txtTypes')}}</label>
                            <div>
                                <select2-type :options="typeOptions" v-model="search.type_id" class="select2">
                                    <option disabled value="0">{{$ml.with('VueJS').get('txtSelectOne')}}</option>
                                </select2-type>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group"> 
                            <label class="">{{$ml.with('VueJS').get('txtDepartments')}}</label>
                            <div>
                                <select-2 :options="departmentOptions" v-model="search.dept_id" class="select2">
                                    <option disabled value="0">{{$ml.with('VueJS').get('txtSelectOne')}}</option>
                                </select-2>
                            </div>
                        </div>
                    </div>
                </div>
            </card>
            <div class="form-group">
                <div class="row">
                    <div class="col-12 col-sm-auto">
                        <button-create>
                            <template slot="title">{{$ml.with('VueJS').get('txtCreateProject')}}</template>
                        </button-create>
                    </div>
                    <div class="col-12 col-sm-auto ml-auto">
                        <button type="button" class="btn btn-primary mr-3" data-toggle="modal" data-target="#issueImport" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-plus"></i>
                            {{$ml.with('VueJS').get('txtImportIssue')}}
                        </button>
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#issueCreate" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-plus"></i>
                            {{$ml.with('VueJS').get('txtAddIssue')}}
                        </button>
                    </div>
                </div>
            </div>
            <card class="strpied-tabled-with-hover">
                <template slot="header">
                    <div class="d-flex justify-content-between">
                        <h4 class="card-title">{{$ml.with('VueJS').get('txtProjectsList')}}</h4>
                        <base-checkbox v-model="showArchive" class="align-self-end">{{$ml.with('VueJS').get('txtViewArchive')}}</base-checkbox>
                    </div>
                </template>
                <div class="table-responsive" v-if="filterResults">
                    <table-project class="table-hover table-striped" :columns="columns" :data="filterResults" v-on:get-item="getItem" v-on:delete-item="deleteItem" v-on:archive-item="archiveItem">
                    </table-project>
                </div>
                <pagination :data="projects" :show-disabled="showDisabled" :limit="limit" :align="align" :size="size" @pagination-change-page="getResults"></pagination>
            </card>
            <CreateItem :departments="departmentOptions" :types="typeOptions" :errors="validationErrors" :success="validationSuccess" v-on:create-item="createItem" v-on:reset-validation="resetValidate">
            </CreateItem>
            <EditProject :currentItem="currentItem" :departments="departmentOptions" :types="types" :errors="validationErrors" :success="validationSuccess" v-on:update-project="updateProject" v-on:reset-validation="resetValidate">
            </EditProject>
            <EditIssue :projects="projectOptions" :currentItem="currentItem" :errors="validationErrors" :success="validationSuccess" v-on:update-issue="updateIssue" v-on:reset-validation="resetValidate">
            </EditIssue>
            <AddIssue :projects="projectOptions" :errors="validationErrors" :success="validationSuccess" v-on:add-issue="AddIssueFunc" v-on:reset-validation="resetValidate">
            </AddIssue>
            <ImportIssue v-on:reset-import="resetImport"></ImportIssue>
        </div>
    </div>
</template>
<script>
import Card from '../../components/Cards/Card'
import CreateItem from './Create'
import EditProject from './EditProject'
import EditIssue from './EditIssue'
import ImportIssue from './ImportIssue'
import AddIssue from './AddIssue'
import CreateButton from '../../components/Buttons/Create' 
import TableProject from '../../components/TableProject'
import moment from 'moment'
import Select2 from '../../components/SelectTwo/SelectTwo.vue'
import Select2Type from '../../components/SelectTwo/SelectTwoType.vue'

export default {
    components: {
        Select2,
        Select2Type,
        Card,
        CreateItem,
        EditProject,
        EditIssue,
        AddIssue,
        CreateButton,
        TableProject,
        ImportIssue
    },
    data() {
        return {
            columns: [
                { id: 'department', value: this.$ml.with('VueJS').get('txtDepartment'), width: '', class: '' },
                { id: 'project', value: this.$ml.with('VueJS').get('txtProject'), width: '', class: '' },
                { id: 'issue', value: this.$ml.with('VueJS').get('txtIssue'), width: '110', class: '' },
                { id: 'page', value: this.$ml.with('VueJS').get('txtPage'), width: '60', class: '' },
                { id: 'type', value: this.$ml.with('VueJS').get('txtType'), width: '', class: '' },
                { id: 'value', value: this.$ml.with('VueJS').get('txtColor'), width: '110', class: 'text-center' },
                { id: 'start_date', value: this.$ml.with('VueJS').get('lblStartDate'), width: '', class: '' },
                { id: 'end_date', value: this.$ml.with('VueJS').get('lblEndDate'), width: '', class: '' }
            ],
            departments: [],
            types: [],
            departmentOptions: [],
            typeOptions: [],
            projects: {},
            projectData: [],
            projectOptions: [],
            filterResults: [],
            currentItem: null,
            showArchive: false,
            validationErrors: '',
            validationSuccess: '',
            limit: 2,
            showDisabled: true,
            align: 'right',
            size: 'small',
            search: {
                keyword: '',
                type_id: -1,
                dept_id: 1
            }
        }
    },
    mounted() {
        let _this = this;
        _this.fetchItems();
        
        $(document).on('click', '.languages button', function() {
            _this.langSlug = _this.$ml.current;
            _this.getDataTypes(_this.types);
            _this.getDataDepartments(_this.departments);
            _this.projectOptions[0].text = _this.$ml.with('VueJS').get('txtSelectOne');
            _this.projectOptions = [..._this.projectOptions]

            _this.columns = [
                { id: 'department', value: _this.$ml.with('VueJS').get('txtDepartment'), width: '', class: '' },
                { id: 'project', value: _this.$ml.with('VueJS').get('txtProject'), width: '', class: '' },
                { id: 'issue', value: _this.$ml.with('VueJS').get('txtIssue'), width: '110', class: '' },
                { id: 'page', value: _this.$ml.with('VueJS').get('txtPage'), width: '60', class: '' },
                { id: 'type', value: _this.$ml.with('VueJS').get('txtType'), width: '', class: '' },
                { id: 'value', value: _this.$ml.with('VueJS').get('txtColor'), width: '110', class: 'text-center' },
                { id: 'start_date', value: _this.$ml.with('VueJS').get('lblStartDate'), width: '', class: '' },
                { id: 'end_date', value: _this.$ml.with('VueJS').get('lblEndDate'), width: '', class: '' }
            ];
        });
    },
    methods: {
        getObjectValue(data, id) {
            let obj = data.filter((elem) => {
                if (elem.id == id) return elem;
            });

            if (obj.length > 0)
                return obj[0];
        },
        getDataDepartments(data) {
            if (data.length) {
                let obj = {
                    id: 0,
                    text: this.$ml.with('VueJS').get('txtSelectOne')
                };
                this.departmentOptions = [obj].concat(data);
            }
        },
        getDataTypes(data) {
            if (data.length) {
                let dataTypes = [];
                let obj = {
                    id: 0,
                    text: '<div>'+this.$ml.with('VueJS').get('txtSelectOne')+'</div>'
                };
                dataTypes.push(obj);

                let objAll = {
                    id: -1,
                    text: '<div>All</div>'
                };
                dataTypes.push(objAll);

                for (let i = 0; i < data.length; i++) {
                    let obj = {
                        id: data[i].id,
                        text: '<div><span class="type-color" style="background: ' + data[i].value + '"></span>' + data[i].slug + '</div>'
                    };
                    dataTypes.push(obj);
                }
                this.typeOptions = dataTypes;
            }
        },
        getDataProjects(projects) {
            if (projects.data.length) {
                let dataProjects = projects.data.map((item, index) => {
                    let checkArchive = item.status === "archive" ? " <i style='color: #FF4A55;'>(Archived)</i>" : "";
                    let checkTR = this.getObjectValue(this.types, item.type_id).slug.includes("_tr") ? " (TR)" : "";
                    return {
                        id: item.id,
                        department: this.getObjectValue(this.departments, item.dept_id).text != 'All' ? this.getObjectValue(this.departments, item.dept_id).text : '',
                        project: item.p_name + checkTR + checkArchive,
                        issue: item.i_name,
                        issue_id: item.issue_id,
                        page: item.page,
                        status: item.status,
                        type: this.getObjectValue(this.types, item.type_id).slug,
                        value: this.getObjectValue(this.types, item.type_id).value,
                        start_date: this.customFormatter(item.start_date),
                        end_date: this.customFormatter(item.end_date)
                    };
                });
                this.projectData = this.filterResults = dataProjects;
            } else {
                this.projectData = this.filterResults = [];
            }
        },
        fetchItems() {
            let uri = '/data/projects';
            axios.get(uri)
                .then(res => {
                    this.departments = res.data.departments;
                    this.types = res.data.types;
                    this.projects = res.data.projects;

                    let obj = {
                        id: 0,
                        text: this.$ml.with('VueJS').get('txtSelectOne')
                    };
                    this.projectOptions = [obj].concat(res.data.projectOptions);
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not load projects");
                });
        },
        getResults(page = 1) {
            axios.get('/data/projects?page=' + page + '&archive=' + this.showArchive + '&search=' + JSON.stringify(this.search))
                .then(response => {
                    this.projects = response.data.projects; 
                });
        },
        getProjects(archive) {
            let uri = '/data/projects/?archive=' + archive + '&search=' + JSON.stringify(this.search);
            axios.get(uri)
                .then(res => {
                    this.projects = res.data.projects;

                    let obj = {
                        id: 0,
                        text: this.$ml.with('VueJS').get('txtSelectOne')
                    };
                    this.projectOptions = [obj].concat(res.data.projectOptions);
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
                        page: res.data.page,
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
                        page: res.data.page,
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
            if (confirm(this.$ml.with('VueJS').get('msgConfirmDelete'))) {
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
        updateProject(item) {
            // Reset validate
            this.validationErrors = '';
            this.validationSuccess = '';

            let uri = '/data/projects/' + item.id;
            axios.patch(uri, item).then((res) => {
                    let foundIndex = this.projects.data.findIndex(x => x.issue_id == item.issue_id);
                    this.projects.data[foundIndex] = item;
                    this.projects.data = [...this.projects.data];
                    this.validationSuccess = res.data.message;
                })
                .catch(err => {
                    if (err.response.status == 422) {
                        this.validationErrors = err.response.data;
                    }
                });
        },
        updateIssue(item) {
            // Reset validate
            this.validationErrors = '';
            this.validationSuccess = '';

            // Update issue
            let uri_issue = '/data/issues/' + item.issue_id;
            axios.patch(uri_issue, item).then((res) => {
                this.validationSuccess = res.data.message;
            })
            .catch(err => {
                if (err.response.status == 422) {
                    this.validationSuccess = '';
                    err.response.data.name = ["The issue has already been taken."];
                    this.validationErrors = err.response.data;
                }
            });
        },
        filterItems(value) {
            if ( value ) {
                this.filterResults = this.projectData.filter(item => {
                    let title = item.project + " " + item.issue;
                    return title.toLowerCase().includes(value.toLowerCase());
                });
            } else {
                this.filterResults = this.projectData;
            }
        },
        searchItems(value) {
            let uri = '/data/projects?search=' + JSON.stringify(value) + '&archive=' + this.showArchive;
            axios.get(uri)
                .then(res => {
                    this.projects = res.data.projects;
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not load projects");
                });
        },
        customFormatter(date) {
            return moment(date).format('DD-MM-YYYY') !== 'Invalid date' ? moment(date).format('YYYY/MM/DD') : '--';
        },
        resetValidate() {
            if ( this.validationErrors || this.validationSuccess ) {
                this.getProjects(this.showArchive);
                this.validationSuccess = '';
                this.validationErrors = '';
                this.currentItem = false;
            }
        },
        resetImport() {
            this.getProjects(this.showArchive);
            this.currentItem = false;
        }
    },
    watch: {
        departments: [{
            handler: 'getDataDepartments'
        }],
        types: [{
            handler: 'getDataTypes'
        }],
        projects: [{
            handler: 'getDataProjects',
            deep: true
        }],
        showArchive: [{
            handler: 'getProjects'
        }],
        search: [{
            handler: 'searchItems',
            deep: true
        }],
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