<template>
    <card>
        <h4 slot="header" class="card-title">Edit Profile</h4>
        <form>
            <div class="row">
                <div class="col-md-6">
                    <base-input type="text" label="Name" placeholder="Name" v-model="user.name">
                    </base-input>
                </div>
                <div class="col-md-6">
                    <base-input type="text" label="Username" placeholder="Username" v-model="user.username">
                    </base-input>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <base-input type="text" label="Email" placeholder="Email" v-model="user.email">
                    </base-input>
                </div>
                <div class="col-md-6">
                    <base-input type="text" label="Role" :disabled="true" placeholder="Role" v-model="role.name">
                    </base-input>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <label class="">Language</label>
                    <select-2 v-model="user.language" class="select2">
                        <option value="en">English</option>
                        <option value="ja">Japanese</option>
                    </select-2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <base-input type="password" label="Password" placeholder="Password" v-model="password">
                    </base-input>
                </div>
                <div class="col-md-6">
                    <base-input type="password" label="Re-Password" placeholder="Re-Password" v-model="password_confirmation">
                    </base-input>
                </div>
            </div>
            <error-item :errors="errors"></error-item>
            <success-item :success="success"></success-item>
            <div class="text-center">
                <button type="submit" class="btn btn-info btn-fill float-right" @click.prevent="updateProfile">
                    Update Profile
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