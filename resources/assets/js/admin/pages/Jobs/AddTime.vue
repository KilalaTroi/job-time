<template>
    <modal id="addTime" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">Add Time</template>
        <form v-if="currentJob" @submit="emitAddTime">
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-6">
                        <label class=""><strong>Client:</strong> {{ currentJob.c_name }}</label>
                    </div>
                    <div class="col-sm-6">
                        <label class=""><strong>Dept:</strong> {{ currentJob.d_name }}</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-6">
                        <label class=""><strong>Project:</strong> {{ currentJob.p_name }}</label>
                    </div>
                    <div class="col-sm-6">
                        <label class=""><strong>Issue:</strong> {{ currentJob.i_name }}</label>
                    </div>
                </div>
            </div>
            <hr>
            <div class="form-group">
                <label class=""><strong>Date:</strong> {{ this.customFormatter(currentJob.date) }}</label>
            </div>
            <div class="form-group">
                <label class=""><strong>Time:</strong></label>
                <vue-timepicker v-model="time" :minute-interval="5" required></vue-timepicker>
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
import ErrorItem from '../../components/Validations/Error'
import SuccessItem from '../../components/Validations/Success'
import Modal from '../../components/Modals/Modal'
import VueTimepicker from 'vue2-timepicker'
import moment from 'moment'

export default {
    name: 'AddTime',
    components: {
        ErrorItem,
        SuccessItem,
        Modal,
        VueTimepicker
    },
    props: ['currentJob', 'errors', 'success'],
    data() {
        return {
            time: '00:00',
        }
    },
    mounted() {},
    methods: {
        emitAddTime(e) {
            e.preventDefault()

            const newTime = {
                issue_id: this.currentJob.id,
                time: this.time
            };

            this.$emit('add-time', newTime);
        },
        customFormatter(date) {
            return moment(date).format('DD-MM-YYYY') !== 'Invalid date' ? moment(date).format('DD/MMM/YYYY') : '';
        },
        resetData(data) {
            // Reset
            if (data.length) {
                this.time = '00:00';
            }
        }
    },
    watch: {
        success: [{
            handler: 'resetData'
        }]
    }
}
</script>
<style lang="scss">
@import '~vue2-timepicker/dist/VueTimepicker.css';
</style>