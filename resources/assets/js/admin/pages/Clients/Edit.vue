<template>
    <div class="modal fade" id="itemDetail">
        <div class="modal-dialog modal-dialog-centered">
            <div v-if="currentItem" class="modal-content bg-light">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Edit Client</h4>
                    <button type="button" class="btn btn-xs btn-danger ml-2" data-dismiss="modal">
                        <i aria-hidden="true" class="fa fa-times"></i>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <hr>
                    <div class="form-group">
                        <label class="">Name</label>
                        <input v-model="currentItem.name" type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="">Name VI</label>
                        <input v-model="currentItem.name_vi" type="text" name="name_vi" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="">Name JA</label>
                        <input v-model="currentItem.name_ja" type="text" name="name_ja" class="form-control">
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
    export default {
        name: 'EditItem',
        props: ['currentItem']
    }
</script>