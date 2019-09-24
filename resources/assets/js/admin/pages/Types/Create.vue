<template>
    <div class="modal fade" id="itemCreate">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-light">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Create Type</h4>
                    <button type="button" class="btn btn-xs btn-danger ml-2" data-dismiss="modal">
                        <i aria-hidden="true" class="fa fa-times"></i>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <hr>
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
                            <ChromePicker :color="value" v-model="value"></ChromePicker>
                        </div>
                        <hr>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Add</button>
                            <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import ErrorItem from '../../components/Validations/Error'
    import SuccessItem from '../../components/Validations/Success'
    import Modal from '../../components/Modals/Modal'
    import ChromePicker from '../../components/ColorPicker/ChromePicker.vue'
    export default {
        name: 'create-item',
        components: {
            Modal,
            ErrorItem,
            SuccessItem,
            ChromePicker
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
                };
                this.$emit('create-item', newItem);
            },
            resetData(data) {
                // Reset
                if ( data.length ) {
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