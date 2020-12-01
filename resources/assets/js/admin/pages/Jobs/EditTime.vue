<template>
    <modal id="itemDetail" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">{{$ml.with('VueJS').get('txtUpdateTime')}}</template>
        <form v-if="currentTimeLog" @submit="emitUpdateTime">
            <div class="form-group">
                <h4 class="text-center mb-1"><b>{{ currentTimeLog.p_name }} {{ currentTimeLog.i_name }} {{ currentTimeLog.phase }}</b></h4>
                <h5 class="text-center mt-1">{{ this.customFormatter(currentTimeLog.date) }}</h5>
            </div>
            <hr>
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-6">
                        <label class=""><strong>{{$ml.with('VueJS').get('lblStartTime')}}</strong></label>
                        <vue-timepicker v-model="currentTimeLog.start_time" hide-disabled-items :minute-range="startMinuteRange" :hour-range="startHourRange"  input-width="100%" close-on-complete @change="changeStartTime" required></vue-timepicker>
                    </div>
                    <div class="col-sm-6">
                        <label class=""><strong>{{$ml.with('VueJS').get('lblEndTime')}}</strong></label>
                        <vue-timepicker v-model="currentTimeLog.end_time" hide-disabled-items :minute-range="endMinuteRange" :hour-range="endHourRange"  input-width="100%" close-on-complete @change="changeEndTime" required :disabled="endDisabled"></vue-timepicker>
                    </div>
                </div>
            </div>
            <div class="form-group" v-if="showLunchBreak">
                <base-checkbox v-model="exceptLunchBreak" class="align-self-end">{{$ml.with('VueJS').get('txtExcludeLunchBreak')}}</base-checkbox>
            </div>
            <div class="form-group" v-if="team == 2">
                <label>{{ $ml.with('VueJS').get('txtWork') }}</label>
                <input
                    type="text"
                    v-model="currentTimeLog.note"
                    class="form-control"
                />
            </div>
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <hr>
            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary" :disabled="buttonDisabled">{{$ml.with('VueJS').get('txtUpdate')}}</button>
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
    props: ['team', 'currentTimeLog', 'logTimeData', 'errors', 'success'],
    data() {
        return {
            startHourRange: [[7, 22]],
            endHourRange: [[7, 22]],
            startMinuteRange: [0, 10, 20, 30, 40, 50],
            endMinuteRange: [0, 10, 20, 30, 40, 50],
            startHour: '',
            startMinute: '',
            buttonDisabled: true,
            endDisabled: false,
            showLunchBreak: false,
            exceptLunchBreak: true
        }
    },
    mounted() {},
    methods: {
        emitUpdateTime(e) {
            e.preventDefault();

            const newTime = {
                note: this.currentTimeLog.note,
                start_time: this.currentTimeLog.start_time,
                end_time: this.currentTimeLog.end_time,
                showLunchBreak: this.showLunchBreak,
                exceptLunchBreak: this.exceptLunchBreak,
            };

            this.$emit('update-time', newTime);
        },
        customFormatter(date) {
            return moment(date).format('DD-MM-YYYY') !== 'Invalid date' ? moment(date).format('DD/MMM/YYYY') : '';
        },
        changeStartTime(eventData) {
            this.startMinute = eventData.data.m*1;
            this.startHour = this.startMinute === 50 ? eventData.data.H*1 + 1 : eventData.data.H*1;
            this.endHourRange = [[this.startHour, 22]];
            this.endMinuteRange = this.startMinute === 50 ? [0, 10, 20, 30, 40, 50] : this.endMinuteRange.filter(item => item > this.startMinute);

            let overlap = false;
            let start_time = this.currentTimeLog.start_time;
            let end_time = this.currentTimeLog.end_time;
            let _this = this;
            this.logTimeData.map(function(value, key) {
                if ( _this.currentTimeLog.id !== value.id && _this.checkTimeOverlap(start_time, end_time, value.start_time, value.end_time) )
                    overlap = true;
            });
            if ( overlap ) {
                this.$emit('overlap-time', { message: ["Overlap time!"] });
                this.buttonDisabled = true;
            } else {
                this.buttonDisabled = false;
                this.$emit('overlap-time', "");
                if ( this.checkTime(this.currentTimeLog.start_time, this.currentTimeLog.end_time) ) this.currentTimeLog.end_time = 'HH:mm';
            }
        },
        changeEndTime(eventData) {
            if ( eventData.data.H*1 > this.startHour ) 
                this.endMinuteRange = [0, 10, 20, 30, 40, 50];
            else 
                this.endMinuteRange = this.startMinute === 50 ? [0, 10, 20, 30, 40, 50] : this.endMinuteRange.filter(item => item > this.startMinute);

            if ( !this.currentTimeLog.end_time.includes('HH') && !this.currentTimeLog.end_time.includes('mm') && this.currentTimeLog.end_time ) {
                let overlap = false;
                let start_time = this.currentTimeLog.start_time;
                let end_time = this.currentTimeLog.end_time;
                let _this = this;
                this.logTimeData.map(function(value, key) {
                    if ( _this.currentTimeLog.id !== value.id && _this.checkTimeOverlap(start_time, end_time, value.start_time, value.end_time) )
                        overlap = true;
                });
                if ( overlap ) {
                    this.$emit('overlap-time', { message: ["Overlap time!"] });
                    this.buttonDisabled = true;
                } else {
                    this.buttonDisabled = false;
                    this.$emit('overlap-time', "");
                }
            } else {
                this.buttonDisabled = true;
            }

            // lunch break
            if ( this.startHour < 12 && eventData.data.H*1 > 13 ) {
                this.showLunchBreak = true;
            } else {
                this.showLunchBreak = false;
            }
        },
        checkTimeOverlap(aStartTime, aEndTime, bStartTime, bEndTime) {
            let aStartTimeArray = aStartTime.split(':');
            let aEndTimeArray = aEndTime.split(':');
            let bStartTimeArray = bStartTime.split(':');
            let bEndTimeArray = bEndTime.split(':');

            let aStartTimeSecond = aStartTimeArray[0]*1*3600 + aStartTimeArray[1]*1*60;
            let aEndTimeSecond = aEndTimeArray[0]*1*3600 + aEndTimeArray[1]*1*60;
            let bStartTimeSecond = bStartTimeArray[0]*1*3600 + bStartTimeArray[1]*1*60;
            let bEndTimeSecond = bEndTimeArray[0]*1*3600 + bEndTimeArray[1]*1*60;

            if ( (aEndTimeSecond <= bStartTimeSecond) || (aStartTimeSecond >= bEndTimeSecond) )
                return false;

            return true;
        },
        checkTime(aStartTime, aEndTime) {
            let aStartTimeArray = aStartTime.split(':');
            let aEndTimeArray = aEndTime.split(':');

            let aStartTimeSecond = aStartTimeArray[0]*1*3600 + aStartTimeArray[1]*1*60;
            let aEndTimeSecond = aEndTimeArray[0]*1*3600 + aEndTimeArray[1]*1*60;

            if ( aStartTimeSecond < aEndTimeSecond )
                return false;

            return true;
        }
    }
}
</script>
<style lang="scss">
@import '~vue2-timepicker/dist/VueTimepicker.css';
</style>