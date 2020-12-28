<template>
	<modal id="editProject" :sizeClasses="modalLg" v-on:reset-validation="resetValidation">
		<template slot="title">
			{{$ml.with('VueJS').get('txtEditProject')}}     
		</template>
		<div v-if="selectedItem">
			<div class="row">
				<div class="col-sm-12">
					<div class="form-group">
						<label class>{{$ml.with('VueJS').get('txtName')}}</label>
						<input
							v-model="selectedItem.p_name"
							type="text"
							name="name"
							class="form-control"
							required
						/>
					</div>
				</div>
			</div>

			<hr />

			<div class="row">
				<div class="col-sm-6">
					<div class="form-group">
						<label class>
							{{$ml.with('VueJS').get('txtTypes')}}              
						</label>
						<div>
							<select2-type :options="typeOptions" v-model="selectedItem.type_id" class="select2" />
						</div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<label class="">{{$ml.with('VueJS').get('txtTeam')}}</label>
						<div>
							<multiselect
                            :multiple="true"
                            v-model="selectedItem.team"
                            :options="currentTeamOption"
                            :clear-on-select="false"
                            :preserve-search="false"
                            :placeholder="$ml.with('VueJS').get('txtPickSome')"
                            label="text"
                            track-by="text"
                            ></multiselect>
						</div>
					</div>
				</div>
			</div>
			
			<error-item :errors="validationErrors"></error-item>
			<success-item :success="validationSuccess"></success-item>
			<hr />
			<div class="form-group text-right">
				<button
					@click="updateItem(selectedItem)"
					type="button"
					class="btn btn-primary"
				>{{$ml.with('VueJS').get('txtUpdate')}}</button>
			</div>
		</div>
	</modal>
</template>

<script>
import Select2 from "../../components/SelectTwo/SelectTwo.vue"
import Select2Type from "../../components/SelectTwo/SelectTwoType.vue"
import Modal from "../../components/Modals/Modal"
import ErrorItem from "../../components/Validations/Error"
import SuccessItem from "../../components/Validations/Success"
import Multiselect from "vue-multiselect"
import { mapGetters, mapActions } from "vuex"

export default {
	name: "edit-project",

	components: {
		Select2,
		Select2Type,
		ErrorItem,
		SuccessItem,
		Modal,
		Multiselect
	},

	computed: {
        ...mapGetters({
            currentTeamOption: 'currentTeamOption',
            typeOptions: 'types/options',
            selectedItem: 'projects/selectedItem',
            validationErrors: 'projects/validationErrors',
            validationSuccess: 'projects/validationSuccess',
        })
	},
	
	data() {
		return {
			modalLg: "modal-lg"
		};
	},

	methods: {
		...mapActions({
			updateItem: 'projects/updateItem',
			resetValidate: 'projects/resetValidate',
			resetSelectedItem: 'projects/resetSelectedItem',
		}),
		
		resetValidation() {
			this.resetValidate();
			this.resetSelectedItem();
		},
	}
};
</script>