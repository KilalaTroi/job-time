<template>
	<modal id="processModal" :sizeClasses="modalLg" v-on:reset-validation="resetValidate">
		<template slot="title">{{$ml.with('VueJS').get('txtUpload')}}</template>
		<div v-if="currentProcess">
			<h5>{{$ml.with('VueJS').get('txtProjectIssue')}}</h5>
			<div class="table-responsive">
				<no-action-table class="table-hover table-striped" :columns="columns" :data="dataProcess"></no-action-table>
			</div>
			<div class="form-group">
				<h5>{{$ml.with('VueJS').get('txtMessage')}}</h5>
				<textarea v-model="newMessage" class="form-control" rows="2"></textarea>
			</div>
			<div id="selectDest" class="d-flex justify-content-between">
				<div class="form-group border p-3">
					<h5>
						{{$ml.with('VueJS').get('txtBoxDestination')}}
						<label class="dest-box" for="destBox">{{$ml.with('VueJS').get('txtSelect')}}</label>
						<input type="file" id="destBox" name />
					</h5>
					<p>https://yuidea.app.box.com/folder/49217853872</p>
				</div>
				<div class="form-group border p-3">
					<h5>
						File
						<label class="dest-file" for="destFile">{{$ml.with('VueJS').get('txtSelect')}}</label>
						<input type="file" id="destFile" name />
					</h5>
					<p>\\192.168.0.233\daichi\tsuchi_kilala\Job\2020_114\1st\indd</p>
				</div>
			</div>
			<div class="form-group d-flex justify-content-between">
				<base-checkbox v-model="currentProcess.status" v-on:input="changeStatus">{{$ml.with('VueJS').get('txtFinish')}}</base-checkbox>
				<button type="button" class="btn btn-primary" @click="uploadProcess">{{$ml.with('VueJS').get('txtSend')}}</button>
			</div>
			<error-item :errors="errors"></error-item>
			<success-item :success="success"></success-item>
			<div v-if="listComments.length">
				<hr />
				<div class="form-group">
					<h5>{{$ml.with('VueJS').get('txtProcessList')}}</h5>
					<div class="table-responsive">
						<no-action-table
						class="table-hover table-striped"
						:columns="columns2"
						:data="listComments"
						></no-action-table>
					</div>
				</div>
			</div>
		</div>
	</modal>
</template>

<script>
import NoActionTable from "../../components/TableNoAction";
import ErrorItem from "../../components/Validations/Error";
import SuccessItem from "../../components/Validations/Success";
import Modal from "../../components/Modals/Modal";
import moment from 'moment';

export default {
	name: "process-modal",
	components: {
		Modal,
		ErrorItem,
		SuccessItem,
		NoActionTable
	},
	props: ["currentProcess"],
	data() {
		return {
			columns: [
			{ id: "p_name", value: this.$ml.with("VueJS").get("txtProject"), width: "", class: "" },
			{ id: "i_name", value: this.$ml.with("VueJS").get("txtIssue"), width: "", class: "" },
			{ id: "phase", value: this.$ml.with("VueJS").get("txtPhase"), width: "", class: "" },
			{ id: "page", value: this.$ml.with("VueJS").get("txtPage"), width: "", class: "" }
			],
			columns2: [
			{ id: "date", value: this.$ml.with('VueJS').get('lblDate'), width: "", class: "" },
			{ id: "name", value: this.$ml.with('VueJS').get('txtName'), width: "", class: "" },
			{ id: "message", value: this.$ml.with('VueJS').get('txtMessage'), width: "", class: "" },
			{ id: "box", value: 'Box', width: "", class: "text-center" }
			],
			dataProcess: [],
			newMessage: "",
			box: "test",
			file: "test",
			errors: "",
			success: "",
			modalLg: "modal-lg",
			userID: document.querySelector("meta[name='user-id']").getAttribute('content'),
			today: moment(new Date()).format('YYYY-MM-DD HH:mm'),
			listComments: []
		};
	},
	mounted() {
		let _this = this;
		this.getBoxFolder();
		$(document).on('click', '.languages button', function() {
			_this.columns = [
			{ id: "p_name", value: _this.$ml.with("VueJS").get("txtProject"), width: "", class: "" },
			{ id: "i_name", value: _this.$ml.with("VueJS").get("txtIssue"), width: "", class: "" },
			{ id: "phase", value: _this.$ml.with("VueJS").get("txtPhase"), width: "", class: "" },
			{ id: "page", value: _this.$ml.with("VueJS").get("txtPage"), width: "", class: "" }
			];

			_this.columns2 = [
			{ id: "date", value: _this.$ml.with('VueJS').get('lblDate'), width: "160", class: "" },
			{ id: "name", value: _this.$ml.with('VueJS').get('txtName'), width: "50", class: "" },
			{ id: "message", value: _this.$ml.with('VueJS').get('txtMessage'), width: "", class: "" },
			{ id: "box", value: 'Box', width: "", class: "text-center" }
			];
		});
	},
	methods: {
		recapName(str) {
			let words = str.split(" ");
			let firstName = words[words.length - 1];
			return firstName;
		},
		getComments() {
			if ( Object.keys(this.currentProcess).length != 0 ) {
				let uri = "/data/get-comments/" + this.currentProcess.issue_id + "/" + this.currentProcess.phase;
				axios
				.get(uri)
				.then(res => {
					this.listComments = res.data.comments.map((item, index) => {
						return {
							date: moment(item.date).format('DD/MMM/YYYY HH:mm'),
							name: this.recapName(item.name),
							message: item.message,
							box: item.box
						}
					});
					console.log(res.data.message);
				})
				.catch(err => {
					console.log(err);
				});
			}
		},
		getBoxFolder() {
			axios.get('https://account.box.com/api/oauth2/authorize?response_type=code&client_id=r3f3t4gyqu4yp46gs5bbh2nhl4n4i87g')
			.then(res => {
				console.log(res);
			})
			.catch(err => {
				console.log(err);
				alert("Could not load box");
			});
		},
		getDataProcess() {
			this.dataProcess = [
			{
				p_name: this.currentProcess.project,
				i_name: this.currentProcess.issue,
				phase: this.currentProcess.phase,
				page: this.currentProcess.page
			}
			];
		},
		changeStatus() {
			let uri = "/data/upload/update-status";
			axios
			.post(uri, {
				currentProcess: this.currentProcess
			})
			.then(res => {
				console.log(res.data.message);
			})
			.catch(err => {
				console.log(err);
			});
		},
		uploadProcess() {
			// Reset validate
			this.errors = "";
			this.success = "";

			if( !this.newMessage ) {
				this.errors = [
					['Message is requried']
				];
				return false;
			}

			if( !this.box ) {
				this.errors = [
					['Box is requried']
				];
				return false;
			}

			if( !this.file ) {
				this.errors = [
					['File is requried']
				];
				return false;
			}

			let newItem = {
				user_id: this.userID,
				issue_id: this.currentProcess.issue_id,
				schedule_id: this.currentProcess.id,
				date: this.today,
				message: this.newMessage,
				box: this.box
			}

			let uri = "/data/comments";
			axios
			.post(uri, newItem)
			.then(res => {
				this.success = res.data.message;
				let newComment = {
					date: moment(res.data.comment.date).format('DD/MMM/YYYY HH:mm'),
					name: this.recapName(res.data.comment.name),
					message: res.data.comment.message,
					box: res.data.comment.box
				}

				this.listComments = [...this.listComments, newComment];
			})
			.catch(err => {
				console.log(err);
			});
		},
        resetValidate() {
            this.errors = '';
            this.success = '';
            this.newMessage = '';
            this.$emit('reset-validation')
        }
	},
	watch: {
		currentProcess: [
		{
			handler: "getDataProcess"
		},
		{
			handler: "getComments"
		}
		]
	}
};
</script>
<style lang="scss">
#processModal {
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
	width: calc(50% - 7.5px);
	input {
	opacity: 0;
	position: absolute;
	z-index: -1;
}
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