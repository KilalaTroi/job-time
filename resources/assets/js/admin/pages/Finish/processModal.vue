<template>
	<div>
		<modal id="processModal" :sizeClasses="modalLg" v-on:reset-validation="resetValidate">
			<template slot="title">{{$ml.with('VueJS').get('txtFinish')}}</template>
			<div v-if="currentProcess">
				<div class="table-responsive">
					<table-no-action class="table-hover table-striped" :columns="columns" :data="dataProcess"></table-no-action>
				</div>
				<div v-if="!sendSuccess" class="form-group">
					<h5>{{$ml.with('VueJS').get('txtMessage')}}</h5>
					<textarea v-model="newMessage" class="form-control" rows="5"></textarea>
					<base-checkbox class="form-control mt-3" v-model="status">{{$ml.with('VueJS').get('txtFinish')}}</base-checkbox>
				</div>
				<error-item :errors="errors"></error-item>
				<success-item :success="success"></success-item>
				<div class="form-group d-flex justify-content-center">
					<button v-if="!sendSuccess" type="button" class="btn btn-primary mr-3" @click="finishProcess">{{$ml.with('VueJS').get('txtSend')}}</button>
					<button type="button" class="btn btn-second" @click="resetValidate">Cancel</button>
				</div>
			</div>
		</modal>
		<loading :active.sync="isLoading" :is-full-page="true" :can-cancel="true" :on-cancel="onCancel"></loading>
	</div>
</template>

<script>
import TableNoAction from "../../components/TableNoAction";
import ErrorItem from "../../components/Validations/Error";
import SuccessItem from "../../components/Validations/Success";
import Modal from "../../components/Modals/Modal";
import moment from 'moment';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
	name: "process-modal",
	components: {
		Modal,
		ErrorItem,
		SuccessItem,
		TableNoAction,
		Loading
	},
	props: ["currentProcess"],
	data() {
		return {
			columns: [
			{ id: "p_name", value: this.$ml.with("VueJS").get("txtProject"), width: "", class: "" },
			{ id: "i_name", value: this.$ml.with("VueJS").get("txtIssue"), width: "", class: "" },
			{ id: "phase", value: this.$ml.with("VueJS").get("txtPhase"), width: "", class: "" }
			],
			dataProcess: [],
			newMessage: "[Finish]\nFinished this work.",
			errors: "",
			success: "",
			sendSuccess: false,
			modalLg: "modal-lg",
			isLoading: false,
			status: false
		};
	},
	mounted() {
		let _this = this;
		$(document).on('click', '.languages button', function() {
			_this.columns = [
			{ id: "p_name", value: _this.$ml.with("VueJS").get("txtProject"), width: "", class: "" },
			{ id: "i_name", value: _this.$ml.with("VueJS").get("txtIssue"), width: "", class: "" },
			{ id: "phase", value: _this.$ml.with("VueJS").get("txtPhase"), width: "", class: "" },
			];
		});
	},
	methods: {
		async sendMessageLineWork(content) {
			if ( this.currentProcess.room_id ) {
				let uri = "/data/finish/submit-message";
				await axios.post(uri, {
						roomId: this.currentProcess.room_id,
						content: content,
					}).then(res => {
						console.log(res.data);
						if ( res.data.code === 200 ) {
							this.success = "Successfully."
						} else {
							this.errors = [[res.data.errorMessage]]
						}
					}).catch(err => {
						console.log(err);
					});
			}	
		},
		getDataProcess() {
			this.dataProcess = [
			{
				p_name: this.currentProcess.project,
				i_name: this.currentProcess.issue,
				phase: this.currentProcess.phase
			}
			];
		},
		finishProcess() {
			// Reset validate
			this.errors = "";
			this.success = "";
			this.isLoading = true;

			this.newMessage += '\n----------------------------\n';
			this.newMessage += 'Project: ' + this.currentProcess.project + '\n';
			this.newMessage += 'Issue: ' + (this.currentProcess.issue ? this.currentProcess.issue : '--') + '\n';
			this.newMessage += 'Phase: ' + (this.currentProcess.phase ? this.currentProcess.phase : '--') + '\n';

			if ( this.status != this.currentProcess.status ) {
				this.$emit('change-status-process', this.currentProcess); 
			}

			this.sendMessageLineWork(this.newMessage).then(res => {
				this.isLoading = false;
				this.newMessage = "[Finish]\nFinished this work.";
			});
			
		},
        resetValidate() {
        	$('#processModal').modal('hide');
            this.errors = '';
            this.success = '';
            this.newMessage = '[Finish]\nFinished this work.';
            this.$emit('reset-validation')
        },
        onCancel() {
        	window.location.reload();
        }
	},
	watch: {
		currentProcess: [
		{
			handler: "getDataProcess"
		},
		{
			handler: function() {
				this.status = this.currentProcess.status;
			}
		}
		]
	}
};
</script>
<style lang="scss">
.btn-link {
cursor: pointer;
}
#commentsModal {
.form-check-sign {
&:after, &:before {
font-size: 26px;
width: 25px;
height: 25px;
margin-left: -29px;
}
}
}
#processModal {
#selectDest .form-control {font-size: 14px;padding: 5px 12px;line-height: 20px;height: 30px;}
.modal-dialog {
.modal-content {
.modal-body {
h5 {
font-weight: 600;
margin-bottom: 10px;
}
.form-control {
border-radius: 0;
}
#selectDest {
h5 {
position: relative;
text-transform: uppercase;
}
p {
margin: 0;
font-size: 14px;
}
.form-group {
background: #ffffff;
label {
cursor: pointer;
font-size: 14px;
font-weight: 600;
position: absolute;
right: 0;
opacity: 0.8;
&.dest-box {
color: #005fdd;
}
&.dest-file {
color: #f83f86;
}
&:hover {
opacity: 1;
}
}
}
}
.table-responsive {
td {
padding: 5px 8px;
}
}
.form-check {
.form-check-sign {
&:before,
&:after {
top: 2px;
}
}
span {
color: #333333;
font-size: 16px;
}
}
}
}
}
}
</style>