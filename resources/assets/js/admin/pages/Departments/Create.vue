<template>
    <div class="modal fade" id="itemCreate">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Create Department</h4>
                    <button type="button" class="btn btn-xs btn-danger ml-2" data-dismiss="modal">
                        <i aria-hidden="true" class="fa fa-times"></i>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <hr>
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
    export default {
        name: 'CreateItem',
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
                }

                this.$emit('create-item', newItem);

                // Reset
                this.name = '';
                this.name_vi = '';
                this.name_ja = '';
            }
        }
    }
</script>