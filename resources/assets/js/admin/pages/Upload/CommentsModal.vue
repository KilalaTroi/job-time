<template>
	<modal id="commentsModal" :sizeClasses="modalLg" v-on:reset-validation="resetValidate">
		<card class="strpied-tabled-with-hover">
            <template slot="header">
                <div class="d-flex justify-content-between">
		            <h4 class="card-title">{{$ml.with('VueJS').get('txtProcessList')}}</h4>
					<base-checkbox v-model="currentProcess.status" v-on:input="changeStatus">{{$ml.with('VueJS').get('txtFinish')}}</base-checkbox>
				</div>
            </template>
            <div v-if="currentProcess">
				<div v-if="listComments.length">
					<div class="table-responsive">
						<no-action-table
						class="table-hover table-striped"
						:columns="columns2"
						:data="listComments"
						></no-action-table>
					</div>
				</div> 
			</div>
        </card>
	</modal>
</template>

<script>
import NoActionTable from "../../components/TableNoAction";
import Modal from "../../components/Modals/Modal";
import moment from 'moment';

export default {
	name: "comments-modal",
	components: {
		Modal,
		NoActionTable
	},
	props: ["currentProcess"],
	data() {
		return {
			columns2: [
			{ id: "date", value: this.$ml.with('VueJS').get('lblDate'), width: "", class: "" },
			{ id: "name", value: this.$ml.with('VueJS').get('txtName'), width: "", class: "" },
			{ id: "message", value: this.$ml.with('VueJS').get('txtMessage'), width: "", class: "" },
			{ id: "box", value: 'Box', width: "", class: "text-center" }
			],
			modalLg: "modal-lg",
			listComments: []
		};
	},
	mounted() {
		let _this = this;
		$(document).on('click', '.languages button', function() {
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
        resetValidate() {
            this.$emit('reset-validation')
        }
	},
	watch: {
		currentProcess: [
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