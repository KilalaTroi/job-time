<template>
    <modal id="itemDetail" :sizeClasses="modalLg" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">Edit Project</template>
        <div v-if="currentItem">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">Types</label>
                        <div>
                            <select2-type :options="typeOptions" v-model="currentItem.type_id" class="select2">
                                <option disabled value="0">Select one</option>
                            </select2-type>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">Departments</label>
                        <div>
                            <select-2 :options="departmentOptions" v-model="currentItem.dept_id" class="select2">
                                <option disabled value="0">Select one</option>
                            </select-2>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label class="">Name</label>
                        <input v-model="currentItem.p_name" type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label class="">Name VI</label>
                        <input v-model="currentItem.p_name_vi" type="text" name="name_vi" class="form-control">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label class="">Name JA</label>
                        <input v-model="currentItem.p_name_ja" type="text" name="name_ja" class="form-control">
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">Issue</label>
                        <input v-model="currentItem.i_name" type="text" name="issue" class="form-control">
                    </div>
                </div>
                <div class="col-sm-6">
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
                <button @click="$emit('update-item', currentItem)" type="button" class="btn btn-primary">
                    Update
                </button>
                <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </modal>
</template>
<script>
import Select2 from '../../components/SelectTwo/SelectTwo.vue'
import Select2Type from '../../components/SelectTwo/SelectTwoType.vue'
import Modal from '../../components/Modals/Modal'
import ErrorItem from '../../components/Validations/Error'
import SuccessItem from '../../components/Validations/Success'
import Datepicker from 'vuejs-datepicker';
import { en, ja } from 'vuejs-datepicker/dist/locale'
import moment from 'moment'

export default {
    name: 'EditItem',
    components: {
        Select2,
        Select2Type,
        datepicker: Datepicker,
        ErrorItem,
        SuccessItem,
        Modal
    },
    props: ['currentItem', 'clients', 'departments', 'types', 'errors', 'success'],
    data() {
        return {
            clientOptions: [],
            departmentOptions: [],
            typeOptions: [],
            modalLg: 'modal-lg',
            has_period: true,
        }
    },
    methods: {
        getLanguage(data) {
            return data.current === "en" ? en : ja
        },
        getDataDepartments(data) {
            if (data.length) {
                let dataOptions = [];
                let obj = {
                    id: 0,
                    text: "Select one"
                };
                dataOptions.push(obj);

                for (let i = 0; i < data.length; i++) {
                    let obj = {
                        id: data[i].id,
                        text: data[i].text
                    };
                    dataOptions.push(obj);
                }
                this.departmentOptions = dataOptions;
            }
        },
        getDataTypes(data) {
            if (data.length) {
                let dataTypes = [];
                let obj = {
                    id: 0,
                    text: '<div>Select one</div>'
                };
                dataTypes.push(obj);

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
            if ( this.currentItem.no_period ) {
                this.currentItem.start_date = '';
                this.currentItem.end_date = '';
                this.has_period = false;
            } else {
                this.has_period = true;
            }
        },
        currentPeriod(data) {
            if ( moment(data.start_date).format('DD-MM-YYYY') === 'Invalid date' && moment(data.end_date).format('DD-MM-YYYY') === 'Invalid date' ) {
                this.currentItem.no_period = true;
                this.has_period = false;
            } else {
                this.has_period = true;
            }
        }
    },
    watch: {
        departments: [{
            handler: 'getDataDepartments'
        }],
        types: [{
            handler: 'getDataTypes'
        }],
        currentItem: [{
            handler: 'currentPeriod'
        }]
    }
}
</script>