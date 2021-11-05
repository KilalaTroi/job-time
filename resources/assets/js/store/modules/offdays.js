export default {
	namespaced: true,

	state: {
		offDayTypes: [
			{
				id: "morning",
				name: "[AM] Half-day (08:00 - 12:00)",
				text: "Half-day (08:00 - 12:00)",
				color: "#00AEEF",
			},
			{
				id: "afternoon",
				name: "[PM] Half-day (13:00 - 17:00)",
				text: "Half-day (13:00 - 17:00)",
				color: "#FFDD00",
			},
			{
				id: "all_day",
				name: "Full-day (08:00 - 17:00)",
				text: "Full-day (08:00 - 17:00)",
				color: "#F55555",
			},
			{
				id: "holiday",
				name: "Holiday",
				text: "Holiday",
				color: "#ffd6fb",
				permission: {
					role: 'admin',
					user_id: [45],
				}
			},
			{
				id: "offday",
				name: "Off day",
				text: "Off day",
				color: "#eaeaea",
				permission: {
					role: 'admin', 
					user_id: [45],
				}
			},
			{
				id: "special_day",
				name: "Special day",
				text: "Special day",
				color: "#eaeaea",
				permission: {
					role: 'admin',
					user_id: [45],
				}
			},
		],
		allOffDays: [],
		users: [],
		offDays: [],
		currentEvent: {},
		currentStart: '',
		currentEnd: '',
		filters: {
			team: 0,
			user_id: ''
		},
		selectedItem: {
			afternoon: '',
			all_day: '',
			morning: '',
			total: 0,
		},
	},

	getters: {
		offDayTypes: state => state.offDayTypes,
		allOffDays: state => state.allOffDays,
		users: state => state.users,
		filters: state => state.filters,
		offDays: state => state.offDays,
		selectedItem: state => state.selectedItem,
		currentEvent: state => state.currentEvent,
		currentStart: state => state.currentStart,
		currentEnd: state => state.currentEnd,
		recapName() {
			return (str) => {
				let words = str.split(" ");
				let firstName = words[words.length - 1],
					middleName = typeof(words[words.length - 2]) != "undefined"
						? words[words.length - 2] + " "
						: "";
				return middleName + firstName;
			}
		},
		recapTime() {
			return (type, translateFunc) => {
				const textType = {
					all_day: "[" + translateFunc.get("txtRCFullDay") + "] ",
					morning: "[" + translateFunc.get("txtRCAM") + "] ",
					afternoon: "[" + translateFunc.get("txtRCPM") + "] ",
					offday: translateFunc.get("txtRCOff"),
					holiday: translateFunc.get("txtRCHoliday"),
					special_day: "[" + translateFunc.get("txtRCSpecial") + "] ",
				}
				return textType[type];
			}
		},
	},

	mutations: {
		SET_ALL_OFF_DAYS: (state, data) => {
			state.allOffDays = data
		},

		SET_USERS: (state, data) => {
			state.users = data
		},

		SET_OFF_DAYS: (state, data) => {
			state.offDays = data
		},

		SET_CURRENT_START: (state, data) => {
			state.currentStart = data
		},

		SET_CURRENT_END: (state, data) => {
			state.currentEnd = data
		},

		SET_TEAM: (state, data) => {
			state.team = data
		},

		SET_SELECTED_ITEM: (state, data) => {
			state.selectedItem = Object.assign({}, data)
		},

		UPDATE_CURRENT_EVENT: (state, data) => {
			state.currentEvent = data 
		},

		DELETE_EVENT: (state, id) => {
			state.allOffDays = state.allOffDays.filter((elem) => {
				if (elem.id != id) return elem
			});
		},

		ADD_OFF_DAY: (state, data) => {
			if (data.oldOffDays.length > 0) {
				state.offDays = state.offDays.filter((elem) => {
					if (data.oldOffDays.indexOf(elem.id) === -1) {
						return elem
					}
				})
			}
			state.offDays = [...state.offDays, data.newOffDay]
		}
	},

	actions: {
		getAllOffDays({ commit, state, rootGetters, getters, rootState }) {
			const user_id = state.filters.user_id && typeof(state.filters.user_id.id) != 'undefined' ? 
							state.filters.user_id.id : 0;
			const uri = '/data/all-off-days?team_id=' + state.filters.team + '&user_id='+ user_id +'&startDate=' + rootGetters['dateFormat'](state.currentStart, 'YYYY-MM-DD') + '&endDate=' + rootGetters['dateFormat'](state.currentEnd, 'YYYY-MM-DD');

			axios.get(uri)
				.then(res => {
					if (res.data.offDays.length) {
						res.data.offDays = res.data.offDays.map((item, index) => {
							const type = rootGetters['getObjectByID'](state.offDayTypes, item.type);
							const name = (-1 == ('holiday, offday').indexOf(item.type)) ? getters['recapName'](item.name) : '';
							const reason = item.reason ? ' ' + item.reason + ' ' : '';
							const title = reason ? '[' + reason + '] ' : getters['recapTime'](item.type, rootState.translateTexts);
							return Object.assign({}, item, {
								title: title + name,
								className: ('printed' == item.status ? 'printed' : '') + ('holiday' == item.type ? 'holiday' : '') + ('offday' == item.type ? 'offday' : ''),
								borderColor: type.color,
								backgroundColor: type.color,
								start: rootGetters['dateFormat'](item.date),
								end: rootGetters['dateFormat'](item.date),
							})
						});
					}
					commit('SET_ALL_OFF_DAYS', res.data.offDays)
					commit('SET_USERS', res.data.users)
				})
				.catch(err => {
					console.log(err);
					alert("Could not load Off days");
				});
		},

		getAllOffDayWeek({ commit }, id) {
			const uri = '/data/all-off-day-week?id=' + id;

			axios.get(uri)
				.then(res => {
					commit('SET_SELECTED_ITEM', res.data);
				})
				.catch(err => {
					console.log(err);
					alert("Could not load Off days");
				});
		},

		deleteEvent({ commit, rootState }, event) {
			const uri = '/data/offdays/' + event.id;
			const uriWithTeam = rootState.queryTeam ? uri + '?' + rootState.queryTeam : uri

			axios.delete(uriWithTeam).then((res) => {
				commit('DELETE_EVENT', event.id);
				$('#editEvent').modal('hide');
			}).catch(err => console.log(err));
		},

		deleteSpecialEvent({ dispatch, rootGetters }, data) {
			const uri = '/data/delete-special-days';

			const postData = {
				user_id: data.currentEvent.extendedProps.user_id,
				start_date: data.currentEvent.start ? rootGetters['dateFormat'](data.currentEvent.start, 'YYYY-MM-DD') : '',
				end_date: data.repeatToDate ? rootGetters['dateFormat'](data.repeatToDate, 'YYYY-MM-DD') : '',
			};

			axios.post(uri, postData)
				.then(res => {
					dispatch('getAllOffDays');
					$('#editEvent').modal('hide');
				})
				.catch(err => {
					console.log(err);
				});
		},

		clickEvent({ commit, rootGetters, state, dispatch, rootState }, item) {
			const user_id = rootGetters['getObjectByID'](state.allOffDays, item.event.id * 1)['user_id'];
			if ( (user_id == rootState.loginUser.id || 1 == rootState.loginUser.role.id || -1 != [45].indexOf(rootState.loginUser.id) ) && -1 == ['holiday', 'offday'].indexOf(item.event.extendedProps.type)) {
				if ('morning' != item.event._def.extendedProps.type && 'special_day' != item.event._def.extendedProps.type) 
					dispatch('getAllOffDayWeek', item.event.id);
				else commit('SET_SELECTED_ITEM', { afternoon: '', all_day: '', morning: '', total: 0 });
				
				item.event.reason = item.event._def.extendedProps.reason;
				commit('UPDATE_CURRENT_EVENT', item.event);
				$('#editEvent').modal('show');
			}
		},

		addEvent({ dispatch, commit, state, rootState, rootGetters }, info) {
			const { event } = info;
			const { id, start, end, borderColor, backgroundColor, title } = event;
			const user_id = state.filters.user_id ? 
							state.filters.user_id.id : rootState.loginUser.id;
			const uri = '/data/offdays';

			const newItem = {
				user_id: user_id,
				type: id,
				date: rootGetters['dateFormat'](start, 'YYYY-MM-DD'),
				start: start,
				end: end,
				borderColor: borderColor,
				backgroundColor: backgroundColor,
				title: title
			};

			axios.post(uri, newItem)
				.then(res => {
					const data = {
						oldOffDays: res.data.oldEvent,
						newOffDay: res.data.event
					}

					info.event.remove();
					// commit('ADD_OFF_DAY', data)
					
					if (
						rootState.loginUser.team.id != state.filters.team && 
						rootState.loginUser.id == user_id
					) {
						state.filters.team = rootState.loginUser.team.id;
					} else {
						dispatch('getAllOffDays');
					}
				})
				.catch(err => {
					console.log(err);
				});
		},

		updateSpecialDays({ dispatch, rootGetters }, data) {
			const uri = '/data/update-special-days';

			const postData = {
				user_id: data.currentEvent.extendedProps.user_id,
				start_date: data.currentEvent.start ? rootGetters['dateFormat'](data.currentEvent.start, 'YYYY-MM-DD') : '',
				end_date: data.repeatToDate ? rootGetters['dateFormat'](data.repeatToDate, 'YYYY-MM-DD') : '',
				reason: data.currentEvent.reason,
			};

			axios.post(uri, postData)
				.then(res => {
					dispatch('getAllOffDays');
				})
				.catch(err => {
					console.log(err);
				});
		},

		printEvent({ dispatch }, event) {
			const uri = "/pdf/absence?id=" + event.id;
			axios.get(uri)
				.then(res => {
					window.open(res.data.file_name, "_blank");
					dispatch('getAllOffDays')
				})
				.catch(err => {
					console.log(err);
					alert("Could not load data");
				});
		},

		printEvents({ dispatch }, selectItem) {
			const uri = "/pdf/absence?total=" + selectItem.total + "&morning=" + selectItem.morning + "&afternoon=" + selectItem.afternoon + "&allDay=" + selectItem.all_day + "&date=" + selectItem.date + '&ids=' + selectItem.ids + '&user_id=' + selectItem.user_id;
			axios.get(uri)
				.then(res => {
					if (res.data.status == 1) window.open(res.data.file_name, "_blank");
					else alert('Error Print');
					dispatch('getAllOffDays');
				})
				.catch(err => {
					console.log(err);
					alert("Could not load data");
				});
		},

		handleMonthChangeAll({ commit, dispatch }, arg) {
			commit('SET_CURRENT_START', arg.view.currentStart)
			commit('SET_CURRENT_END', arg.view.currentEnd)
			dispatch('getAllOffDays')
		}
	}
}
