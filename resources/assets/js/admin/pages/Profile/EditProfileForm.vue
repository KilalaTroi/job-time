<template>
    <card>
        <h4 slot="header" class="card-title">{{$ml.with('VueJS').get('txtEditProfile')}}</h4>
        <form>
            <div class="row">
                <div class="col-md-6">
                    <base-input type="text" :label="this.$ml.with('VueJS').get('txtName')" v-model="user.name">
                    </base-input>
                </div>
                <div class="col-md-6">
                    <base-input type="text" :label="this.$ml.with('VueJS').get('txtUsername')" v-model="user.username">
                    </base-input>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <base-input type="text" :label="this.$ml.with('VueJS').get('txtEmail')" v-model="user.email">
                    </base-input>
                </div>
                <div class="col-md-6">
                    <base-input type="text" :label="this.$ml.with('VueJS').get('txtRole')" :disabled="true"  v-model="role.name">
                    </base-input>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <label class="">{{$ml.with('VueJS').get('txtLang')}}</label>
                    <select-2 v-model="user.language" class="select2">
                        <option value="vi">English</option>
                        <option value="ja">Japanese</option>
                    </select-2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <base-input type="password" :label="this.$ml.with('VueJS').get('txtPassword')" v-model="password">
                    </base-input>
                </div>
                <div class="col-md-6">
                    <base-input type="password" :label="this.$ml.with('VueJS').get('txtRePassword')" v-model="password_confirmation">
                    </base-input>
                </div>
            </div>
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <div class="text-center">
                <button type="submit" class="btn btn-info btn-fill float-right" @click.prevent="updateProfile">
                    {{$ml.with('VueJS').get('txtUpdate')}}
                </button>
            </div>
            <div class="clearfix"></div>
        </form>
    </card>
</template>
<script>
import Card from '../../components/Cards/Card.vue'
import ErrorItem from '../../components/Validations/Error'
import SuccessItem from '../../components/Validations/Success'
import Select2 from '../../components/SelectTwo/SelectTwo.vue'

export default {
    components: {
        Card,
        ErrorItem,
        SuccessItem,
        Select2
    },
    data() {
        return {
            userID: document.querySelector("meta[name='user-id']").getAttribute('content'),
            user: {},
            role: {},
            password: '',
            password_confirmation: '',
            errors: '',
            success: ''
        }
    },
    mounted() {
        this.fetch();
    },
    methods: {
        fetch() {
            let uri = '/data/users/' + this.userID;
            axios.get(uri).then((response) => {
                this.role = response.data.role;
                this.user = response.data.user;
            });
        },
        updateProfile() {
            // Reset validate
            this.success = '';
            this.errors = '';

            let user = Object.assign({}, {
                        password: this.password,
                        password_confirmation: this.password_confirmation
                    }, this.user);

            let uri = '/data/users/' + user.id;
            axios.patch(uri, user).then((res) => {
                    this.success = res.data.message;
                })
                .catch(err => {
                    console.log(err);
                    if (err.response.status == 422) {
                        this.errors = err.response.data;
                    }
                });
        },
    }
}
</script>
<style>
</style>