<template>
    <div class="modal fade" id="issueCreate">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-light">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Add new issue</h4>
                    <button type="button" class="btn btn-xs btn-danger ml-2" data-dismiss="modal">
                        <i aria-hidden="true" class="fa fa-times"></i>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <hr>
                    <form @submit="emitAddItem">
                        <div class="form-group">
                            <label class="">Project</label>
                            <div>
                                <Select2 :options="projectOptions" v-model="project_id" class="select2">
                                    <option disabled value="0">Select one</option>
                                </Select2>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="">Issue name</label>
                            <input v-model="i_name" type="text" name="i_name" class="form-control">
                        </div>

                        <div class="form-group">
                            <label class="">Start date</label>
                            <datepicker
                                    name="startDate"
                                    input-class="form-control"
                                    placeholder="Select Date"
                                    v-model="start_date"
                                    :format="customFormatter"
                                    :disabled-dates="disabledEndDates()">
                            </datepicker>
                        </div>

                        <div class="form-group">
                            <label class="">End date</label>
                            <datepicker
                                    name="endDate"
                                    input-class="form-control"
                                    placeholder="Select Date"
                                    v-model="end_date"
                                    :format="customFormatter"
                                    :language="ja"
                                    :disabled-dates="disabledStartDates()">
                            </datepicker>
                        </div>
                        <hr>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Add</button>
                            <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Select2 from '../../components/SelectTwo/SelectTwo.vue'
    import Datepicker from 'vuejs-datepicker';
    import {en, ja} from 'vuejs-datepicker/dist/locale'
    import moment from 'moment'

    export default {
        name: 'AddIssue',
        components: {
            Select2,
            datepicker: Datepicker
        },
        props: ['projects'],
        data() {
            return {
                project_id: 0,
                i_name: '',
                start_date: '',
                end_date: '',
                en: en,
                ja: ja,
                projectOptions: [],
            }
        },
        mounted() {
        },
        methods: {
            getDataProjects(data) {
                if ( data.length ) {
                    let dataOptions = [];
                    let obj = {
                        id: 0,
                        text: "Select one"
                    };
                    dataOptions.push(obj);

                    for (let i = 0; i < data.length; i++) {
                        let objCheck = dataOptions.filter(function (elem) {
                            if (elem.id == data[i].id) return elem;
                        });
                        if ( !(objCheck.length > 0) ) {
                            let obj = {
                                id: data[i].id,
                                text: data[i].p_name
                            };
                            dataOptions.push(obj);
                        }
                    }
                    this.projectOptions = dataOptions;
                }
            },
            emitAddItem(e) {
                e.preventDefault()

                const newIssue = {
                    project_id: this.project_id,
                    i_name: this.i_name,
                    start_date: this.start_date,
                    end_date: this.end_date
                };

                this.$emit('add-issue', newIssue);

                // Reset
                this.project_id = 0;
                this.i_name = '';
                this.start_date = '';
                this.end_date = '';
            },
            customFormatter(date) {
                return moment(date).format('DD-MM-YYYY');
            },
            disabledStartDates() {
                let obj = {
                    to: new Date(this.start_date), // Disable all dates after specific date
                    // days: [0], // Disable Saturday's and Sunday's
                };
                return obj;
            },
            disabledEndDates() {
                let obj = {
                    from: new Date(this.end_date), // Disable all dates after specific date
                    // days: [0], // Disable Saturday's and Sunday's
                };
                return obj;
            }
        },
        watch: {
            projects: [{
                handler: 'getDataProjects'
            }],
            start_date: [{
                handler: 'disabledStartDates'
            }],
            end_date: [{
                handler: 'disabledEndDates'
            }]
        }
    }
</script>
<style lang="scss">
    input[type="radio"], input[type="checkbox"] {
        &.form-control {
            width: 40px;
        }
    }
</style>