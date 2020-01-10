<template>
    <modal id="itemCreate" v-on:reset-validation="$emit('reset-validation')">
        <template slot="title">Create User</template>
        <form @submit="emitCreateUser">
            <div class="form-group">
                <label class="">Name</label>
                <input v-model="name" type="text" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="">Username</label>
                <input v-model="username" type="text" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="">Language</label>
                <select-2 v-model="language" class="select2">
                    <option value="en">English</option>
                    <option value="ja">Japanese</option>
                </select-2>
            </div>
            <div class="form-group">
                <label class="">Email</label>
                <input v-model="email" type="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="">Role</label>
                <div>
                    <select-2 :options="rolesOption" v-model="role" class="select2">
                        <option disabled value="0">Select role</option>
                    </select-2>
                </div>
            </div>
            <div class="form-group">
                <label class="">Password</label>
                <input v-model="password" type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="">Re-Password</label>
                <input v-model="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
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
    props: ['roles', 'errors', 'success'],
    data() {
        return {
            name: '',
            username: '',
            email: '',
            language: 'en',
            role: 0,
            password: '',
            password_confirmation: '',
            rolesOption: []
        }
    },
    mounted() {},
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
        emitCreateUser(e) {
            e.preventDefault();

            const newUser = {
                name: this.name,
                username: this.username,
                email: this.email,
                language: this.language,
                role: this.role,
                password: this.password,
                password_confirmation: this.password_confirmation
            };

            this.$emit('create-user', newUser);
        },
        customFormatter(date) {
            return moment(date).format('DD-MM-YYYY');
        },
        disabledStartDates() {
            let obj = {
                to: new Date(this.start_date), // Disable all dates after specific date
                // days: [0], // Disable Saturday's and Sunday's
            };
            return obj;
        },
        disabledEndDates() {
            let obj = {
                from: new Date(this.end_date), // Disable all dates after specific date
                // days: [0], // Disable Saturday's and Sunday's
            };
            return obj;
        },
        resetData(data) {
            // Reset
            if (data.length) {
                this.name = '';
                this.username = '';
                this.role = 0;
                this.email = '';
                this.language = 'en';
                this.password = '';
                this.password_confirmation = '';
            }
        }
    },
    watch: {
        roles: [{
            handler: 'getDataRoles'
        }],
        success: [{
            handler: 'resetData'
        }]
    }
}
</script>
<style lang="scss">
</style>