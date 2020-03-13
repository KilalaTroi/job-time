<template>
    <modal id="editEvent" v-on:reset-validation="$emit('reset-validation')">
        <div v-if="currentEvent">
            <div class="form-group">
                <h4 class="text-center mb-1"><b>{{ currentEvent.title_not_memo }}</b></h4>
                <h5 class="text-center mt-1">{{ this.hourFormatter(currentEvent.start) }} - {{ this.hourFormatter(currentEvent.end) }}</h5>
            </div>
            <hr>
            <div class="form-group">
                <label>Phase</label>
                <input type="text" name="memo"  v-model="currentEvent.memo" class="form-control project-memo">
            </div>
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <hr>
            <div class="form-group text-right">
                <button @click="$emit('update-event', currentEvent)" type="button" class="btn btn-primary">Save</button>
                <button type="button"  @click="$emit('delete-event', currentEvent)" class="btn btn-danger ml-3">Delete</button>
            </div>
        </div>
    </modal>
</template>
<script>
import moment from 'moment'
import Modal from '../../components/Modals/Modal'
import ErrorItem from '../../components/Validations/Error'
import SuccessItem from '../../components/Validations/Success'

export default {
    name: 'EditEvent',
    components: {
        ErrorItem,
        SuccessItem,
        Modal
    },
    props: ['currentEvent', 'errors', 'success'],
    data() {
        return {
        }
    },
    methods: {
        hourFormatter(date) {
            return moment(date).format('HH:mm');
        }
    }
}
</script>