<template>
    <modal id="issueImport" v-on:reset-validation="resetValidate">
        <template slot="title">Import Issues</template>
            <div class="form-group">
                <label class="">File</label>
                <input ref="fileInput" type="file" name="file" v-on:change="handleFileUpload()" required>
            </div>
            
            <error-item :errors="errors"></error-item>
            <success-list :success="success"></success-list>
            <hr>
            <div class="form-group text-right">
                <button  v-on:click="importIssue" class="btn btn-primary">Import</button>
            </div>
    </modal>
</template>
<script>
import Modal from '../../components/Modals/Modal'
import ErrorItem from '../../components/Validations/Error'
import SuccessList from '../../components/Validations/SuccessList'

export default {
    name: 'import-issue',
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
        importIssue() {
            // Reset validate
            this.errors = '';
            this.success = '';

            let formData = new FormData();
            formData.append('file', this.file);

            let uri = "/data/import-projects";
            axios
                .post(uri, formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(res => {
                    this.success = res.data.message;
                    this.$emit('reset-import');
                })
                .catch(err => {
                    if (err.response.status == 422) {
                        this.errors = err.response.data;
                    }
                    if (err.response.status == 403) {
                        this.errors = err.response.data.errors;
                        this.success = err.response.data.success;
                    }
                    this.$emit('reset-import');
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