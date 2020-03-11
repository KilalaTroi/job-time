<template>
    <modal id="issueCreate" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">Add new issue</template>
        <form @submit="emitAddItem">
            <div class="form-group">
                <label class="">Project</label>
                <div>
                    <select-2 :options="projectOptions" v-model="project_id" class="select2" @input="changeProjects">
                        <option disabled value="0">Select one</option>
                    </select-2>
                </div>
            </div>
            <div class="form-group">
                <label class="">Issue name</label>
                <input v-model="i_name" type="text" name="i_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="">Page</label>
                <input v-model="page" type="text" name="page" class="form-control">
            </div>
            <div class="form-group">
                <label class="">Start date</label>
                <datepicker name="startDate" input-class="form-control" placeholder="Select Date" v-model="start_date" :format="customFormatter" :disabled-dates="disabledEndDates()" :language="getLanguage(this.$ml)">
                </datepicker>
            </div>
            <div class="form-group">
                <label class="">End date</label>
                <datepicker name="endDate" input-class="form-control" placeholder="Select Date" v-model="end_date" :format="customFormatter" :disabled-dates="disabledStartDates()" :language="getLanguage(this.$ml)">
                </datepicker>
            </div>
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <hr>
            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary">Add</button>
            </div>
        </form>
    </modal>
</template>
<script>
import Select2 from '../../components/SelectTwo/SelectTwo.vue'
import Datepicker from 'vuejs-datepicker';
import { vi, ja } from 'vuejs-datepicker/dist/locale'
import ErrorItem from '../../components/Validations/Error'
import SuccessItem from '../../components/Validations/Success'
import Modal from '../../components/Modals/Modal'
import moment from 'moment'

export default {
    name: 'AddIssue',
    components: {
        Select2,
        datepicker: Datepicker,
        ErrorItem,
        SuccessItem,
        Modal
    },
    props: ['projects', 'errors', 'success'],
    data() {
        return {
            project_id: 0,
            i_name: '',
            start_date: '',
            end_date: '',
            page: '',
            projectOptions: []
        }
    },
    mounted() {},
    methods: {
        getObjectValue(data, id) {
            let obj = data.filter((elem) => {
                if (elem.id == id) return elem;
            });

            if (obj.length > 0)
                return obj[0];
        },
        getDataProjects(data) {
            if (data.length) {
                let obj = {
                    id: 0,
                    text: "Select one"
                };
                this.projectOptions = [obj].concat(data);
            }
        },
        getLanguage(data) {
            return data.current === "vi" ? vi : ja
        },
        emitAddItem(e) {
            e.preventDefault()

            const newIssue = {
                project_id: this.project_id,
                i_name: this.i_name,
                page: this.page,
                start_date: this.start_date,
                end_date: this.end_date
            };

            this.$emit('add-issue', newIssue);
        },
        customFormatter(date) {
            return moment(date).format('YYYY/MM/DD');
        },
        disabledStartDates() {
            let obj = {
                to: new Date(this.start_date), // Disable all dates after specific date
                // days: [0], // Disable Saturday's and Sunday's
            };
            return obj;
        },
        disabledEndDates() {
            let obj = {
                from: new Date(this.end_date), // Disable all dates after specific date
                // days: [0], // Disable Saturday's and Sunday's
            };
            return obj;
        },
        resetData(data) {
            // Reset
            if (data.length) {
                this.project_id = 0;
                this.i_name = '';
                this.page = '';
                this.start_date = '';
                this.end_date = '';
            }
        },
        changeProjects() {
            let issue_id = this.getObjectValue(this.projectOptions, this.project_id).issue_id;

            let uri = '/data/issues/getpage/' + issue_id;
            axios.get(uri)
                .then(res => {
                    console.log(res);
                    this.page = res.data.page ? res.data.page : '';
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not found!");
                });
        }
    },
    watch: {
        projects: [{
            handler: 'getDataProjects'
        }],
        start_date: [{
            handler: 'disabledStartDates'
        }],
        end_date: [{
            handler: 'disabledEndDates'
        }],
        success: [{
            handler: 'resetData'
        }]
    }
}
</script>
<style lang="scss">
input[type="radio"],
input[type="checkbox"] {
    &.form-control {
        width: 40px;
    }
}
</style>