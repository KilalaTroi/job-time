<template>
    <modal id="itemCreate" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">Create Type</template>
        <form @submit="emitCreateItem">
            <div class="form-group">
                <label class="">Slug</label>
                <input v-model="slug" type="text" name="slug" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="">Slug VI</label>
                <input v-model="slug_vi" type="text" name="slug_vi" class="form-control">
            </div>
            <div class="form-group">
                <label class="">Slug JA</label>
                <input v-model="slug_ja" type="text" name="slug_ja" class="form-control">
            </div>
            <div class="form-group">
                <label class="">Type Color</label>
                <color-picker :color="value" v-model="value"></color-picker>
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
    import ColorPicker from '../../components/ColorPicker/ColorPicker'

    export default {
        name: 'create-item',
        components: {
            Modal,
            ErrorItem,
            SuccessItem,
            ColorPicker
        },
        props: ['errors', 'success'],
        data() {
            return {
                slug: '',
                slug_vi: '',
                slug_ja: '',
                value: '#000000',
            }
        },
        methods: {
            emitCreateItem(e) {
                e.preventDefault()
                const newItem = {
                    slug: this.slug,
                    slug_vi: this.slug_vi,
                    slug_ja: this.slug_ja,
                    value: this.value
                };
                this.$emit('create-item', newItem);
            },
            resetData(data) {
                // Reset
                if (data.length) {
                    this.slug = '';
                    this.slug_vi = '';
                    this.slug_ja = '';
                    this.value = '#000000';
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