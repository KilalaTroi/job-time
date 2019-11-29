<template>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-auto">
                    <card>
                        <template slot="header">
                            <h4 class="card-title text-center">{{ this.customFormatter(start_date) }}</h4>
                        </template>
                        <datepicker name="startDate" v-model="start_date" :format="customFormatter" :inline="true">
                        </datepicker>
                    </card>
                </div>
                <div class="col">
                    <h2 class="card-title mt-0">Jobs List</h2>

                    <job-table
                        class="table-hover table-bordered table-striped"
                        :columns="columns"
                        :data="jobs">
                    </job-table>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Card from '../../components/Cards/Card'
import Datepicker from 'vuejs-datepicker';
import { en, ja } from 'vuejs-datepicker/dist/locale'
import moment from 'moment'
import JobTable from '../../components/TableJob'

const tableColumns = [
    { id: 'client', value: 'Client', width: '', class: '' },
    { id: 'department', value: 'Department', width: '', class: '' },
    { id: 'project', value: 'Project', width: '', class: '' },
    { id: 'issue', value: 'Issue', width: '60', class: 'text-center' },
    { id: 'time', value: 'Time', width: '110', class: 'text-center' }
];

export default {
    components: {
        Card,
        datepicker: Datepicker,
        JobTable
    },
    data() {
        return {
            columns: [...tableColumns],
            clients: [],
            departments: [],
            jobs: [],
            jobData: [],

            start_date: new Date()
        }
    },
    mounted() {
        this.fetchItems();
    },
    methods: {
        fetchItems() {
            let uri = '/api/v1/jobs/?date=' + this.dateFormatter(this.start_date);
            axios.get(uri)
                .then(res => {
                    this.clients = res.data.clients;
                    this.departments = res.data.departments;
                    this.jobData = res.data.jobs;
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not load projects");
                });
        },
        getObjectValue(data, id) {
            let obj = data.filter(function(elem) {
                if (elem.id === id) return elem;
            });

            if (obj.length > 0)
                return obj[0];
        },
        getDataJobs(data) {
            if (data.length) {
                let dataJobs = [];

                for (let i = 0; i < data.length; i++) {
                    let obj = {
                        id: data[i].issue_id,
                        client: this.getObjectValue(this.clients, data[i].client_id).text,
                        department: this.getObjectValue(this.departments, data[i].dept_id).text,
                        project: data[i].p_name,
                        issue: data[i].i_name,
                        time: ''
                    };
                    dataJobs.push(obj);
                }
                this.jobs = dataJobs;
            } else {
                this.jobs = [];
            }
        },
        customFormatter(date) {
            return moment(date).format('DD-MM-YYYY') !== 'Invalid date' ? moment(date).format('DD MMM YYYY') : '';
        },
        dateFormatter(date) {
            return moment(date).format('YYYY-MM-DD');
        }
    },
    watch: {
        jobData: [{
            handler: 'getDataJobs'
        }],
        start_date: [{
            handler: 'fetchItems'
        }]
    }
}
</script>
<style lang="scss">
</style>