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
                            <h4 class="card-title">{{ year }} Working Time</h4>
                            <p class="card-category">Kilala VN Time allocation</p>
                        </template>
                        <template slot="footer">
                            <div class="legend">
                                <span class="ct-series-a"><i class="fa fa-circle ct-legend"></i> Project Type 1</span>
                                <span class="ct-series-b"><i class="fa fa-circle ct-legend"></i> Project Type 2</span>
                                <span class="ct-series-c"><i class="fa fa-circle ct-legend"></i> Project Type 3</span>
                                <span class="ct-series-d"><i class="fa fa-circle ct-legend"></i> Project Type 4</span>
                                <span class="ct-series-e"><i class="fa fa-circle ct-legend"></i> Project Type 5</span>
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
                barChart: {
                    id: 'time-allocation',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        series: [
                            [5, 4, 3, 7, 3, 2, 9, 5, 1, 5, 8, 4],
                            [3, 2, 9, 5, 1, 5, 8, 4, 2, 3, 4, 6],
                            [1, 5, 8, 4, 2, 3, 4, 6, 4, 1, 2, 1],
                            [2, 3, 4, 6, 4, 1, 2, 1, 5, 4, 3, 7],
                            [4, 1, 2, 1, 5, 4, 3, 7, 3, 2, 9, 5]
                        ]
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
        }
    }
</script>
<style>
</style>