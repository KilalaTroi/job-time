<template>
	<modal id="itemCreate" v-on:reset-validation="resetValidation">
		<template slot="title">{{$ml.with('VueJS').get('txtCreateUser')}}</template>
		<form @submit="emitCreateUser">
			<div class="form-group">
				<label class="">{{$ml.with('VueJS').get('txtName')}}
				</label>
				<input v-model="name" type="text" class="form-control" required>
			</div>
			<div class="form-group">
				<label class="">{{$ml.with('VueJS').get('txtUsername')}}
				</label>
				<input v-model="username" type="text" class="form-control" required>
			</div>
			<div class="form-group">
				<label class="">{{$ml.with('VueJS').get('txtLang')}}
				</label>
				<select-2 v-model="language" class="select2">
					<option value="vi">Vietnamese</option>
                    <option value="ja">Japanese</option>
				</select-2>
			</div>
			<div class="form-group">
				<label class="">{{$ml.with('VueJS').get('txtEmail')}}
				</label>
				<input v-model="email" type="email" class="form-control" required>
			</div>
			<div class="form-group">
				<label class="">{{$ml.with('VueJS').get('txtRole')}}
				</label>
				<div>
					<select-2 :options="roleOptions" v-model="role" class="select2">
						<option disabled value="0">{{$ml.with('VueJS').get('txtSelectRole')}}</option>
					</select-2>
				</div>
			</div>
			<div class="form-group">
				<label class="">{{$ml.with('VueJS').get('txtPassword')}}
				</label>
				<input v-model="password" type="password" name="password" class="form-control" required>
			</div>
			<div class="form-group">
				<label class="">{{$ml.with('VueJS').get('txtRePassword')}}
				</label>
				<input v-model="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
			</div>
			<error-item :errors="validationErrors"></error-item>
			<success-item :success="validationSuccess"></success-item>
			<hr>
			<div class="form-group text-right">
				<button type="submit" class="btn btn-primary">{{$ml.with('VueJS').get('txtCreate')}}
				</button>
			</div>
		</form>
	</modal>
</template>

<script>
import Select2 from '../../components/SelectTwo/SelectTwo.vue'
import Modal from '../../components/Modals/Modal'
import ErrorItem from '../../components/Validations/Error'
import SuccessItem from '../../components/Validations/Success'
import { mapGetters, mapActions } from 'vuex'

export default {
	name: 'CreateItem',

	components: {
		Select2,
		ErrorItem,
		SuccessItem,
		Modal
	},

	data() {
		return {
			name: '',
			username: '',
			email: '',
			language: this.$ml.current,
			role: 0,
			password: '',
			password_confirmation: ''
		}
	},

	computed: {
        ...mapGetters({
            roleOptions: 'users/roleOptions',
            validationErrors: 'users/validationErrors',
			validationSuccess: 'users/validationSuccess'
        })
	},

	methods: {
		...mapActions({
            resetValidate: 'users/resetValidate',
            createUser: 'users/createUser'
		}),
		
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

			this.createUser(newUser);
		},

		resetData(data) {
			// Reset
			if (data.length) {
				this.name = '';
				this.username = '';
				this.role = 0;
				this.email = '';
				this.language = this.$ml.current;
				this.password = '';
				this.password_confirmation = '';
			}
		},

		resetValidation() {
            this.resetValidate()
            this.resetData()
        }
	},
	
	mounted() {
		let _this = this;
		$(document).on('click', '.languages button', function() {
			_this.language = _this.$ml.current
		});
	},

	watch: {
		validationSuccess: [{
			handler: 'resetData'
		}]
	}
}
</script>
<style lang="scss">
</style>