<template>
    <modal id="itemDetail" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">Update Time</template>
        <form v-if="currentTimeLog" @submit="emitUpdateTime">
            <div class="form-group">
                <h4 class="text-center mb-1"><b>{{ currentTimeLog.p_name }} {{ currentTimeLog.i_name }}</b></h4>
                <h5 class="text-center mt-1">{{ this.customFormatter(currentTimeLog.date) }}</h5>
            </div>
            <hr>
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-6">
                        <label class=""><strong>Start Time:</strong></label>
                        <vue-timepicker v-model="currentTimeLog.start_time" hide-disabled-items :minute-range="startMinuteRange" :hour-range="startHourRange"  input-width="100%" close-on-complete @change="changeStartTime" required></vue-timepicker>
                    </div>
                    <div class="col-sm-6">
                        <label class=""><strong>End Time:</strong></label>
                        <vue-timepicker v-model="currentTimeLog.end_time" hide-disabled-items :minute-range="endMinuteRange" :hour-range="endHourRange"  input-width="100%" close-on-complete @change="changeEndTime" required :disabled="endDisabled"></vue-timepicker>
                    </div>
                </div>
            </div>
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <hr>
            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary" :disabled="buttonDisabled">Update</button>
                <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </modal>
</template>
<script>
import ErrorItem from '../../components/Validations/Error'
import SuccessItem from '../../components/Validations/Success'
import Modal from '../../components/Modals/Modal'
import VueTimepicker from 'vue2-timepicker'
import moment from 'moment'

export default {
    name: 'EditTime',
    components: {
        ErrorItem,
        SuccessItem,
        Modal,
        VueTimepicker
    },
    props: ['currentTimeLog', 'errors', 'success'],
    data() {
        return {
            startHourRange: [[7, 19]],
            endHourRange: [[7, 19]],
            startMinuteRange: [0, 10, 20, 30, 40, 50],
            endMinuteRange: [0, 10, 20, 30, 40, 50],
            startHour: '',
            startMinute: '',
            buttonDisabled: true,
            endDisabled: true
        }
    },
    mounted() {},
    methods: {
        emitUpdateTime(e) {
            e.preventDefault()

            const newTime = {
                start_time: this.currentTimeLog.start_time,
                end_time: this.currentTimeLog.end_time
            };

            this.$emit('update-time', newTime);
        },
        customFormatter(date) {
            return moment(date).format('DD-MM-YYYY') !== 'Invalid date' ? moment(date).format('DD/MMM/YYYY') : '';
        },
        changeStartTime(eventData) {
            this.startMinute = eventData.data.m*1;
            this.startHour = this.startMinute === 50 ? eventData.data.H*1 + 1 : eventData.data.H*1;
            this.endHourRange = [[this.startHour, 19]];
            this.endMinuteRange = this.startMinute === 50 ? [0, 10, 20, 30, 40, 50] : this.endMinuteRange.filter(item => item > this.startMinute);
            this.currentTimeLog.end_time = '';
            
            if ( !this.currentTimeLog.start_time.includes('HH') && !this.currentTimeLog.start_time.includes('mm') && this.currentTimeLog.start_time ) {
                this.endDisabled = false;
            } else {
                this.currentTimeLog.end_time = 'HH:mm';
                this.endDisabled = true;
            }
        },
        changeEndTime(eventData) {
            if ( eventData.data.H*1 > this.startHour ) 
                this.endMinuteRange = [0, 10, 20, 30, 40, 50];
            else 
                this.endMinuteRange = this.startMinute === 50 ? [0, 10, 20, 30, 40, 50] : this.endMinuteRange.filter(item => item > this.startMinute);

            if ( !this.currentTimeLog.end_time.includes('HH') && !this.currentTimeLog.end_time.includes('mm') && this.currentTimeLog.end_time ) {
                this.buttonDisabled = false;
            } else {
                this.buttonDisabled = true;
            }
        }
    }
}
</script>
<style lang="scss">
@import '~vue2-timepicker/dist/VueTimepicker.css';
</style>