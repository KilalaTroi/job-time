<template>
    <modal id="editEvent">
        <div v-if="currentEvent">
            <div class="form-group text-center">
                <h4 class="mb-1"><b>Off day</b></h4>
                <h4 class="mb-1 mt-2">{{ currentEvent.title }}</h4>
                <h5 class="mt-1">{{ this.customFormatter(currentEvent.start) }}</h5>
            </div>
            <hr>
            <div class="form-group text-right">
                <button type="button"  @click="$emit('delete-event', currentEvent)" class="btn btn-danger ml-3">Delete</button>
                <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </modal>
</template>
<script>
import moment from 'moment'
import Modal from '../../components/Modals/Modal'

export default {
    name: 'EditEvent',
    components: {
        Modal
    },
    props: ['currentEvent'],
    data() {
        return {
        }
    },
    methods: {
        customFormatter(date) {
            return moment(date).format('DD-MM-YYYY') !== 'Invalid date' ? moment(date).format('DD MMM YYYY') : '';
        }
    }
}
</script>