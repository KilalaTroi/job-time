<template>
    <modal id="addAttachFile" v-on:reset-validation="resetValidate">
        <template slot="title">{{$ml.with('VueJS').get('txtAttachFile')}}</template>
            <div class="form-group">
                <label class="">File</label>
                <input ref="fileInput" type="file" name="file" v-on:change="handleFileUpload()" required>
            </div>

            <error-item :errors="errors"></error-item>
            <success-list :success="success"></success-list>
            <hr>
            <div class="form-group text-right">
                <button  v-on:click="addAttachFile" class="btn btn-primary">Upload</button>
            </div>
    </modal>
</template>
<script>
import Modal from '../../components/Modals/Modal'
import ErrorItem from '../../components/Validations/Error'
import SuccessList from '../../components/Validations/SuccessList'

export default {
    name: 'update-attach-file',
    components: {
        Modal,
        ErrorItem,
        SuccessList,
    },
    data() {
        return {
            file: '',
            errors: '',
            success: ''
        }
    },
    mounted() {
    },
    methods: {
        addAttachFile() {
            // Reset validate
            this.errors = '';
            this.success = '';

            let formData = new FormData();
            formData.append('file', this.file);

            let uri = "/data/add-attach-file";
            axios
                .post(uri, formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(res => {
                    this.success = res.data.message;
                    this.$emit('reset-upload', res.data.file);
                })
                .catch(err => {
                    if (err.response.status == 422) {
                        this.errors = err.response.data;
                    }

                    this.$emit('reset-upload', null);
                });
        },
        handleFileUpload() {
            if ( typeof(this.$refs.fileInput.files[0]) !== 'undefined' )
                this.file = this.$refs.fileInput.files[0];
        },
        resetValidate() {
            this.errors = '';
            this.success = '';
            this.file = '';
            this.$refs.fileInput.value = '';
        }
    }
}
</script>