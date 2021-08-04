<template>
	<modal id="editEvent">
		<div v-if="currentEvent">
			<div class="form-group text-center">
				<h4 class="mb-1 mt-2">
					{{ multiplePrints ? currentEvent.extendedProps.name : currentEvent.title }}
				</h4>
				<h5 class="mt-1">
					{{ multiplePrints ? "" : this.customFormatter(currentEvent.start)
					}}
				</h5>
                <!-- Kiểm tra nếu special thì thêm repeat and reason -->
				<div v-if="isSpecialDay" class="special-content">
					<hr />
					<div class="form-group">
						<label>{{ 'Reason' }} </label>
						<input type="text" v-model="currentEvent.reason" class="form-control"/>
					</div>
          			<div class="form-group d-flex align-items-center">
        				<input id="ckc_1" type="checkbox" v-model="repeat" class="form-control mr-2" :style="{width: '25px'}" />
        				<label for="ckc_1" class="mb-0 mr-3">{{ 'Repeat to' }}</label>

						<datepicker
							v-if="repeat"
							input-class="form-control"
							:placeholder="$ml.with('VueJS').get('txtSelectDate')"
							v-model="repeatToDate"
							:disabled-dates="disabledStartDates(currentEvent.start)"
							:format="customFormatter"
							:language="getLangCode(this.$ml)"
						></datepicker>
						<button
							@click="updateSpecialDays({currentEvent, repeatToDate})"
							type="button"
							class="btn btn-primary ml-auto"
						>
							{{ $ml.with("VueJS").get("txtSave") }}
						</button>
      				</div>
				</div>
				<div v-if="showMultipleContent">
					<ul class="px-0" :style="{ listStyle: 'none' }">
						<li v-if="selectedItem.afternoon">
							{{ offDayTypes[1].text }}:
							{{ selectedItem.afternoon }}
						</li>
						<li v-if="selectedItem.all_day">
							{{ offDayTypes[2].text }}:
							{{ selectedItem.all_day }}
						</li>
						<li v-if="selectedItem.morning">
							{{ offDayTypes[0].text }}:
							{{ selectedItem.morning }}
						</li>
					</ul>
					<div
						class="font-weight-bold"
						:style="{ fontSize: '22px', color: 'red' }"
					>
						Total Of Days: {{ selectedItem.total }}
					</div>
				</div>
			</div>
			<hr />
			<div class="form-group text-right">
				<div
					class="d-inline-flex align-items-center"
					v-if="showCboxMultiple"
				>
					<input
						class="mr-2"
						v-model="multiplePrints"
						id="multiple_days"
						type="checkbox"
					/>
					<label
						class="mb-0"
						:style="{ position: 'relative', top: '1px' }"
						for="multiple_days"
						>Multiple Days</label
					>
				</div>
				<button
					v-if="showBtnPrint"
					type="button"
					@click="
						multiplePrints
							? printEvents(selectedItem)
							: printEvent(currentEvent)
					"
					class="btn btn-primary ml-3"
				>
					{{ $ml.with("VueJS").get("txtPrint") }}
				</button>
				<button
					v-if="showBtnDelete && !isSpecialDay"
					type="button"
					@click="deleteEvent(currentEvent)"
					class="btn btn-danger ml-3"
				>
					{{ $ml.with("VueJS").get("txtDelete") }}
				</button>
				<!-- Delete button for special day -->
				<button
					v-if="showBtnDelete && isSpecialDay"
					type="button"
					@click="deleteSpecialEvent({currentEvent, repeatToDate})"
					class="btn btn-danger ml-3"
				>
					{{ $ml.with("VueJS").get("txtDelete") }}
				</button>
			</div>
		</div>
	</modal>
</template>
<script>
import moment from "moment";
import Modal from "../../components/Modals/Modal";
import Datepicker from "vuejs-datepicker";
import { mapGetters, mapActions } from "vuex";

export default {
	name: "EditEvent",
	components: {
		Modal,
		Datepicker
	},
	computed: {
		...mapGetters({
			loginUser: "loginUser",
			getLangCode: "getLangCode",
			customFormatter: "customFormatter",
		}),
		...mapGetters("offdays", {
			currentEvent: "currentEvent",
			selectedItem: "selectedItem",
			offDayTypes: "offDayTypes",
		}),
		isSpecialDay: function() {
            return this.currentEvent.extendedProps && 'special_day' == this.currentEvent.extendedProps.type;
        },
		showBtnPrint: function() {
            return this.currentEvent.extendedProps && 'special_day' != this.currentEvent.extendedProps.type;
        },
        showBtnDelete: function() {
            return (this.currentEvent.extendedProps && 'approved' == this.currentEvent.extendedProps.status && 'special_day' != this.currentEvent.extendedProps.type) ||
                (this.loginUser.role && 1 == this.loginUser.role.id) || -1 != [45].indexOf(this.loginUser.id);
        },
        showMultipleContent: function() {
            return this.multiplePrints && this.currentEvent.extendedProps && 'morning' != this.currentEvent.extendedProps.type;
        },
        showCboxMultiple: function() {
            return this.multiplePrints &&
				this.currentEvent.extendedProps &&
				'morning' != this.currentEvent.extendedProps.type &&
				'special_day' != this.currentEvent.extendedProps.type
        }
	},
	data() {
		return {
			multiplePrints: false,
			repeat: false,
			repeatToDate: '',
		};
	},

	methods: {
		...mapActions("offdays", {
			deleteEvent: "deleteEvent",
			deleteSpecialEvent: "deleteSpecialEvent",
			printEvent: "printEvent",
			printEvents: "printEvents",
			resetSelectedItem: "resetSelectedItem",
			updateSpecialDays: "updateSpecialDays",
		}),

		disabledStartDates(start_date) {
			if (start_date) return { to: start_date };
	  	},
	},
	watch: {
		selectedItem: [
			{
				handler: function(value) {
					this.multiplePrints = false;
					if (value.total > 0 && value.ids.split(",").length > 2)
						this.multiplePrints = true;
				},
				deep: true
			}
		],
		repeat: function (value) {
			if ( !value ) this.repeatToDate = '';
		}
	}
};
</script>
