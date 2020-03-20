<template>
  <modal id="processModal" :sizeClasses="modalLg" v-on:reset-validation="$emit('reset-validation')">
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
        <base-checkbox v-model="finishProcess">{{$ml.with('VueJS').get('txtFinish')}}</base-checkbox>
        <button type="button" class="btn btn-primary">{{$ml.with('VueJS').get('txtSend')}}</button>
      </div>
      <error-item :errors="errors"></error-item>
      <success-item :success="success"></success-item>
      <hr />
      <div class="form-group">
        <h5>{{$ml.with('VueJS').get('txtProcessList')}}</h5>
        <div class="table-responsive">
          <no-action-table
            class="table-hover table-striped"
            :columns="columns2"
            :data="dataProcessList"
          ></no-action-table>
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
			dataProcessList: [],
			newMessage: "",
			errors: "",
			success: "",
			finishProcess: false,
			modalLg: "modal-lg"
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
				{ id: "date", value: _this.$ml.with('VueJS').get('lblDate'), width: "", class: "" },
				{ id: "name", value: _this.$ml.with('VueJS').get('txtName'), width: "", class: "" },
				{ id: "message", value: _this.$ml.with('VueJS').get('txtMessage'), width: "", class: "" },
				{ id: "box", value: 'Box', width: "", class: "text-center" }
			];
		});
	},
	methods: {
		getBoxFolder() {
			axios.get('https://api.box.com/2.0/folders/107571331091', {
				headers: {
					'As-User': '3475118616',
			    	'Authorization': 'Bearer JY1DtLyygVaPQVA6A8lTIz934sFIO92T',
			  	}
			}).then(res => {
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
		getDataProcessList() {
			this.dataProcessList = [
			{
				date: "2020/Jan/14",
				name: "Lợi",
				message: "20, 21, 30 page",
				box: '<a type="button" class="btn btn-second" target="_blank" href="#">SHOW</a>'
			},
			{
				date: "2020/Jan/14",
				name: "Ngọc",
				message: "22, 23, 24 page",
				box: '<a type="button" class="btn btn-second" target="_blank" href="#">SHOW</a>'
			}
			];
		}
	},
	watch: {
		currentProcess: [
		{
			handler: "getDataProcessList"
		},
		{
			handler: "getDataProcess"
		}
		]
	}
};
</script>
<style lang="scss">
#processModal {
	.modal-dialog {
		max-height: calc(100vh - 100px);
		.modal-content {
			overflow-y: auto;
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