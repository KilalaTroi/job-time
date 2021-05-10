<template>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <stats-card>
                        <div slot="header" class="icon-warning">
                            <i class="nc-icon nc-chart text-warning"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">{{$ml.with('VueJS').get('txtWorkedTime')}}</p>
                            <h4 class="card-title">{{ totalArrayObject(hoursPerProject) | numeral('0,0') }}/{{ totalObject(totalHoursPerMonth) | numeral('0,0') }} {{$ml.with('VueJS').get('txtHour')}}</h4>
                        </div>
                        <div slot="footer">
                            <i class="fa fa-calendar-o mr-1"></i>{{ customFormatterStr(startMonth) }} - {{ customFormatterEnd(endMonth) }}
                        </div>
                    </stats-card>
                </div>
                <div class="col-xl-3 col-md-6">
                    <stats-card>
                        <div slot="header" class="icon-success">
                            <i class="nc-icon nc-light-3 text-success"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">{{$ml.with('VueJS').get('txtWorkingTime')}}</p>
                            <h4 class="card-title" v-if="getCurrentMonth(currentMonth)"> {{ getCurrentMonth(currentMonth).hours | numeral('0,0') }}/{{ getCurrentMonth(currentMonth).total | numeral('0,0') }} {{$ml.with('VueJS').get('txtHour')}}</h4>
                        </div>
                        <div slot="footer">
                            <i class="fa fa-calendar-o mr-1"></i>{{ currentMonth.startDate }} - {{ currentMonth.currentDate }}
                        </div>
                    </stats-card>
                </div>
                <div class="col-xl-3 col-md-6">
                    <stats-card>
                        <div slot="header" class="icon-danger">
                            <i class="nc-icon nc-vector text-danger"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">{{$ml.with('VueJS').get('txtCurrentJobs')}}</p>
                            <h4 class="card-title">{{ jobs }}</h4>
                        </div>
                        <div slot="footer">
                            <i class="fa fa-calendar-o mr-1"></i>{{ currentMonth.currentDate }}
                        </div>
                    </stats-card>
                </div>
                <div class="col-xl-3 col-md-6">
                    <stats-card>
                        <div slot="header" class="icon-info">
                            <i class="nc-icon nc-circle-09 text-primary"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">{{$ml.with('VueJS').get('txtNewUser')}}</p>
                            <h4 class="card-title">+{{ totalObject(newUsersPerMonth) }}/{{ currentMonth.totalUsers }}</h4>
                        </div>
                        <div slot="footer">
                            <i class="fa fa-calendar-o mr-1"></i>{{ customFormatterStr(startMonth) }} - {{ customFormatterEnd(endMonth) }}
                        </div>
                    </stats-card>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex mb-3 justify-content-between">
                        <div class="d-flex align-items-center flex-wrap">
                            <datepicker name="startMonth" input-class="form-control" v-model="startMonth" :format="customFormatterM" :minimumView="'day'" :maximumView="'year'" :initialView="'month'" :disabled-dates="disabledEndMonth()" :language="getLanguage(this.$ml)">
                            </datepicker>
                            <span class="mx-2">-</span>
                            <datepicker name="endMonth" input-class="form-control" v-model="endMonth" :format="customFormatterM" :minimumView="'day'" :maximumView="'year'" :initialView="'month'" :disabled-dates="disabledStartMonth()" :language="getLanguage(this.$ml)">
                            </datepicker>
                        </div>
                        <div>
                            <div class="d-flex align-items-center">
                                <div class="d-flex align-items-center mr-3" style="min-width: 100px;">
                                    <label class="mr-2">{{$ml.with('VueJS').get('txtTeam')}}</label>
                                    <div>
                                        <select-2 :options="currentTeamOption" v-model="team" class="select2" />
                                    </div>
                                </div>
                                <div style="width: 200px;">
                                    <select2 :options="userOptions" v-model="user_id" class="select2 form-control no-disable-first-value">
                                        <option disabled value="0">All</option>
                                    </select2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link active" id="timeallocation-tab" data-toggle="tab" href="#timeallocation" role="tab" aria-controls="timeallocation" aria-selected="true">{{$ml.with('VueJS').get('txtTimeAllocation')}}</a>
                            <a v-if="3 != team" @click="showTotalPages" class="nav-item nav-link " id="totalpage-tab" data-toggle="tab" href="#totalpage" role="tab" aria-controls="totalpage" aria-selected="false">{{$ml.with('VueJS').get('txtTotalPages')}}</a>
                            <a v-if="3 == team" @click="showTotalProjects" class="nav-item nav-link " id="totalproject-tab" data-toggle="tab" href="#totalprojects" role="tab" aria-controls="totalprojects" aria-selected="false">{{$ml.with('VueJS').get('txtTotalProjects')}}</a>
                            <a v-if="3 == team" @click="showTotalJobs" class="nav-item nav-link " id="totaljobs-tab" data-toggle="tab" href="#totaljobs" role="tab" aria-controls="totaljobs" aria-selected="false">{{$ml.with('VueJS').get('txtTotalJobs')}}</a>
                            <a v-if="2 == team" @click="showTotalPages" class="nav-item nav-link" id="table-tab" data-toggle="tab" href="#table" role="tab" aria-controls="table" aria-selected="false">{{$ml.with('VueJS').get('txtTable')}}</a>
                        </div>
                    </nav>
                     <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="timeallocation" role="tabpanel" aria-labelledby="timeallocation-tab">
                            <div class="row">
                                <div class="col-md-12" v-if="barChart.data">
                                    <chart-card :chart-data="barChart.data" :chart-options="barChart.options" :chart-responsive-options="barChart.responsiveOptions" chart-type="Bar" :chart-id="barChart.id" v-on:chart-loaded="chartLoaded">
                                        <template slot="footer">
                                            <div class="legend">
                                                <span v-for="(type, index) in types" :key="index" :class="circleClass(type.class)"><i class="fa fa-circle ct-legend"></i>{{ 'ja' == currentLang ? type.slug_ja : type.slug_vi }}</span>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="export col-auto ml-auto">
                                                    <a :href="exportLink"><i class="fa fa-download"></i> {{$ml.with('VueJS').get('txtExportExcel')}}</a>
                                                </div>
                                            </div>
                                        </template>
                                    </chart-card>
                                </div>
                            </div>
                        </div>
                        <div v-if="3 != team" class="tab-pane fade" id="totalpage" role="tabpanel" aria-labelledby="totalpage-tab">
                            <div class="row">
                                <div class="col-md-12" v-if="pageChart.data">
                                    <chart-card :chart-data="pageChart.data" :chart-options="pageChart.options" chart-type="Bar" :chart-id="pageChart.id" v-on:chart-loaded="chartLoaded">
                                        <template slot="footer">
                                            <div class="legend loading">
                                                <span v-for="(type, index) in types" :key="index" :class="circleClass(type.class) + (type.slug == 'other' || type.slug == 'yuidea_other' ? ' d-none' : '') "><i class="fa fa-circle ct-legend"></i>{{ 'ja' == currentLang ? type.slug_ja : type.slug_vi }}</span>
                                            </div>
                                        </template>
                                    </chart-card>
                                </div>
                            </div>
                        </div>
                        <div v-if="3 == team" class="tab-pane fade" id="totalprojects" role="tabpanel" aria-labelledby="totalprojects-tab">
                            <div class="row">
                                <div class="col-md-12" v-if="projectChart.data">
                                    <chart-card :chart-data="projectChart.data" :chart-options="projectChart.options" chart-type="Bar" :chart-id="projectChart.id" v-on:chart-loaded="chartLoaded">
                                        <template slot="footer">
                                            <div class="legend loading">
                                                <span v-for="(type, index) in types" :key="index" :class="circleClass(type.class)"><i class="fa fa-circle ct-legend"></i>{{ 'ja' == currentLang ? type.slug_ja : type.slug_vi }}</span>
                                            </div>
                                        </template>
                                    </chart-card>
                                </div>
                            </div>
                        </div>
                        <div v-if="3 == team" class="tab-pane fade" id="totaljobs" role="tabpanel" aria-labelledby="totaljobs-tab">
                            <div class="row">
                                <div class="col-md-12" v-if="jobChart.data">
                                    <chart-card :chart-data="jobChart.data" :chart-options="jobChart.options" chart-type="Bar" :chart-id="jobChart.id" v-on:chart-loaded="chartLoaded">
                                        <template slot="footer">
                                            <div class="legend loading">
                                                <span v-for="(type, index) in types" :key="index" :class="circleClass(type.class)"><i class="fa fa-circle ct-legend"></i>{{ 'ja' == currentLang ? type.slug_ja : type.slug_vi }}</span>
                                            </div>
                                        </template>
                                    </chart-card>
                                </div>
                            </div>
                        </div>
                        <div v-if="2 == team" class="tab-pane fade" :class="checkUser() ? 'flag' : ''" id="table" role="tabpanel" aria-labelledby="table-tab">
                            <div class="row mt-3">
                                <button v-if="checkUser()" type="button" class="btn btn-primary" data-toggle="modal" data-target="#totalpageAction" data-backdrop="static" data-keyboard="false">
                                    Update
                                    <slot name="title"></slot>
                                </button>
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <th></th>
                                            <th class="text-center" v-for="(month, index) in data.totalpage.monthYearText" :key="index">{{ month }}</th>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(type, index) in types" :key="index" :class="type.class + (type.slug == 'other' || type.slug == 'yuidea_other' ? ' d-none' : '')">
                                                <td>{{ 'ja' == currentLang ? type.slug_ja : type.slug_vi }}</td>
                                                <td class="text-center" v-for="(month, indexMonth) in data.totalpage.monthYearText" :key="indexMonth">{{ data.totalpage.table[type.id+'_'+indexMonth] ? data.totalpage.table[type.id+'_'+indexMonth].page : 0 }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <total-update v-if="checkUser()" />
        </div>
    </div>
</template>
<script>
    import ChartCard from '../components/Cards/ChartCard.vue'
    import StatsCard from '../components/Cards/StatsCard.vue'
    import LTable from '../components/Table.vue'
    import Chartist from 'chartist'
    import chartistPluginTooltip from 'chartist-plugin-tooltip'
    import Datepicker from 'vuejs-datepicker';
    import { vi, ja, en } from 'vuejs-datepicker/dist/locale'
    import Select2 from '../components/SelectTwo/SelectTwo.vue'
    import moment from 'moment'
    import TotalUpdate from './TotalPage/Update'
    import { mapGetters, mapActions } from "vuex"

    export default {
        components: {
            LTable,
            ChartCard,
            datepicker: Datepicker,
            StatsCard,
            Select2,
            TotalUpdate
        },
        computed: {
            ...mapGetters({
                currentTeamOption: 'currentTeamOption',
                currentTeam: 'currentTeam',
                currentLang: 'currentLang',
                loginUser: 'loginUser',
            }),
        },
        data() {
            return {
                exportLink: '',
                types: [],
                monthsText: [],
                series: [],
                newUsersPerMonth: {},
                totalHoursPerMonth: {},
                hoursPerProject: [],
                pageData: {
                    'totalpage' : [],
                    'table' : [],
                },
                jobsData: [],
                projectsData: [],
                jobs: 0,

                startMonth: new Date(moment().subtract(11, 'months').startOf('month').format('YYYY/MM/DD')),
                endMonth: new Date(moment().subtract(0, 'months').endOf('month').format('YYYY/MM/DD')),
                currentMonth: {},

                users: [],
                userOptions: [],
                user_id: 0,

                barChart: {
                    id: 'time-allocation',
                    data: {
                        labels: [],
                        series: []
                    },
                    options: {
                        seriesBarDistance: 30,
                        stackBars: true,
                        axisX: {
                            showGrid: true
                        },
                        axisY: {
                            offset: 40,
                            labelInterpolationFnc: (value) => {
                                return value + '%'
                            },
                            scaleMinSpace: 40,
                            high: 100,
                            low: 0
                        },
                        height: '360px',
                        plugins: [
                            Chartist.plugins.tooltip()
                        ]
                    },
                    responsiveOptions: [
                        ['screen and (max-width: 640px)', {
                            seriesBarDistance: 5,
                            axisX: {
                                labelInterpolationFnc(value) {
                                    return value[0]
                                }
                            }
                        }]
                    ]
                },

                data: {
                    'totalpage' : []
                },

                pageChart: {
                    id: 'page-chart',
                    data: {
                        labels: [],
                        series: []
                    },
                    options: {
                        seriesBarDistance: 30,
                        stackBars: true,
                        axisX: {
                            showGrid: true
                        },
                        height: '360px',
                        plugins: [
                            Chartist.plugins.tooltip()
                        ]
                    }
                },

                jobChart: {
                    id: 'job-chart',
                    data: {
                        labels: [],
                        series: []
                    },
                    options: {
                        seriesBarDistance: 30,
                        stackBars: true,
                        axisX: {
                            showGrid: true
                        },
                        height: '360px',
                        plugins: [
                            Chartist.plugins.tooltip()
                        ]
                    }
                },

                projectChart: {
                    id: 'project-chart',
                    data: {
                        labels: [],
                        series: []
                    },
                    options: {
                        seriesBarDistance: 30,
                        stackBars: true,
                        axisX: {
                            showGrid: true
                        },
                        height: '360px',
                        plugins: [
                            Chartist.plugins.tooltip()
                        ]
                    }
                },

                dataLang: {
                    vi: vi,
                    ja: ja,
                    en: en
                },

                team: "",
            }
        },
        mounted() {
            let _this = this;
            _this.team = _this.currentTeam ? _this.currentTeam.id : ""

            $(document).on('mouseenter', '#time-allocation .ct-bar', function() {
                var seriesDesc = $(this).attr('ct:meta'),
                value = $(this).attr('ct:value');
                $('.ct-tooltip').html('<span>' + seriesDesc + '</span><br><span>' + value + "%</span>");
            });

            $(document).on('mouseenter', '#page-chart .ct-bar', function() {
                var seriesDesc = $(this).attr('ct:meta'),
                value = $(this).attr('ct:value');
                var pages = value > 1 ? 'pages' : 'page';
                $('.ct-tooltip').html('<span>' + seriesDesc + '</span><br><span>' + value + " " + pages + "</span>");
            });

            $(document).on('click', '.languages button', function() {
                _this.getAllData();
            });
        },
        methods: {
            ...mapActions({
     	 	    setCurrentTeam: "setCurrentTeam"
            }),

            getAllData(isFilter = 0) {

                this.exportLink = '/data/statistic/export-report/xlsx?user_id=' + this.user_id + '&startMonth=' + this.customFormatterStr(this.startMonth) + '&endMonth=' + this.customFormatterEnd(this.endMonth) + '&team_id=' + this.team;

                const uri = '/data/statistic/time-allocation?user_id=' + this.user_id + '&startMonth=' + this.customFormatterStr(this.startMonth) + '&endMonth=' + this.customFormatterEnd(this.endMonth) + '&team_id=' + this.team + '&isFilter=' + isFilter;

                axios.get(uri)
                    .then(res => {
                        if ( ! isFilter ) {
                            // Reset
                            this.pageData = {
                                'totalpage' : [],
                                'table' : [],
                            };
                            this.jobsData = [];
                            this.projectsData = [];

                            // Assign data one time
                            this.types = res.data.types;
                            this.users = res.data.users.all;
                            this.jobs = res.data.jobs;
                        }

                         // Assign data
                        this.newUsersPerMonth = res.data.users.newUsersPerMonth;  
                        this.totalHoursPerMonth = res.data.totals.hoursPerMonth;
                        this.hoursPerProject = res.data.totals.hoursPerProject;
                        this.currentMonth = res.data.currentMonth;
                        this.currentMonth.startDate = moment().startOf('month').format('YYYY/MM/DD');
                        this.currentMonth.currentDate = moment().format('YYYY/MM/DD');
                        this.monthsText = this.pageChart.data.labels = this.jobChart.data.labels = this.projectChart.data.labels = this.barChart.data.labels = res.data.monthsText;
                        this.getSeries(this.types, this.totalHoursPerMonth, this.hoursPerProject);
                    })
                    .catch(err => {
                        console.log(err);
                        alert("Could not load data");
                    });

            },

            getPageData(url){
                axios.get(url)
                .then(res => {
                    this.pageData = res.data;
                    this.getCustomSeries(this.types,this.pageData.totalpage,'pageChart','page',this.totalHoursPerMonth)
                    this.data.totalpage = this.pageData;
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not load data");
                });
            },

            showTotalPages() {
                if ( ! this.pageData.totalpage.length ) {
                    const uriPage = '/data/statistic/get-page-report?user_id=' + this.user_id + '&startMonth=' + this.customFormatterStr(this.startMonth) + '&endMonth=' + this.customFormatterEnd(this.endMonth) + '&team_id=' + this.team;
                    this.getPageData(uriPage);
                }
            },

            getJobData(url){
                axios.get(url)
                .then(res => {
                    this.jobsData = res.data;
                    this.getCustomSeries(this.types,this.jobsData.totaljob,'jobChart','issue',this.totalHoursPerMonth);
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not load data");
                });
            },

            showTotalJobs() {
                if ( ! this.jobsData.totaljob ) {
                    const uriJobs = '/data/statistic/get-job-report?user_id=' + this.user_id + '&startMonth=' + this.customFormatterStr(this.startMonth) + '&endMonth=' + this.customFormatterEnd(this.endMonth) + '&team_id=' + this.team;
                    this.getJobData(uriJobs);
                }
            },

            getProjectData(url){
                axios.get(url)
                .then(res => {
                    this.projectsData = res.data;
                    this.getCustomSeries(this.types,this.projectsData.totalproject,'projectChart','project',this.totalHoursPerMonth);
                })
                .catch(err => {
                    console.log(err);
                    alert("Could not load data");
                });
            },

            showTotalProjects() {
                if ( ! this.projectsData.totalproject ) {
                    const uriProject = '/data/statistic/get-project-report?user_id=' + this.user_id + '&startMonth=' + this.customFormatterStr(this.startMonth)
                    this.getProjectData(uriProject);
                }
            },

            hasObjectValue(data, id, yearMonth) {
                let obj = data.filter((elem) => { if (typeof(elem) !== 'undefined' && elem.id == id && elem.yearMonth == yearMonth) return elem; });
                if (obj.length > 0) return obj[0];
                return false;
            },
            customFormatterEnd(date) {
                // return moment(date).endOf('month').format('YYYY/MM/DD');
                return moment(date).format('YYYY/MM/DD');
            },
            customFormatterStr(date) {
                // return moment(date).startOf('month').format('YYYY/MM/DD');
                return moment(date).format('YYYY/MM/DD')
            },
            customFormatterM(date) {
                return moment(date).format('YYYY/MM/DD');
            },
            circleClass(cl) {
                return cl + ' text-uppercase ml-3';
            },
            totalObject(obj) {
                return Object.keys(obj).reduce((total, key) => { return total + obj[key] }, 0);
            },
            totalArrayObject(arr) {
                return arr.reduce((total, item) => { return total + (item.total*1).toFixed(2)*1 }, 0).toFixed(2);
            },
            getCurrentMonth(data) {
                if (typeof(data.hours) !== 'undefined') return {
                    hours: (data.hours[0].total*1).toFixed(2),
                    total:  data.total
                };
                return false;
            },
            getSeries(projectTypes, totalHoursPerMonth, hoursPerProject) {
                let _this = this;
                let series = projectTypes.map((item, index) => {
                    let _item = item;
                    let row = Object.keys(totalHoursPerMonth).map((key, index) => {
                        if ( _this.hasObjectValue(hoursPerProject, _item.id, key) ) {
                            let percents = (_this.hasObjectValue(hoursPerProject, _item.id, key).total*1/totalHoursPerMonth[key]*100).toFixed(2);
                            return {
                                value: percents,
                                meta: _item.slug
                            };
                        } else {
                            return {
                                value: 0,
                                meta: _item.slug
                            };
                        }
                    })
                    return row;
                });
                this.series = this.barChart.data.series = [...series];
            },

            getCustomSeries(type, data, chart, dataKey, totalHoursPerMonth){
                let _this = this;
                let series = type.map((item, index) => {
                    let _item = item;
                    let row = Object.keys(totalHoursPerMonth).map((key, index) => {
                        if ( _this.hasObjectValue(data, _item.id, key) ) {
                            return {
                                value: _this.hasObjectValue(data, _item.id, key)[dataKey],
                                meta: _item.slug
                            };
                        } else {
                            return {
                                value: 0,
                                meta: _item.slug
                            };
                        }
                    })
                    return row;
                });
                this[chart].data.series = series;
            },
            dateFormatter(date) {
                return moment(date).format('YYYY-MM-DD');
            },
            disabledEndMonth() {
                let obj = {
                    from: this.endMonth,
                };
                return obj;
            },
            disabledStartMonth() {
                let obj = {
                    to: this.startMonth,
                    from: new Date(),
                };
                return obj;
            },
            getUserOptions(data) {
                if (data.length) {
                    let obj = {
                        id: 0,
                        text: this.$ml.with('VueJS').get('txtSelectAll')
                    };

                    const userActive = data.filter((item) => {
                        return item.disable_date === null;
                    });

                    this.userOptions = [obj].concat(userActive);
                }
            },
            getLanguage(data) {
                return this.dataLang[data.current]
            },
            chartLoaded(chartID) {
                const types = this.types;
                $('.ct-chart, .card-footer .legend').addClass('loading');
                if ( types.length ) {
                    setTimeout(function(){
                        types.forEach(function(item, index) {
                            $(chartID).closest('.card').find('.' + item.class).each(function(){
                                $(this).find('.ct-legend').css('color', item.value);
                                $(this).find('.ct-point, .ct-line, .ct-bar, .ct-slice-donut').css('stroke', item.value);
                            })
                        });
                        $('.ct-chart, .card-footer .legend').removeClass('loading');
                    }, 1000);
                }
            },
            checkUser(){
                if(-1 != ('1,24,49').indexOf(this.loginUser.id)) return true;
                return false;
            },

            dateChange(value, oldValue) {
                if ( value != oldValue ) {
                    const _this = this;
                    _this.getAllData(1);

                    // Reset
                    _this.pageData = {
                        'totalpage' : [],
                        'table' : [],
                    };
                    _this.jobsData = [];
                    _this.projectsData = [];

                    if ( $('#nav-tab > a#table').hasClass('active') ) _this.showTotalPages();
                    if ( $('#nav-tab > a#totalpage-tab').hasClass('active') ) _this.showTotalPages();
                    if ( $('#nav-tab > a#totalproject-tab').hasClass('active') ) _this.showTotalProjects();
                    if ( $('#nav-tab > a#totaljobs-tab').hasClass('active') ) _this.showTotalJobs();
                }
            }
        },
        watch: {
            users: [{
                handler: 'getUserOptions'
            }],
            user_id: [{
                handler: function() {
                    this.getAllData(1);
                }
            }],
            startMonth: [{
                handler: 'dateChange'
            }],
            endMonth: [{
                handler: 'dateChange'
            }],
            team: [{
                handler: function(value, oldValue) {
                    if ( value != oldValue ) {
                        this.getAllData();
                        if ( value != this.currentTeam.id ) {
                            this.setCurrentTeam(value);
                            $('#nav-tab > a').not('#timeallocation-tab').removeClass('active').attr('aria-selected',false);
                            $('#timeallocation-tab').addClass('active').attr('aria-selected',true);
                            $('#nav-tabContent > div').not('#timeallocation').removeClass('active').removeClass('show');
                            $('#timeallocation').addClass('active').addClass('show');
                        }
                    }
                }
            }]
        }
    }
</script>
<style lang="scss">
$chart-tooltip-bg: rgba(40, 40, 40, 0.75) ;
$chart-tooltip-color: #fff;
#nav-tabContent{
    overflow: hidden;
}
.tab-content{
    > .tab-pane{
        display: block;
        height: 0;
    }
    > .active {
        height: auto;
    }
}
.ct-tooltip {
    position: absolute;
    margin-top: 150px;
    display: inline-block;
    opacity: 0.75;
    min-width: 130px;
    padding: 5px 10px;
    border-radius: 5px;
    background: $chart-tooltip-bg;
    color: $chart-tooltip-color;
    font-family: Roboto, Helvetica Neue, Arial, sans-serif;
    font-weight: 500;
    font-size: 12px;
    text-align: center;
    pointer-events: none;
    z-index: 1;
    -webkit-transition: opacity .2s linear;
    -moz-transition: opacity .2s linear;
    -o-transition: opacity .2s linear;
    transition: opacity .2s linear;
    text-transform: uppercase;

    #page-chart & {
        margin-top: 100px;
    }

    &:before {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        width: 0;
        height: 0;
        margin-left: -7px;
        border: 7px solid transparent;
        border-top-color: $chart-tooltip-bg;
    }
    span:last-child {
        font-size: 14px;
    }
}
#table{
    position: relative;
    &.flag{
        padding-top: 60px;
    }
    button[data-toggle="modal"]{
        position: absolute;
        top: 15px;
        right: 0;
    }
}
.ct-area, .ct-line {
    pointer-events: none;
}
.tab-content .card {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    border-top-color: transparent;
}
.table-bordered th, .table-bordered td{
  border-bottom: 1px solid #f1eded !important;
}
</style>