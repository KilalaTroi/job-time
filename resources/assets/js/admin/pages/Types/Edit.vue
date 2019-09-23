<template>
    <div class="modal fade" id="itemDetail">
        <div class="modal-dialog modal-dialog-centered">
            <div v-if="currentItem" class="modal-content bg-light">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Edit Type</h4>
                    <button type="button" class="btn btn-xs btn-danger ml-2" data-dismiss="modal">
                        <i aria-hidden="true" class="fa fa-times"></i>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <hr>
                    <div class="form-group">
                        <label class="">Slug</label>
                        <input v-model="currentItem.slug" type="text" name="slug" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="">Slug VI</label>
                        <input v-model="currentItem.slug_vi" type="text" name="slug_vi" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="">Slug JA</label>
                        <input v-model="currentItem.slug_ja" type="text" name="slug_ja" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="">Type Color</label>
                        <ChromePicker :color="currentItem.value" v-model="currentItem.value"></ChromePicker>
                    </div>
                    <hr>
                    <div class="form-group">
                        <button @click="$emit('update-item', currentItem)" type="button" class="btn btn-primary">
                            Update
                        </button>
                        <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import ChromePicker from '../../components/ColorPicker/ChromePicker.vue'

    export default {
        name: 'EditItem',
        components: {
            ChromePicker
        },
        props: ['currentItem']
    }
</script>