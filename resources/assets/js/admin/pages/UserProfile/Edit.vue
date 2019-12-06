<template>
    <modal id="itemDetail" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">Edit User</template>
        <div v-if="currentUser">
            <div class="form-group">
                <label class="">Name</label>
                <input v-model="currentUser.name" type="text" class="form-control">
            </div>
            <div class="form-group">
                <label class="">Username</label>
                <input v-model="currentUser.username" type="text" class="form-control">
            </div>
            <div class="form-group">
                <label class="">Email</label>
                <input v-model="currentUser.email" type="email" class="form-control">
            </div>
            <div class="form-group">
                <label class="">Role</label>
                <div>
                    <select-2 :options="rolesOption" v-model="currentUser.r_name" class="select2">
                        <option disabled value="0">Select role</option>
                    </select-2>
                </div>
            </div>
            <div class="form-group">
                <label class="">Password</label>
                <input v-model="password" type="password" name="password" class="form-control">
            </div>
            <div class="form-group">
                <label class="">Re-Password</label>
                <input v-model="password_confirmation" type="password" name="password_confirmation" class="form-control">
            </div>
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <hr>
            <div class="form-group">
                <button @click="emitUser" type="button" class="btn btn-primary">
                    Update
                </button>
                <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </modal>
</template>
<script>
import Select2 from '../../components/SelectTwo/SelectTwo.vue'
import Modal from '../../components/Modals/Modal'
import ErrorItem from '../../components/Validations/Error'
import SuccessItem from '../../components/Validations/Success'

export default {
    name: 'EditItem',
    components: {
        Select2,
        ErrorItem,
        SuccessItem,
        Modal
    },
    props: ['currentUser', 'roles', 'errors', 'success'],
    data() {
        return {
            password: '',
            password_confirmation: '',
            rolesOption: []
        }
    },
    methods: {
        getDataRoles(data) {
            if (data.length) {
                let dataOptions = [];
                let obj = {
                    id: 0,
                    text: "Select role"
                };
                dataOptions.push(obj);

                for (let i = 0; i < data.length; i++) {
                    let obj = {
                        id: data[i].name,
                        text: data[i].name
                    };
                    dataOptions.push(obj);
                }
                this.rolesOption = dataOptions;
            }
        },
        emitUser() {
            let user = Object.assign({}, {
                password: this.password,
                password_confirmation: this.password_confirmation
            }, this.currentUser);

            this.$emit('update-user', user);
        }
    },
    watch: {
        roles: [{
            handler: 'getDataRoles'
        }]
    }
}
</script>