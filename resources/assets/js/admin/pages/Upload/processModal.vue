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
						{{$ml.with('VueJS').get('txtBoxUrl')}}
						<label class="dest-box"><a :href="currentProcess.box_url" target="_blank">Open</a></label>
					</h5>
					<p>{{ currentProcess.box_url }}</p>
				</div>
				<div class="form-group border p-3">
					<h5>{{$ml.with('VueJS').get('txtBoxDestination')}}</h5>
					<input v-model="box" type="text" name="box" class="form-control">
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
			box: "",
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
		sendMessageLineWork() {
			axios.post('https://cors-anywhere.herokuapp.com/https://apis.worksmobile.com/jp1YSSqsNgFBe/message/sendMessage/v2', {
					"botNo": 763699,
					"roomId": this.currentProcess.room_id,
					"content": {
						"type": "text",
						"text": this.newMessage
					}
				}, {
				headers: {
					'Access-Control-Allow-Origin': '*',
					'Content-Type': 'application/json',
    				'consumerKey': 'dcn0NgAygjFgGVL584hJ',
    				'Authorization': 'Bearer AAAA+ORRLyUp2fmvNLT6LEfbU9D6ZtBOuPIs6B1WdPu9m4meKf38uz3wm1N8De0KsisHGaMULdu0S/VbODus9njxTrirJPSImVEFLoCS7Utu9+v7hJPWmVJICM9HUEtIaSzF85txqsZ7O5VTacvzLeCJBmVmDD3lIcZ1TqeC3O+DSULXVRJxzsazKmOyL0alCvKopN0n4pjsEe/ZBQmFKgFPxi1jEBS3Q58GG+7Zn9He00WL4GL8wI5Ki1aBnyG9mp4H44SUT0C/igjBsO351qKuqvccI+B117leYfQhYzRNe4v71vJ/7R1RztLpjKEWQLZwGQMUjrk5ysgshWYdaBZT670='
				}
			})
			.then(res => {
				console.log(res);
			})
			.catch(err => {
				console.log(err);
				alert("Could not load box");
			});
		},
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
							box: '<a href="' + item.box + '" target="_blank" class="btn btn-secondary">Open</a>',
						}
					});
					console.log(res.data.message);
				})
				.catch(err => {
					console.log(err);
				});
			}
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

				this.newMessage = '';
				this.box = '';
			})
			.catch(err => {
				console.log(err);
			});

			this.sendMessageLineWork();
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