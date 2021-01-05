export default {
	namespaced: true,

	state: {
		offDayTypes: [
			{
				id: "morning",
				name: "[AM] Half-day (8:00 - 12:00)",
				color: "#00AEEF",
			},
			{
				id: "afternoon",
				name: "[PM] Half-day (13:00 - 17:00)",
				color: "#FFDD00",
			},
			{
				id: "all_day",
				name: "Full-day (8:00 - 17:00)",
				color: "#F55555",
			},
		],
		allOffDays: [],
		offDays: [],
		currentEvent: {},
		currentStart: '',
		currentEnd: '',
		filters: {
			team: '',
			user_id: document.querySelector("meta[name='user-id']").getAttribute('content')
		}
	},

	getters: {
		offDayTypes: state => state.offDayTypes,
		allOffDays: state => state.allOffDays,
		filters: state => state.filters,
		offDays: state => state.offDays,
		currentEvent: state => state.currentEvent,
		currentStart: state => state.currentStart,
		currentEnd: state => state.currentEnd,
		recapName() {
			return (str) => {
				let words = str.split(" ");
				let firstName = words[words.length - 1],
					middleName = words[words.length - 2]
						? words[words.length - 2] + " "
						: "";
				return middleName + firstName;
			}
		},
		recapTime() {
			return (type, translateFunc) => {
				if (type == "all_day") {
					return "[" + translateFunc.get("txtRCFullDay") + "] ";
				}
				if (type == "morning") {
					return "[" + translateFunc.get("txtRCAM") + "] ";
				}
				if (type == "afternoon") {
					return "[" + translateFunc.get("txtRCPM") + "] ";
				}
			}
		},
	},

	mutations: {
		SET_ALL_OFF_DAYS: (state, data) => {
			state.allOffDays = data
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

		UPDATE_CURRENT_EVENT: (state, data) => {
			state.currentEvent = data
		},

		// DELETE_EVENT: (state, id) => {
		// 	state.offDays = state.offDays.filter((elem) => {
		// 		if (elem.id != id) return elem
		// 	});
		// },

		DELETE_EVENT: (state, id) => {
			state.allOffDays = state.allOffDays.filter((elem) => {
				if (elem.id != id) return elem
			});
		},

		ADD_OFF_DAY: (state, data) => {
			if (data.oldOffDays) {
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
		async getAllOffDays({ commit, state, rootGetters, getters, rootState }) {
			const uri = '/data/all-off-days?team_id=' + state.filters.team + '&startDate=' + rootGetters['dateFormat'](state.currentStart, 'YYYY-MM-DD') + '&endDate=' + rootGetters['dateFormat'](state.currentEnd, 'YYYY-MM-DD');

			await axios.get(uri)
				.then(res => {
					if (res.data.offDays.length) {
						res.data.offDays = res.data.offDays.map((item, index) => {
							let type = rootGetters['getObjectByID'](state.offDayTypes, item.type);

							return Object.assign({}, item, {
								title: getters['recapTime'](item.type, rootState.translateTexts) + getters['recapName'](item.name),
								borderColor: type.color,
								backgroundColor: type.color,
								start: rootGetters['dateFormat'](item.date),
								end: rootGetters['dateFormat'](item.date),
							})
						});
					}
					commit('SET_ALL_OFF_DAYS', res.data.offDays)
				})
				.catch(err => {
					console.log(err);
					alert("Could not load Off days");
				});
		},

		getOffDays({ commit, state, rootState, rootGetters, dispatch }) {
			// const uri = '/data/offdays?user_id=' + rootState.loginUser.id + '&startDate=' + rootGetters['dateFormat'](state.currentStart, 'YYYY-MM-DD') + '&endDate=' + rootGetters['dateFormat'](state.currentEnd, 'YYYY-MM-DD');

			const uri = '/data/all-off-days?team_id=' + state.filters.team + '&startDate=' + rootGetters['dateFormat'](state.currentStart, 'YYYY-MM-DD') + '&endDate=' + rootGetters['dateFormat'](state.currentEnd, 'YYYY-MM-DD');

			axios.get(uri)
				.then(res => {
					if (res.data.offDays.length) {
						res.data.offDays = res.data.offDays.map((item, index) => {
							return Object.assign({}, item, {
								title: rootGetters['getObjectByID'](state.offDayTypes, item.type).name,
								borderColor: rootGetters['getObjectByID'](state.offDayTypes, item.type).color,
								backgroundColor: rootGetters['getObjectByID'](state.offDayTypes, item.type).color,
								start: rootGetters['dateFormat'](item.date),
								end: rootGetters['dateFormat'](item.date)
							})
						})
					}
					commit('SET_OFF_DAYS', res.data.offDays)
				})
				.catch(err => {
					console.log(err);
					alert("Could not load Off days");
				});
		},

		setTeam({ commit }, data) {
			commit('SET_TEAM', data)
		},

		deleteEvent({ commit, rootState }, event) {
			const uri = '/data/offdays/' + event.id;
			const uriWithTeam = rootState.queryTeam ? uri + '?' + rootState.queryTeam : uri

			axios.delete(uriWithTeam).then((res) => {
				commit('DELETE_EVENT', event.id);
				$('#editEvent').modal('hide');
			}).catch(err => console.log(err));
		},

		clickEvent({ commit, rootGetters, state }, item) {
			const user_id = rootGetters['getObjectByID'](state.allOffDays, item.event.id * 1)['user_id'];
			if (user_id == state.filters.user_id) {
				commit('UPDATE_CURRENT_EVENT', item.event);
				$('#editEvent').modal('show');
			}
		},

		addEvent({ dispatch ,commit, state,rootState, rootGetters }, info) {
			const { event } = info;
			const { id, start, end, borderColor, backgroundColor, title } = event;
			const uri = '/data/offdays?user_id=' + rootState.loginUser.id;
			const uriWithTeam = rootState.queryTeam ? uri + '&' + rootState.queryTeam : uri

			const newItem = {
				user_id: this.userID,
				type: id,
				date: rootGetters['dateFormat'](start, 'YYYY-MM-DD'),
				start: start,
				end: end,
				borderColor: borderColor,
				backgroundColor: backgroundColor,
				title: title
			};

			axios.post(uriWithTeam, newItem)
				.then(res => {
					const data = {
						oldOffDays: res.data.oldEvent,
						newOffDay: res.data.event
					}
					commit('ADD_OFF_DAY', data)
					state.filters.team = '';
					info.event.remove();
					dispatch('getAllOffDays')
				})
				.catch(err => {
					console.log(err);
				});
		},

		printEvent({ }, event) {
			const uri = "/pdf/absence?id=" + event.id;
			axios.get(uri)
				.then(res => {
					window.open(res.data.file_name, "_blank");
				})
				.catch(err => {
					console.log(err);
					alert("Could not load data");
				});
		},

		handleMonthChange({ commit, dispatch }, arg) {
			commit('SET_CURRENT_START', arg.view.currentStart)
			commit('SET_CURRENT_END', arg.view.currentEnd)
			dispatch('getOffDays')
		},

		handleMonthChangeAll({ commit, dispatch }, arg) {
			commit('SET_CURRENT_START', arg.view.currentStart)
			commit('SET_CURRENT_END', arg.view.currentEnd)
			dispatch('getAllOffDays')
		}
	}
}
