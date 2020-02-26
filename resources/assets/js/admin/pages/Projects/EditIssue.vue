<template>
    <modal id="editIssue" :sizeClasses="modalLg" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">Edit Issue</template>
        <div v-if="currentItem">
            <div class="row">
                <div class="col-sm-5">
                    <div class="form-group">
                        <label class="">Issue</label>
                        <input v-model="currentItem.i_name" type="text" name="issue" class="form-control">
                    </div>
                </div>
                <div class="col-sm-5">
                    <div class="form-group">
                        <label class="">Project</label>
                        <div>
                            <select-2 :options="projectOptions" v-model="currentItem.id" class="select2">
                                <option disabled value="0">Select one</option>
                            </select-2>
                        </div>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label class="">No period</label>
                        <input v-model="currentItem.no_period" type="checkbox" name="currentItem.no_period" @change="updatePeriod" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row" v-if="has_period">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">Start date</label>
                        <datepicker name="startDate" input-class="form-control" placeholder="Select Date" v-model="currentItem.start_date" :format="customFormatter" :disabled-dates="disabledEndDates()" :language="getLanguage(this.$ml)">
                        </datepicker>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">End date</label>
                        <datepicker name="endDate" input-class="form-control" placeholder="Select Date" v-model="currentItem.end_date" :format="customFormatter" :disabled-dates="disabledStartDates()" :language="getLanguage(this.$ml)">
                        </datepicker>
                    </div>
                </div>
            </div>
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <hr>
            <div class="form-group text-right">
                <button @click="$emit('update-issue', currentItem)" type="button" class="btn btn-primary">
                    Update
                </button>
            </div>
        </div>
    </modal>
</template>
<script>
import Select2 from '../../components/SelectTwo/SelectTwo.vue'
import Modal from '../../components/Modals/Modal'
import ErrorItem from '../../components/Validations/Error'
import SuccessItem from '../../components/Validations/Success'
import Datepicker from 'vuejs-datepicker';
import { en, ja } from 'vuejs-datepicker/dist/locale'
import moment from 'moment'

export default {
    name: 'EditIssue',
    components: {
        Select2,
        datepicker: Datepicker,
        ErrorItem,
        SuccessItem,
        Modal
    },
    props: ['projects', 'currentItem', 'errors', 'success'],
    data() {
        return {
            modalLg: 'modal-lg',
            has_period: true,
            projectOptions: []
        }
    },
    methods: {
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
            return data.current === "en" ? en : ja
        },
        customFormatter(date) {
            return moment(date).format('YYYY/MM/DD');
        },
        disabledStartDates() {
            if (this.currentItem.start_date) {
                let obj = {
                    to: new Date(this.currentItem.start_date), // Disable all dates after specific date
                    // days: [0], // Disable Saturday's and Sunday's
                };
                return obj;
            }
        },
        disabledEndDates() {
            if (this.currentItem.end_date) {
                let obj = {
                    from: new Date(this.currentItem.end_date), // Disable all dates after specific date
                    // days: [0], // Disable Saturday's and Sunday's
                };
                return obj;
            }
        },
        updatePeriod() {
            if (this.currentItem.no_period) {
                this.currentItem.start_date = '';
                this.currentItem.end_date = '';
                this.has_period = false;
            } else {
                this.has_period = true;
            }
        },
        currentPeriod(data) {
            if (moment(data.start_date).format('DD-MM-YYYY') === 'Invalid date' && moment(data.end_date).format('DD-MM-YYYY') === 'Invalid date') {
                this.currentItem.no_period = true;
                this.has_period = false;
            } else {
                this.has_period = true;
            }
        }
    },
    watch: {
        projects: [{
            handler: 'getDataProjects'
        }],
        currentItem: [{
            handler: 'currentPeriod'
        }]
    }
}
</script>