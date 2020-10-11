<template>
    <modal id="issueCreate" v-on:reset-validation="resetValidation">
        <template slot="title">{{$ml.with('VueJS').get('txtAddIssue')}}</template>
        <form @submit="addIssue">
            <div class="form-group">
                <label class="">{{$ml.with('VueJS').get('txtProject')}}</label>
                <div>
                    <select-2 :options="projectOptions" v-model="selectedItem.project_id" @input="changeProjects" class="select2">
                        <option disabled value="0">{{$ml.with('VueJS').get('txtSelectOne')}}</option>
                    </select-2>
                </div>
            </div>
            <div class="form-group">
                <label class="">{{$ml.with('VueJS').get('txtName')}}</label>
                <input v-model="selectedItem.i_name" type="text" name="i_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="">{{$ml.with('VueJS').get('txtPage')}}</label>
                <input v-model="selectedItem.page" type="text" name="page" class="form-control">
            </div>
            <div class="form-group">
                <label class="">{{$ml.with('VueJS').get('txtStartDate')}}</label>
                <datepicker name="startDate" input-class="form-control" :placeholder="$ml.with('VueJS').get('txtSelectDate')" v-model="selectedItem.start_date" :format="customFormatter" :disabled-dates="disabledEndDates()" :language="getLangCode(this.$ml)">
                </datepicker>
            </div>
            <div class="form-group">
                <label class="">{{$ml.with('VueJS').get('txtEndDate')}}</label>
                <datepicker name="endDate" input-class="form-control" :placeholder="$ml.with('VueJS').get('txtSelectDate')" v-model="selectedItem.end_date" :format="customFormatter" :disabled-dates="disabledStartDates()" :language="getLangCode(this.$ml)">
                </datepicker>
            </div>
            <error-item :errors="validationErrors"></error-item>
            <success-item :success="validationSuccess"></success-item>
            <hr>
            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary">{{$ml.with('VueJS').get('txtAdd')}}</button>
            </div>
        </form>
    </modal>
</template>
<script>
import Select2 from '../../components/SelectTwo/SelectTwo.vue'
import Datepicker from 'vuejs-datepicker';
import ErrorItem from '../../components/Validations/Error'
import SuccessItem from '../../components/Validations/Success'
import Modal from '../../components/Modals/Modal'
import { mapGetters, mapActions } from "vuex"

export default {
    name: 'AddIssue',

    components: {
        Select2,
        datepicker: Datepicker,
        ErrorItem,
        SuccessItem,
        Modal
    },

    computed: {
        ...mapGetters({
            projectOptions: 'projects/options',
            selectedItem: 'projects/selectedItem',
            validationErrors: 'projects/validationErrors',
            validationSuccess: 'projects/validationSuccess',
            customFormatter: 'customFormatter',
            getObjectByID: 'getObjectByID',
            getLangCode: 'getLangCode'
        })
    },

    methods: {
        ...mapActions({
			addIssue: 'projects/addIssue',
			resetValidate: 'projects/resetValidate',
			resetSelectedItem: 'projects/resetSelectedItem',
		}),
		resetValidation() {
			this.resetValidate();
			this.resetSelectedItem();
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
        changeProjects() {
            console.log(this.selectedItem.project_id)
            const issue_id = this.getObjectByID(this.projectOptions, +this.selectedItem.project_id).issue_id;
            console.log(issue_id)
            const uri = '/data/issues/getpage/' + issue_id;
            axios.get(uri)
                .then(res => {
                    this.selectedItem.page = res.data.page ? res.data.page : '';
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not found!");
                });
        }
    }
}
</script>
<style lang="scss">
input[type="radio"],
input[type="checkbox"] {
    &.form-control {
        width: 40px;
    }
}
</style>