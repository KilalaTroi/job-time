<template>
    <modal id="editIssue" :sizeClasses="modalLg" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">{{$ml.with('VueJS').get('txtEditIssue')}}</template>
        <div v-if="currentItem">
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtIssue')}}</label>
                        <input v-model="currentItem.i_name" type="text" name="issue" class="form-control">
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtPage')}}</label>
                        <input v-model="currentItem.page" type="number" name="page" class="form-control">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtProject')}}</label>
                        <div>
                            <select-2 :options="projects" v-model="currentItem.id" class="select2">
                                <option disabled value="0">{{$ml.with('VueJS').get('txtSelectOne')}}</option>
                            </select-2>
                        </div>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtNoPeriod')}}</label>
                        <input v-model="currentItem.no_period" type="checkbox" name="no_period" @change="updatePeriod" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row" v-if="has_period">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtStartDate')}}</label>
                        <datepicker name="startDate" input-class="form-control" placeholder="Select Date" v-model="currentItem.start_date" :format="customFormatter" :disabled-dates="disabledEndDates()" :language="getLanguage(this.$ml)">
                        </datepicker>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="">{{$ml.with('VueJS').get('txtEndDate')}}</label>
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
                    {{$ml.with('VueJS').get('txtUpdate')}}
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
import { vi, ja, en } from 'vuejs-datepicker/dist/locale'
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
            dataLang: {
                vi: vi,
                ja: ja,
                en: en
            }
        }
    },
    mounted() {
        let _this = this;
        
        $(document).on('click', '.languages button', function() {
            if(_this.currentItem) _this.currentItem.id = 0;
        });
    },
    methods: {
        getLanguage(data) {
            return this.dataLang[data.current]
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
        currentItem: [{
            handler: 'currentPeriod'
        }]
    }
}
</script>