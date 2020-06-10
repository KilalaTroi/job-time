<template>
    <div>
        <div class="form-group">
            <label class="">Title</label>
            <input v-model="title" type="text" class="form-control">
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="form-group">
                    <label>Date</label>
                    <datepicker
                    name="date"
                    input-class="form-control"
                    placeholder=""
                    v-model="date"
                    :format="customFormatter"
                    :disabled-dates="disabledEndDates()"
                    :language="getLanguage(this.$ml)"
                    ></datepicker>
                </div>
            </div>
            <div class="col-sm-9">
                <div class="form-group">
                    <label class>{{$ml.with('VueJS').get('txtUsers')}}</label>
                    <div>
                        <multiselect
                        :multiple="true"
                        v-model="user_id"
                        :options="userOptions"
                        :clear-on-select="false"
                        :preserve-search="true"
                        :placeholder="$ml.with('VueJS').get('txtPickSome')"
                        label="text"
                        track-by="text"
                        :preselect-first="true"
                        ></multiselect>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Datepicker from "vuejs-datepicker";
import { vi, ja, en } from "vuejs-datepicker/dist/locale";
import moment from "moment";

export default {
    name: 'add-new',
    components: {
        Datepicker
    },
    data() {
        return {
            title: '',
            date: '',
            dataLang: {
                vi: vi,
                ja: ja,
                en: en
            }
        }
    },
    methods: {
        getLanguage(data) {
            return this.dataLang[data.current]
        },
        disabledEndDates() {
            let obj = {
                from: new Date() // Disable all dates after specific date
                // days: [0], // Disable Saturday's and Sunday's
            };
            return obj;
        },
        customFormatter(date) {
			return moment(date).format("YYYY/MM/DD");
		},
    },
}
</script>