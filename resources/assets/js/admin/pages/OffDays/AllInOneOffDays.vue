<template>
	<card>
		<template slot="header">
			<div class="d-flex justify-content-between">
				<h4 class="card-title">
					{{ $ml.with("VueJS").get("sbOffDays") }}
				</h4>
				<div
					class="form-group mb-0 d-flex justify-content-between"
					style="min-width: 100px"
				>
					<div style="min-width: 100px">
						<select-2
							:options="teamOptions"
							v-model="filters.team"
							class="select2"
						/>
					</div>
                    <div
						v-if="showTeamSelect"
						style="min-width: 240px"
						class="ml-3"
					>
						<multiselect
							:multiple="false"
							v-model="filters.user_id"
							:options="users"
							:clear-on-select="false"
							:preserve-search="true"
							:placeholder="$ml.with('VueJS').get('txtSelectOne')"
							label="text"
							track-by="text"
						></multiselect>
					</div>
				</div>
			</div>
		</template>
		<div class="row">
			<div class="col-sm-12 row--left">
				<card>
					<template slot="header">
						<h4 class="card-title">
                            {{ $ml.with("VueJS").get("txtStaffOffDay") }}
						</h4>
					</template>
					<div class="mb-3" id="external-events">
						<div id="external-events-list">
							<template v-for="(item, index) in offDayTypes">
								<div
									class="alert alert-success fc-event"
									v-if="checkTypeShow(item)"
									:data-type="item.id"
									:key="index"
									:color="item.color"
									:style="setBackground(item.color)"
                                    :class="{'mt-5': item.id == 'holiday'}"
								>
									<span
										><b>{{ item.name }}</b></span
									>
								</div>
							</template>
						</div>
					</div>
				</card>
			</div>
			<div class="col-sm-12 row--right">
				<FullCalendar
					class="off-days"
					defaultView="dayGridMonth"
					:plugins="calendarPlugins"
					:header="calendarHeader"
					:business-hours="businessHours"
					:editable="false"
					:droppable="false"
					:events="allOffDays"
					:all-day-slot="false"
					height="auto"
					:hidden-days="[0]"
					@eventReceive="addEvent"
					@eventClick="clickEvent"
					:locale="getLanguage(this.$ml)"
					:datesRender="handleMonthChangeAll"
				/>
			</div>
		</div>
		<edit-event />
	</card>
</template>

<script>
import FullCalendar from "@fullcalendar/vue";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timeGrid";
import interactionPlugin, { Draggable } from "@fullcalendar/interaction";
import listPlugin from "@fullcalendar/list";
import Datepicker from "vuejs-datepicker";
import Card from "../../components/Cards/Card";
import Select2 from "../../components/SelectTwo/SelectTwo.vue";
import Multiselect from "vue-multiselect";
import EditEvent from "./Edit";
import { mapGetters, mapActions } from "vuex";

export default {
	name: "all-in-one-off-days",
	components: {
		FullCalendar, // make the <FullCalendar> tag available
		Card,
		Select2,
		Multiselect,
		EditEvent,
		Datepicker
	},

	computed: {
		...mapGetters({
			setBackground: "setBackground",
			getLanguage: "getLanguage",
			loginUser: "loginUser",
			currentFullTeamOption: "currentFullTeamOption",
			currentTeam: "currentTeam",
			getLangCode: "getLangCode",
			dateFormat: "dateFormat"
		}),

		...mapGetters("offdays", {
			allOffDays: "allOffDays",
			offDayTypes: "offDayTypes",
			currentEvent: "currentEvent",
			filters: "filters",
			users: "users"
		}),

		showTeamSelect: function() {
            return this.filters.team != '0' &&
                !(
                    this.loginUser.role &&
                    -1 == [45].indexOf(this.loginUser.id) &&
                    -1 == ['admin'].indexOf(this.loginUser.role.name)
                )
        },

        teamOptions: function () {
            return [{ id: 0, text: "ALL" }, ...this.currentFullTeamOption]
        }
	},

	data() {
		return {
			calendarPlugins: [
				listPlugin,
				interactionPlugin,
				dayGridPlugin,
				timeGridPlugin
			],

			calendarHeader: {
				left: "prev",
				center: "title",
				right: "next"
			},

			businessHours: [
				{
					// days of week. an array of zero-based day of week integers (0=Sunday)
					daysOfWeek: [1, 2, 3, 4, 5] // Monday - Thursday
				},
				{
					// days of week. an array of zero-based day of week integers (0=Sunday)
					daysOfWeek: [6] // Monday - Thursday
				}
			]
		};
	},

	methods: {
		...mapActions({
			setCurrentTeam: "setCurrentTeam"
		}),

		...mapActions("offdays", {
			handleMonthChangeAll: "handleMonthChangeAll",
			getAllOffDays: "getAllOffDays",
			addEvent: "addEvent",
			clickEvent: "clickEvent",
			deleteEvent: "deleteEvent"
		}),

		disabledStartDates(date) {
			if (date) return { to: new Date(date) };
			return { from: new Date() };
		},

        checkTypeShow(item) {
            return !(
                item.permission &&
                this.loginUser.role &&
                -1 ==
                    item.permission.user_id.indexOf(
                        this.loginUser.id
                    ) &&
                -1 ==
                    item.permission.role.indexOf(
                        this.loginUser.role.name
                    )
            )
        },

		makeDraggable() {
			let draggableEl = document.getElementById("external-events-list");

			new Draggable(draggableEl, {
				itemSelector: ".fc-event",
				eventData: eventEl => {
					return {
						title: eventEl.innerText.trim(),
						id: eventEl.getAttribute("data-type"),
						borderColor: eventEl.getAttribute("color"),
						backgroundColor: eventEl.getAttribute("color")
					};
				}
			});
		}
	},

	mounted() {
		this.makeDraggable();
	},

	watch: {
		filters: [
			{
				handler: function(value) {
					if ("undefined" != typeof this.filters.team) {
						if (value.team != this.currentTeam.id) {
							this.filters.user_id = "";
							this.setCurrentTeam(value.team);
						}
                        this.getAllOffDays();
					}
				},
				deep: true
			}
		],
	}
};
</script>

<style lang="scss">
@import "~vue-multiselect/dist/vue-multiselect.min.css";
@import "~@fullcalendar/core/main.css";
@import "~@fullcalendar/daygrid/main.css";
@import "~@fullcalendar/timegrid/main.css";
@import "~@fullcalendar/list/main.css";

#offdays {
	.fc-time-grid .fc-event {
		padding: 5px;
	}

	@media (min-width: 992px) {
		.row {
			&--left {
				flex: 0 0 20%;
				max-width: 20%;
			}

			&--right {
				flex: 0 0 80%;
				max-width: 80%;
			}
		}
	}

	.fc-event {
		cursor: move;
		color: rgba(0, 0, 0, 0.8);
		min-height: 0;

		&.printed {
			position: relative;
			padding-right: 25px;

			&::after {
				content: "\f046";
				display: inline-block;
				font: normal normal normal 14px/1 FontAwesome;
				font-size: inherit;
				text-rendering: auto;
				-webkit-font-smoothing: antialiased;
				position: absolute;
				right: 5px;
				top: 50%;
				transform: translateY(-50%);
				margin-top: 1px;
			}
		}

		&.holiday,
		&.offday {
			pointer-events: none;
			display: flex;
			align-items: center;
			justify-content: center;
			height: 53px;
			font-weight: 600;
			text-transform: uppercase;

			&:after {
				content: "";
				position: absolute;
				top: -31px;
				z-index: -1;
				left: -3px;
				height: 120px;
				width: calc(100% + 6px);
			}
		}

		&.offday {
			&:after {
				background-color: #eaeaea;
			}
		}

		&.holiday {
			&:after {
				background-color: #ffd6fb;
			}
		}
	}

	.fc-time-grid-event .fc-time,
	.fc-time-grid-event .fc-title {
		color: rgba(0, 0, 0, 0.8);
	}

	.fc-time-grid .fc-slats td {
		height: 2em;
	}

	.fc-unthemed td.fc-today {
		background-color: transparent;
	}

	.fc .fc-view-container .fc-head .fc-today {
		background-color: #ffd05b;
	}

	.fc-unthemed th,
	.fc-unthemed td,
	.fc-unthemed thead,
	.fc-unthemed tbody,
	.fc-unthemed .fc-divider,
	.fc-unthemed .fc-row,
	.fc-unthemed .fc-content,
	.fc-unthemed .fc-popover,
	.fc-unthemed .fc-list-view,
	.fc-unthemed .fc-list-heading td {
		border-color: #b3aeae;
	}

	.off-days .fc-day-grid-event .fc-time {
		display: none;
	}
}
</style>
