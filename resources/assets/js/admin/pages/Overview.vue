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
                            <p class="card-category">Time Worked</p>
                            <h4 class="card-title">{{ totalArrayObject(hoursPerProject) }}/{{ totalObject(totalHoursPerMonth) }} hrs</h4>
                        </div>
                        <div slot="footer">
                            <i class="fa fa-calendar-o mr-1"></i>{{ getTimeWorked(startEndYear) }}
                        </div>
                    </stats-card>
                </div>
                <div class="col-xl-3 col-md-6">
                    <stats-card>
                        <div slot="header" class="icon-success">
                            <i class="nc-icon nc-light-3 text-success"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">Time Working</p>
                            <h4 class="card-title"> {{ getCurrentMonth(currentMonth) }} hrs</h4>
                        </div>
                        <div slot="footer">
                            <i class="fa fa-calendar-o mr-1"></i>{{ customFormatter(startEndYear[1]) }} - {{ currentFormatterDate() }}
                        </div>
                    </stats-card>
                </div>
                <div class="col-xl-3 col-md-6">
                    <stats-card>
                        <div slot="header" class="icon-danger">
                            <i class="nc-icon nc-vector text-danger"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">Current Jobs</p>
                            <h4 class="card-title">{{ jobs }}</h4>
                        </div>
                        <div slot="footer">
                            <i class="fa fa-calendar-o mr-1"></i>{{ currentFormatterDate() }}
                        </div>
                    </stats-card>
                </div>
                <div class="col-xl-3 col-md-6">
                    <stats-card>
                        <div slot="header" class="icon-info">
                            <i class="nc-icon nc-circle-09 text-primary"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">New Users</p>
                            <h4 class="card-title">+{{ totalObject(newUsersPerMonth) }}</h4>
                        </div>
                        <div slot="footer">
                            <i class="fa fa-calendar-o mr-1"></i>{{ getTimeWorked(startEndYear) }}
                        </div>
                    </stats-card>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <chart-card :chart-data="barChart.data" :chart-options="barChart.options" :chart-responsive-options="barChart.responsiveOptions" chart-type="Bar" :chart-id="barChart.id">
                        <template slot="header">
                            <h4 class="card-title">Kilala VN Time allocation</h4>
                            <p class="card-category">{{ getTimeWorked(startEndYear) }}</p>
                        </template>
                        <template slot="footer">
                            <div class="legend">
                                <span v-for="(type, index) in types" :key="index" :class="circleClass(type.class)"><i class="fa fa-circle ct-legend"></i> {{ type.slug }}</span>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="export col-auto ml-auto">
                                    <a :href="exportLink" target="_blank"><i class="fa fa-download"></i> Export excel</a>
                                </div>
                            </div>
                        </template>
                    </chart-card>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import ChartCard from '../components/Cards/ChartCard.vue'
    import StatsCard from '../components/Cards/StatsCard.vue'
    import LTable from '../components/Table.vue'
    import Chartist from 'chartist'
    import chartistPluginTooltip from 'chartist-plugin-tooltip'
    import moment from 'moment'

    export default {
        components: {
            LTable,
            ChartCard,
            StatsCard
        },
        data() {
            return {
                year: new Date().getFullYear(),
                exportLink: '/data/export-report/'+ new Date().getFullYear() +'/xlsx',
                types: [],
                monthsText: [],
                series: [],
                startEndYear: [],
                newUsersPerMonth: {},
                totalHoursPerMonth: {},
                hoursPerProject: [],
                jobs: 0,
                currentMonth: {},

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
                            labelInterpolationFnc: function(value) {
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
                }
            }
        },
        mounted() {
            this.fetch();
            $(document).on('mouseenter', '.ct-bar', function() {
                var seriesDesc = $(this).attr('ct:meta'),
                value = $(this).attr('ct:value');
                $('.ct-tooltip').html('<span>' + seriesDesc + '</span><br><span>' + value + "%</span>");
            });
        },
        methods: {
            fetch() {
                let uri = '/data/statistic/time-allocation';
                axios.get(uri)
                    .then(res => {
                        this.types = res.data.types;
                        this.startEndYear = res.data.startEndYear;
                        this.newUsersPerMonth = res.data.newUsersPerMonth;
                        this.totalHoursPerMonth = res.data.totalHoursPerMonth;
                        this.hoursPerProject = res.data.hoursPerProject;
                        this.jobs = res.data.jobs;
                        this.currentMonth = res.data.currentMonth;
                        this.monthsText = this.barChart.data.labels = res.data.monthsText;
                        this.getSeries(this.types, this.totalHoursPerMonth, this.hoursPerProject);
                    })
                    .catch(err => {
                        console.log(err);
                        alert("Could not load data");
                    });
            },
            hasObjectValue(data, id, yearMonth) {
                let obj = data.filter(function(elem) {
                    if (typeof(elem) !== 'undefined' && elem.id == id && elem.yearMonth == yearMonth) return elem;
                });

                if (obj.length > 0)
                    return obj[0];

                return false;
            },
            currentFormatterDate() {
                return moment().format('YYYY/MM/DD');
            },
            customFormatter(date) {
                return moment(date).format('YYYY/MM/DD');
            },
            yesterday(date) {
                date = new Date(date);
                return date.setDate(date.getDate() - 1);
            },
            circleClass(cl) {
                return cl + ' text-uppercase ml-3';
            },
            getTimeWorked(date) {
                return this.customFormatter(date[0]) + ' - ' + this.customFormatter(this.yesterday(date[1]));
            },
            totalObject(obj) {
                let total = [];
                Object.entries(obj).forEach(([key, val]) => {
                    total.push(val)
                });
                return total.reduce(function(total, num){ return total + num }, 0);
            },
            totalArrayObject(arr) {
                let total = [];
                arr.map(function(value, key) {
                    total.push(Math.round(value.total))
                });
                return total.reduce(function(total, num){ return total + num }, 0);
            },
            getCurrentMonth(data) {
                if (typeof(data.hours) !== 'undefined') return Math.round(data.hours[0].total) + '/' + data.total;
            },
            getSeries(projectTypes, totalHoursPerMonth, hoursPerProject) {
                let series = [];
                let _this = this;
                projectTypes.map(function(value, key) {
                    let id = value.id;
                    let row = [];
                    Object.entries(totalHoursPerMonth).forEach(([key, val]) => {
                        if ( _this.hasObjectValue(hoursPerProject, id, key) ) {
                            let percents = (Math.round(_this.hasObjectValue(hoursPerProject, id, key).total)/val*100).toFixed(2);
                            row.push({
                                value: percents,
                                meta: value.slug
                            });
                        } else {
                            row.push({
                                value: 0,
                                meta: value.slug
                            });
                        }
                    });
                    series.push(row);
                });
                this.series = this.barChart.data.series = series;
            }
        }
    }
</script>
<style lang="scss">
$chart-tooltip-bg: #F4C63D;
$chart-tooltip-color: #453D3F;
.ct-tooltip {
    position: absolute;
    margin-top: 100px;
    display: inline-block;
    opacity: 1;
    min-width: 5em;
    padding: .5em;
    background: $chart-tooltip-bg;
    color: $chart-tooltip-color;
    font-family: Oxygen,Helvetica,Arial,sans-serif;
    font-weight: 700;
    text-align: center;
    pointer-events: none;
    z-index: 1;
    -webkit-transition: opacity .2s linear;
    -moz-transition: opacity .2s linear;
    -o-transition: opacity .2s linear;
    transition: opacity .2s linear;
    text-transform: uppercase;

    &:before {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        width: 0;
        height: 0;
        margin-left: -15px;
        border: 15px solid transparent;
        border-top-color: $chart-tooltip-bg;
    }
    &.tooltip-show {
        opacity: 1;
    }
}
.ct-area, .ct-line {
    pointer-events: none;
}
</style>