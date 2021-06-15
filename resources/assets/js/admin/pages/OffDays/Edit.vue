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
                <!-- Kiểm tra nếu special thì thêm reepeat and reson -->
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
                <!-- Kiểm tra nếu special và admin thì thêm button update -->
				<button
					v-if="showBtnDelete"
					type="button"
					@click="deleteEvent(currentEvent)"
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
import { mapGetters, mapActions } from "vuex";

export default {
	name: "EditEvent",
	components: {
		Modal
	},
	computed: {
		...mapGetters({
			loginUser: "loginUser"
		}),
		...mapGetters("offdays", {
			currentEvent: "currentEvent",
			selectedItem: "selectedItem",
			offDayTypes: "offDayTypes"
		}),
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
			multiplePrints: false
		};
	},

	methods: {
		...mapActions("offdays", {
			deleteEvent: "deleteEvent",
			printEvent: "printEvent",
			printEvents: "printEvents",
			resetSelectedItem: "resetSelectedItem"
		}),

		customFormatter(date) {
			return moment(date).format("DD-MM-YYYY") !== "Invalid date"
				? moment(date).format("DD MMM YYYY")
				: "";
		}
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
		]
	}
};
</script>
