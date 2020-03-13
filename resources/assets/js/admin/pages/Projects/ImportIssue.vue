<template>
    <modal id="issueImport" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">Import Projects</template>
            <div class="form-group">
                <label class="">File</label>
                <input ref="fileInput" type="file" name="file" v-on:change="handleFileUpload()" required>
            </div>
            
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <hr>
            <div class="form-group text-right">
                <button  v-on:click="importIssue" class="btn btn-primary">Import</button>
            </div>
    </modal>
</template>
<script>
import Modal from '../../components/Modals/Modal'
import ErrorItem from '../../components/Validations/Error'
import SuccessItem from '../../components/Validations/Success'

export default {
    name: 'ImportIssue',
    components: {
        Modal,
        ErrorItem,
        SuccessItem,
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
        importIssue() {
            // Reset validate
            this.errors = '';
            this.success = '';

            let formData = new FormData();
            formData.append('file', this.$refs.fileInput.files[0]);

            let uri = "/data/import-projects/";
            axios
                .post(uri, formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(res => {
                    console.log(res.data);
                    this.success = res.data.message;
                })
                .catch(err => {
                    if (err.response.status == 422) {
                        this.errors = err.response.data;
                    }
                });
        },
        handleFileUpload(){
            this.file = this.$refs.fileInput.files[0];
        }
    }
}
</script>