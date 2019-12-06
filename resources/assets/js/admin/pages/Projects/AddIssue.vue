<template>
    <modal id="issueCreate" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">Add new issue</template>
        <form @submit="emitAddItem">
            <div class="form-group">
                <label class="">Project</label>
                <div>
                    <select-2 :options="projectOptions" v-model="project_id" class="select2">
                        <option disabled value="0">Select one</option>
                    </select-2>
                </div>
            </div>
            <div class="form-group">
                <label class="">Issue name</label>
                <input v-model="i_name" type="text" name="i_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="">Start date</label>
                <datepicker name="startDate" input-class="form-control" placeholder="Select Date" v-model="start_date" :format="customFormatter" :disabled-dates="disabledEndDates()">
                </datepicker>
            </div>
            <div class="form-group">
                <label class="">End date</label>
                <datepicker name="endDate" input-class="form-control" placeholder="Select Date" v-model="end_date" :format="customFormatter" :disabled-dates="disabledStartDates()">
                </datepicker>
            </div>
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <hr>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Add</button>
                <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </modal>
</template>
<script>
import Select2 from '../../components/SelectTwo/SelectTwo.vue'
import Datepicker from 'vuejs-datepicker';
import { en, ja } from 'vuejs-datepicker/dist/locale'
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
            en: en,
            ja: ja,
            projectOptions: [],
        }
    },
    mounted() {},
    methods: {
        getDataProjects(data) {
            if (data.length) {
                let dataOptions = [];
                let obj = {
                    id: 0,
                    text: "Select one"
                };
                dataOptions.push(obj);

                for (let i = 0; i < data.length; i++) {
                    let objCheck = dataOptions.filter(function(elem) {
                        if (elem.id == data[i].id) return elem;
                    });
                    if (!(objCheck.length > 0)) {
                        let obj = {
                            id: data[i].id,
                            text: data[i].p_name
                        };
                        dataOptions.push(obj);
                    }
                }
                this.projectOptions = dataOptions;
            }
        },
        emitAddItem(e) {
            e.preventDefault()

            const newIssue = {
                project_id: this.project_id,
                i_name: this.i_name,
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
                this.start_date = '';
                this.end_date = '';
            }
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