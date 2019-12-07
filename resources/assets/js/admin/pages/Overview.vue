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
                            <p class="card-category">Capacity</p>
                            <h4 class="card-title">105GB</h4>
                        </div>
                        <div slot="footer">
                            <i class="fa fa-refresh"></i>Updated now
                        </div>
                    </stats-card>
                </div>
                <div class="col-xl-3 col-md-6">
                    <stats-card>
                        <div slot="header" class="icon-success">
                            <i class="nc-icon nc-light-3 text-success"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">Revenue</p>
                            <h4 class="card-title">$1,345</h4>
                        </div>
                        <div slot="footer">
                            <i class="fa fa-calendar-o"></i>Last day
                        </div>
                    </stats-card>
                </div>
                <div class="col-xl-3 col-md-6">
                    <stats-card>
                        <div slot="header" class="icon-danger">
                            <i class="nc-icon nc-vector text-danger"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">Errors</p>
                            <h4 class="card-title">23</h4>
                        </div>
                        <div slot="footer">
                            <i class="fa fa-clock-o"></i>Last day
                        </div>
                    </stats-card>
                </div>
                <div class="col-xl-3 col-md-6">
                    <stats-card>
                        <div slot="header" class="icon-info">
                            <i class="nc-icon nc-favourite-28 text-primary"></i>
                        </div>
                        <div slot="content">
                            <p class="card-category">Followers</p>
                            <h4 class="card-title">+45</h4>
                        </div>
                        <div slot="footer">
                            <i class="fa fa-refresh"></i>Updated now
                        </div>
                    </stats-card>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <chart-card :chart-data="barChart.data" :chart-options="barChart.options" :chart-responsive-options="barChart.responsiveOptions" chart-type="Bar" :chart-id="barChart.id">
                        <template slot="header">
                            <h4 class="card-title">Kilala VN Time allocation</h4>
                            <p class="card-category">{{ customFormatter(startEndDate[0]) }} - {{ customFormatter(yesterday(startEndDate[1])) }}</p>
                        </template>
                        <template slot="footer">
                            <div class="legend">
                                <span v-for="(type, index) in types" :key="index" :class="circleClass(type.class)"><i class="fa fa-circle ct-legend"></i> {{ type.slug }}</span>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="export col-auto ml-auto">
                                    <a :href="exportLink" target="_blank"><i class="fa fa-download"></i> Export</a>
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
                startEndDate: [],
                exportLink: '/data/export-report/'+ new Date().getFullYear() +'/xlsx',
                types: [],
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
                        height: '360px'
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
        },
        methods: {
            fetch() {
                let uri = '/data/statistic/time-allocation';
                axios.get(uri)
                    .then(res => {
                        this.barChart.data.labels = res.data.monthsText;
                        this.barChart.data.series = [
                            [5, 4, 3, 7, 3, 2, 9, 5, 1, 5, 8, 4],
                            [3, 2, 9, 5, 1, 5, 8, 4, 2, 3, 4, 6],
                            [1, 5, 8, 4, 2, 3, 4, 6, 4, 1, 2, 1],
                            [2, 3, 4, 6, 4, 1, 2, 1, 5, 4, 3, 7],
                            [4, 1, 2, 1, 5, 4, 3, 7, 3, 2, 9, 5]
                        ];
                        this.types = res.data.types;
                        this.startEndDate = res.data.startEndYear;
                    })
                    .catch(err => {
                        console.log(err);
                        alert("Could not load data");
                    });
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
            }
        }
    }
</script>
<style>
</style>