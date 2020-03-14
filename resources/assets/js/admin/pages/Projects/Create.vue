<template>
    <modal id="itemCreate" :sizeClasses="modalLg" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">{{$ml.with('VueJS').get('txtCreateProject')}}</template>
        <form @submit="emitCreateItem">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtTypes')}}</label>
                        <div>
                            <select2-type :options="typeOptions" v-model="type_id" class="select2">
                                <option disabled value="0">Select one</option>
                            </select2-type>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtDepartments')}}</label>
                        <div>
                            <select-2 :options="departments" v-model="dept_id" class="select2">
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
                        <label class="">{{$ml.with('VueJS').get('txtName')}}</label>
                        <input v-model="p_name" type="text" name="p_name" class="form-control" required>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtNameVi')}}</label>
                        <input v-model="p_name_vi" type="text" name="p_name_vi" class="form-control">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtNameJa')}}</label>
                        <input v-model="p_name_ja" type="text" name="p_name_ja" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtLineRoomId')}}</label>
                        <input v-model="room_id" type="text" name="room_id" class="form-control">
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtIssue')}}</label>
                        <input v-model="i_name" type="text" name="i_name" class="form-control">
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtPage')}}</label>
                        <input v-model="page" type="number" name="page" class="form-control">
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtNoPeriod')}}</label>
                        <input v-model="no_period" type="checkbox" name="no_period" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row" v-if="has_period">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtStartDate')}}</label>
                        <datepicker name="startDate" input-class="form-control" placeholder="Select Date" v-model="start_date" :format="customFormatter" :disabled-dates="disabledEndDates()" :language="getLanguage(this.$ml)">
                        </datepicker>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtEndDate')}}</label>
                        <datepicker name="endDate" input-class="form-control" placeholder="Select Date" v-model="end_date" :format="customFormatter" :disabled-dates="disabledStartDates()" :language="getLanguage(this.$ml)">
                        </datepicker>
                    </div>
                </div>
            </div>
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <hr>
            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary">{{$ml.with('VueJS').get('txtCreate')}}</button>
            </div>
        </form>
    </modal>
</template>
<script>
import Select2 from '../../components/SelectTwo/SelectTwo.vue'
import Select2Type from '../../components/SelectTwo/SelectTwoType.vue'
import Modal from '../../components/Modals/Modal'
import ErrorItem from '../../components/Validations/Error'
import SuccessItem from '../../components/Validations/Success'
import Datepicker from 'vuejs-datepicker';
import { vi, ja } from 'vuejs-datepicker/dist/locale'
import moment from 'moment'

export default {
    name: 'CreateItem',
    components: {
        Select2,
        Select2Type,
        datepicker: Datepicker,
        ErrorItem,
        SuccessItem,
        Modal
    },
    props: ['departments', 'types', 'errors', 'success'],
    data() {
        return {
            dept_id: 1,
            type_id: 0,
            p_name: '',
            p_name_vi: '',
            p_name_ja: '',
            room_id: '',
            no_period: false,
            has_period: true,
            i_name: '',
            page: '',
            start_date: '',
            end_date: '',
            modalLg: 'modal-lg',
            departmentOptions: [],
            typeOptions: []
        }
    },
    mounted() {
    },
    methods: {
        getLanguage(data) {
            return data.current === "vi" ? vi : ja
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
        updatePeriod(data) {
            if ( data ) {
                this.start_date = '';
                this.end_date = '';
                this.has_period = false;
            } else {
                this.has_period = true;
            }
        },
        emitCreateItem(e) {
            e.preventDefault();

            const newItem = {
                dept_id: this.dept_id,
                type_id: this.type_id,
                p_name: this.p_name,
                p_name_vi: this.p_name_vi,
                p_name_ja: this.p_name_ja,
                room_id: this.room_id,
                i_name: this.i_name,
                page: this.page,
                start_date: this.start_date,
                end_date: this.end_date,
            };

            this.$emit('create-item', newItem);
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
                this.dept_id = 1;
                this.type_id = 0;
                this.p_name = '';
                this.p_name_vi = '';
                this.p_name_ja = '';
                this.room_id = '';
                this.no_period = false;
                this.i_name = '';
                this.page = '';
                this.start_date = '';
                this.end_date = '';
            }
        }
    },
    watch: {
        types: [{
            handler: 'getDataTypes'
        }],
        no_period: [{
            handler: 'updatePeriod'
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