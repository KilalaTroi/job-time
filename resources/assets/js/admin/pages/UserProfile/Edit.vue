<template>
    <modal id="itemDetail" v-on:reset-validation="resetValidation">
        <template slot="title">{{$ml.with('VueJS').get('txtEditUser')}}</template>
        <div v-if="selectedUser">
            <div class="form-group">
                <label class="">{{$ml.with('VueJS').get('txtName')}}</label>
                <input v-model="selectedUser.name" type="text" class="form-control">
            </div>
            <div class="form-group">
                <label class="">{{$ml.with('VueJS').get('txtUsername')}}</label>
                <input v-model="selectedUser.username" type="text" class="form-control">
            </div>
            <div class="form-group">
                <label class="">{{$ml.with('VueJS').get('txtEmail')}}</label>
                <input v-model="selectedUser.email" type="email" class="form-control">
            </div>
            <div class="form-group">
                <label class="">{{$ml.with('VueJS').get('txtLang')}}</label>
                <select-2 v-model="selectedUser.language" class="select2">
                    <option value="vi">Vietnamese</option>
                    <option value="ja">Japanese</option>
                </select-2>
            </div>
            <div class="form-group">
                <label class="">{{$ml.with('VueJS').get('txtRole')}}</label>
                <div>
                    <select-2 :options="roleOptions" v-model="selectedUser.r_name" class="select2">
                        <option disabled value="0">Select role</option>
                    </select-2>
                </div>
            </div>
            <div class="form-group">
                <label class="">Disable date</label>
                <datepicker name="disable_date" input-class="form-control" placeholder="Select Date" v-model="selectedUser.disable_date" :format="customFormatter" :language="getLangCode(this.$ml)">
                </datepicker>
            </div>
            <div class="form-group">
                <label class="">{{$ml.with('VueJS').get('txtPassword')}}</label>
                <input v-model="password" type="password" name="password" class="form-control">
            </div>
            <div class="form-group">
                <label class="">{{$ml.with('VueJS').get('txtRePassword')}}</label>
                <input v-model="password_confirmation" type="password" name="password_confirmation" class="form-control">
            </div>
            <error-item :errors="validationErrors"></error-item>
            <success-item :success="validationSuccess"></success-item>
            <hr>
            <div class="form-group text-right">
                <button @click="emitUser" type="button" class="btn btn-primary">
                    {{$ml.with('VueJS').get('txtUpdate')}}
                </button>
            </div>
        </div>
    </modal>
</template>
<script>
import Select2 from '../../components/SelectTwo/SelectTwo.vue'
import Modal from '../../components/Modals/Modal'
import ErrorItem from '../../components/Validations/Error'
import SuccessItem from '../../components/Validations/Success'
import Datepicker from 'vuejs-datepicker'
import { mapGetters, mapActions } from 'vuex'

export default {
    name: 'EditItem',

    components: {
        Select2,
        ErrorItem,
        SuccessItem,
        Modal,
        Datepicker
    },

    data() {
        return {
            password: '',
            password_confirmation: ''
        }
    },

    computed: {
        ...mapGetters({
            roleOptions: 'users/roleOptions',
            selectedUser: 'users/selectedUser',
            validationErrors: 'users/validationErrors',
            validationSuccess: 'users/validationSuccess',
            dateFormat: 'dateFormat',
            getLangCode: 'getLangCode'
        })
    },

    methods: {
        ...mapActions({
            resetValidate: 'users/resetValidate',
            resetSelectedUser: 'users/resetSelectedUser',
            updateUser: 'users/updateUser'
        }),

        emitUser() {
            let user = Object.assign({}, {
                password: this.password,
                password_confirmation: this.password_confirmation
            }, this.selectedUser);

            this.updateUser(user);
        },

        customFormatter(date) {
            return this.dateFormat(date, 'YYYY/MM/DD');
        },

        resetValidation() {
            this.resetValidate()
            this.resetSelectedUser()
        }
    }
}
</script>