<template>
    <modal id="itemCreate" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">Create Department</template>
        <form @submit="emitCreateItem">
            <div class="form-group">
                <label class="">Name</label>
                <input v-model="name" type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="">Name VI</label>
                <input v-model="name_vi" type="text" name="name_vi" class="form-control">
            </div>
            <div class="form-group">
                <label class="">Name JA</label>
                <input v-model="name_ja" type="text" name="name_ja" class="form-control">
            </div>
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <hr>
            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary">Create</button>
                <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </modal>
</template>

<script>
    import ErrorItem from '../../components/Validations/Error'
    import SuccessItem from '../../components/Validations/Success'
    import Modal from '../../components/Modals/Modal'

    export default {
        name: 'create-item',
        components: {
            Modal,
            ErrorItem,
            SuccessItem
        },
        props: ['errors', 'success'],
        data() {
            return {
                name: '',
                name_vi: '',
                name_ja: '',
            }
        },
        methods: {
            emitCreateItem(e) {
                e.preventDefault()
                const newItem = {
                    name: this.name,
                    name_vi: this.name_vi,
                    name_ja: this.name_ja,
                };
                this.$emit('create-item', newItem);
            },
            resetData(data) {
                // Reset
                if (data.length) {
                    this.name = '';
                    this.name_vi = '';
                    this.name_ja = '';
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