<template>
    <div class="modal fade" id="itemDetail">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div v-if="currentItem" class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Edit Project</h4>
                    <button type="button" class="btn btn-xs btn-danger ml-2" data-dismiss="modal">
                        <i aria-hidden="true" class="fa fa-times"></i>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <hr>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="">Clients</label>
                                <div>
                                    <Select2 :options="clientOptions" v-model="currentItem.client_id" class="select2">
                                        <option disabled value="0">Select one</option>
                                    </Select2>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="">Departments</label>
                                <div>
                                    <Select2 :options="departmentOptions" v-model="currentItem.dept_id" class="select2">
                                        <option disabled value="0">Select one</option>
                                    </Select2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="">Types</label>
                                <div>
                                    <Select2Type :options="typeOptions" v-model="currentItem.type_id" class="select2">
                                        <option disabled value="0">Select one</option>
                                    </Select2Type>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="">Is training?</label>
                                <input v-model="currentItem.is_training" type="checkbox" name="name_ja" class="form-control">
                            </div>
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="">Name</label>
                                <input v-model="currentItem.p_name" type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="">Name VI</label>
                                <input v-model="currentItem.p_name_vi" type="text" name="name_vi" class="form-control">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="">Name JA</label>
                                <input v-model="currentItem.p_name_ja" type="text" name="name_ja" class="form-control">
                            </div>
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="">Issue</label>
                                <input v-model="currentItem.i_name" type="text" name="issue" class="form-control">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="">Start date</label>
                                <datepicker
                                        name="startDate"
                                        input-class="form-control"
                                        placeholder="Select Date"
                                        v-model="currentItem.start_date"
                                        :format="customFormatter"
                                        :disabled-dates="disabledEndDates()">
                                </datepicker>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="">End date</label>
                                <datepicker
                                        name="endDate"
                                        input-class="form-control"
                                        placeholder="Select Date"
                                        v-model="currentItem.end_date"
                                        :format="customFormatter"
                                        :language="ja"
                                        :disabled-dates="disabledStartDates()">
                                </datepicker>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <button @click="$emit('update-item', currentItem)" type="button" class="btn btn-primary">
                            Update
                        </button>
                        <button type="button" class="btn btn-secondary ml-3" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Select2 from '../../components/SelectTwo/SelectTwo.vue'
    import Select2Type from '../../components/SelectTwo/SelectTwoType.vue'

    import Datepicker from 'vuejs-datepicker';
    import {en, ja} from 'vuejs-datepicker/dist/locale'
    import moment from 'moment'

    export default {
        name: 'EditItem',
        components: {
            Select2,
            Select2Type,
            datepicker: Datepicker
        },
        props: ['currentItem', 'clients', 'departments', 'types'],
        data() {
            return {
                clientOptions: [],
                departmentOptions: [],
                typeOptions: [],
                en: en,
                ja: ja,
            }
        },
        methods: {
            getDataClients(data) {
                if ( data.length ) {
                    let dataOptions = [];
                    let obj = {
                        id: 0,
                        text: "Select one"
                    };
                    dataOptions.push(obj);

                    for (let i = 0; i < data.length; i++) {
                        let obj = {
                            id: data[i].id,
                            text: data[i].text
                        };
                        dataOptions.push(obj);
                    }
                    this.clientOptions = dataOptions;
                }
            },
            getDataDepartments(data) {
                if ( data.length ) {
                    let dataOptions = [];
                    let obj = {
                        id: 0,
                        text: "Select one"
                    };
                    dataOptions.push(obj);

                    for (let i = 0; i < data.length; i++) {
                        let obj = {
                            id: data[i].id,
                            text: data[i].text
                        };
                        dataOptions.push(obj);
                    }
                    this.departmentOptions = dataOptions;
                }
            },
            getDataTypes(data) {
                if ( data.length ) {
                    let dataTypes = [];
                    let obj = {
                        id: 0,
                        text: '<div>Select one</div>'
                    };
                    dataTypes.push(obj);

                    for (let i = 0; i < data.length; i++) {
                        let obj = {
                            id: data[i].id,
                            text: '<div><span class="type-color" style="background: ' + data[i].value + '"></span>' + data[i].slug + '</div>'
                        };
                        dataTypes.push(obj);
                    }
                    this.typeOptions = dataTypes;
                }
            },
            customFormatter(date) {
                return moment(date).format('DD-MM-YYYY');
            },
            disabledStartDates() {
                if ( this.currentItem.start_date ) {
                    let obj = {
                        to: new Date(this.currentItem.start_date), // Disable all dates after specific date
                        // days: [0], // Disable Saturday's and Sunday's
                    };
                    return obj;
                }
            },
            disabledEndDates() {
                if ( this.currentItem.end_date ) {
                    let obj = {
                        from: new Date(this.currentItem.end_date), // Disable all dates after specific date
                        // days: [0], // Disable Saturday's and Sunday's
                    };
                    return obj;
                }
            }
        },
        watch: {
            clients: [{
                handler: 'getDataClients'
            }],
            departments: [{
                handler: 'getDataDepartments'
            }],
            types: [{
                handler: 'getDataTypes'
            }]
        }
    }
</script>