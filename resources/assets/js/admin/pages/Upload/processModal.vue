<template>
	<div>
		<modal id="processModal" :sizeClasses="modalLg" v-on:reset-validation="resetValidate">
			<template slot="title">{{$ml.with('VueJS').get('txtUpload')}}</template>
			<div v-if="currentProcess">
				<h5>{{$ml.with('VueJS').get('txtProjectIssue')}}</h5>
				<div class="table-responsive">
					<table-no-action class="table-hover table-striped" :columns="columns" :data="dataProcess"></table-no-action>
				</div>
				<div v-if="!sendSuccess" class="form-group">
					<h5>{{$ml.with('VueJS').get('txtMessage')}}</h5>
					<textarea v-model="newMessage" class="form-control" rows="5"></textarea>
				</div>
				<div v-if="!sendSuccess" id="selectDest" class="">
					<div class="form-group border p-3">
						<h5>{{$ml.with('VueJS').get('txtLineRoomName')}}</h5>
						<p v-if="currentProcess.room_name">{{ currentProcess.room_name }}</p>
					</div>
					<div class="form-group border p-3">
						<h5>{{$ml.with('VueJS').get('txtBoxUrl')}}</h5>
						<p>{{ currentProcess.box_url }} <a v-if="currentProcess.box_url" :href="currentProcess.box_url" target="_blank"><i class="fa fa-external-link"></i></a></p>
					</div>
					<div class="form-group border p-3">
						<h5>{{$ml.with('VueJS').get('txtBoxDestination')}}</h5>
						<div v-if="boxArr.length">
							<ul id="boxList" class="pl-0">
								<li v-for="(item, index) in boxArr" :key="index" class="d-flex align-items-center mt-2">
									<span style="white-space: nowrap;" class="mr-2">Box {{ index + 1 }}: </span>
									<input type="text" :value="item" v-on:keyup="updateValue(index)" class="form-control">
									<i class="fa fa-trash text-danger ml-3 btn-link" @click="delBox(index)"></i> <a class="ml-2" :href="item" target="_blank"><i class="fa fa-external-link"></i></a>
								</li>
							</ul>
						</div>
						<div class="d-flex justify-content-between align-items-center">
							<input v-model="boxItem" type="text" name="boxItem" class="form-control">
							<button type="button" class="btn btn-primary btn-sm" @click="addBox">Add</button>
						</div>
					</div>
				</div>
				<div v-if="!sendSuccess" class="form-group d-flex justify-content-between">
					<div></div>
					<button type="button" class="btn btn-primary" @click="uploadProcess">{{$ml.with('VueJS').get('txtSend')}}</button>
				</div>
				<error-item :errors="errors"></error-item>
				<success-item :success="success"></success-item>
				<div v-if="listComments.length">
					<hr />
					<div class="form-group">
						<h5>{{$ml.with('VueJS').get('txtProcessList')}}</h5>
						<div class="table-responsive">
							<table-no-action
							class="table-hover table-striped"
							:columns="columns2"
							:data="listComments"
							></table-no-action>
						</div>
					</div>
				</div>
				<div v-if="sendSuccess" class="form-group d-flex justify-content-center">
					<button type="button" class="btn btn-primary" @click="resetValidate">Close</button>
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
			{ id: "phase", value: this.$ml.with("VueJS").get("txtPhase"), width: "", class: "" },
			{ id: "page", value: this.$ml.with("VueJS").get("txtPage"), width: "", class: "" }
			],
			columns2: [
			{ id: "date", value: this.$ml.with('VueJS').get('lblDate'), width: "170", class: "" },
			{ id: "name", value: this.$ml.with('VueJS').get('txtName'), width: "", class: "" },
			{ id: "message", value: this.$ml.with('VueJS').get('txtMessage'), width: "", class: "message-content" },
			{ id: "box", value: 'Box', width: "200", class: "text-center" }
			],
			dataProcess: [],
			newMessage: "@%name%\n-----------------------------------------------\n案件名：%project%　%issue%\n担当：%userName%\n-----------------------------------------------\n【】\n出来上がったファイルをアップいたしました。\nご確認お願いいたします。\n%url%",
			boxArr: [],
			box: "",
			boxItem: "",
			errors: "",
			success: "",
			sendSuccess: false,
			modalLg: "modal-lg",
			userID: document.querySelector("meta[name='user-id']").getAttribute('content'),
            currentUser: {},
			today: moment(new Date()).format('YYYY-MM-DD HH:mm'),
			listComments: [],
			isLoading: false
		};
	},
	mounted() {
		let _this = this;
		_this.getUser();
		$(document).on('click', '.languages button', function() {
			_this.columns = [
			{ id: "p_name", value: _this.$ml.with("VueJS").get("txtProject"), width: "", class: "" },
			{ id: "i_name", value: _this.$ml.with("VueJS").get("txtIssue"), width: "", class: "" },
			{ id: "phase", value: _this.$ml.with("VueJS").get("txtPhase"), width: "", class: "" },
			{ id: "page", value: _this.$ml.with("VueJS").get("txtPage"), width: "", class: "" }
			];

			_this.columns2 = [
			{ id: "date", value: _this.$ml.with('VueJS').get('lblDate'), width: "170", class: "" },
			{ id: "name", value: _this.$ml.with('VueJS').get('txtName'), width: "50", class: "" },
			{ id: "message", value: _this.$ml.with('VueJS').get('txtMessage'), width: "", class: "message-content" },
			{ id: "box", value: 'Box', width: "200", class: "text-center" }
			];
		});
	},
	methods: {
		getUser() {
            let uri = '/data/users/' + this.userID;
            axios.get(uri).then((response) => {
                this.currentUser = response.data.user;
            });
        },
		sendMessageLineWork(content) {
			if ( this.currentProcess.room_id ) {
				let uri = "/data/upload/submit-message";
				axios.post(uri, {
						roomId: this.currentProcess.room_id,
						content: content,
					})
					.then(res => {
						if ( typeof(res.data.data.code) !== 'undefined' && res.data.data.code != 200 ) {
							setTimeout(function () { this.sendMessageLineWork() }.bind(this), 30000);
						} else {
							this.isLoading = false;
						}
					})
					.catch(err => {
						console.log(err);
					});
			}	
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
							message: item.message.replace(/\n/g, '<br>') + '<button class="show-more">Show <span class="more">more</span> <span class="less">less</span></button>',
							box: this.getBoxButtons(item.box),
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
		uploadProcess() {
			// Reset validate
			this.errors = "";
			this.success = "";
			this.isLoading = true;

			if( !this.box ) {
				this.errors = [
					['Box is requried']
				];
				this.isLoading = false;
				return false;
			}

			if( this.boxItem ) {
				this.errors = [
					['Please add or clear the Box URL']
				];
				this.isLoading = false;
				return false;
			}

			let boxUrl = this.box.replace(/, /g, '\n');

			console.log(boxUrl);

			let content = this.newMessage.replace("%project%", this.currentProcess.project);   
				content = content.replace("%issue%", this.currentProcess.issue);
				content = content.replace("%userName%", this.recapName(this.currentUser.name));
				content = content.replace("%url%", boxUrl);

			let newItem = {
				user_id: this.userID,
				issue_id: this.currentProcess.issue_id,
				schedule_id: this.currentProcess.id,
				date: this.today,
				message: content,
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
					message: content.replace(/\n/g, '<br>') + '<button class="show-more">Show <span class="more">more</span> <span class="less">less</span></button>',
					box: this.getBoxButtons(res.data.comment.box)
				}

				this.listComments = [...this.listComments, newComment];

				this.newMessage = "@%name%\n-----------------------------------------------\n案件名：%project%　%issue%\n担当：%userName%\n-----------------------------------------------\n【】\n出来上がったファイルをアップいたしました。\nご確認お願いいたします。\n%url%";
				this.box = '';
				this.sendSuccess = true;
			})
			.catch(err => {
				this.isLoading = false;
				console.log(err);
			});

			this.sendMessageLineWork(content);
		},
		addBox() {
			if ( !this.boxItem ) {
				this.errors = [
					['Please type Box URL']
				];
				return false;
			}

			if ( !this.validateUrl(this.boxItem) ) {
				this.errors = [
					['Box URL invalidate']
				];
				return false;
			}

			if ( this.box ) {
				this.box += ', ' + this.boxItem;
			} else {
				this.box = this.boxItem;
			};

			this.boxItem = "";
		},
		delBox(pt) {
			this.boxArr = this.boxArr.filter((item, index) => index !== pt);
			let box = "";
			if ( this.boxArr ) {
				this.boxArr.map((item, index) => {
					if ( box ) {
						box += ', ' + item;
					} else {
						box = item;;
					};
				});
			}
			this.box = box;
		},
		updateValue(index) {
			let val = $('#boxList').find('li:nth-child(' + (index + 1) + ')').find('input').val();
			this.boxArr[index] = val;
			this.boxArr = [...this.boxArr];

			let box = "";
			if ( this.boxArr ) {
				this.boxArr.map((item, index) => {
					if ( box ) {
						box += ', ' + item;
					} else {
						box = item;;
					};
				});
			}
			this.box = box;
		},
		getBoxArr(data) {
			if ( data ) {
				this.boxArr =  data.split(", ");
			} else {
				this.boxArr =  [];
			}
		},
		getBoxButtons(data) {
			let boxButtons = '';
			let boxArr =  data.split(", ");
			boxArr.map((item, index) => {
				boxButtons += '<a href="' + item + '" target="_blank" class="btn btn-secondary m-1">Box ' + (index + 1) + ' <i class="fa fa-external-link"></i></a>';
			});
			return boxButtons;
		},
		validateUrl(url) {
		    let regex = /^(http|https)/;
		    return url.match(regex);
		},
        resetValidate() {
        	$('#processModal').modal('hide');
            this.errors = '';
            this.success = '';
            this.newMessage = "@%name%\n-----------------------------------------------\n案件名：%project%　%issue%\n担当：%userName%\n-----------------------------------------------\n【】\n出来上がったファイルをアップいたしました。\nご確認お願いいたします。\n%url%";
            this.box = "";
            this.sendSuccess = false;
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
			handler: "getComments"
		}
		],
		box: [{
			handler: "getBoxArr"
		}]
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