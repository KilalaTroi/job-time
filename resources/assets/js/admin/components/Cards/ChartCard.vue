<template>
    <div class="card">
        <div class="card-header" v-if="$slots.header">
            <slot name="header"></slot>
        </div>
        <div class="card-body">
            <div :id="chartId" class="ct-chart"></div>
        </div>
        <div class="card-footer" v-if="$slots.footer">
            <slot name="footer"></slot>
        </div>
    </div>
</template>
<script>
import Chartist from 'chartist'
import Card from './Card.vue'

export default {
    name: 'chart-card',
    components: {
        Card
    },
    props: {
        chartId: {
            type: String,
            default: 'no-id'
        },
        chartType: {
            type: String,
            default: 'Line' // Line | Pie | Bar
        },
        chartOptions: {
            type: Object,
            default: () => {
                return {}
            }
        },
        chartData: {
            type: Object,
            default: () => {
                return {
                    labels: [],
                    series: []
                }
            }
        },
        responsiveOptions: [Object, Array]
    },
    data() {
        return {
            $Chartist: null,
            chart: null
        }
    },
    mounted() {
        const _this = this;
        _this.$Chartist = Chartist.default || Chartist;
        _this.initChart();
        $(window).resize(function () {
            _this.chartUpdate();
        });
    },
    methods: {
        /***
         * Initializes the chart by merging the chart options sent via props and the default chart options
         */
        initChart() {
            this.chart = this.$Chartist[this.chartType]('#' + this.chartId, this.chartData, this.chartOptions, this.responsiveOptions);
            this.$emit('initialized', this.chart);
            if (this.chartType === 'Line') {
                this.animateLineChart()
            }
            if (this.chartType === 'Bar') {
                this.animateBarChart()
            }
        },
        animateLineChart() {
            let seq = 0;
            let durations = 500;
            let delays = 80;
            this.chart.on('draw', (data) => {
                if (data.type === 'line' || data.type === 'area') {
                    data.element.animate({
                        d: {
                            begin: 600,
                            dur: 700,
                            from: data.path.clone().scale(1, 0).translate(0, data.chartRect.height()).stringify(),
                            to: data.path.clone().stringify(),
                            easing: this.$Chartist.Svg.Easing.easeOutQuint
                        }
                    })
                } else if (data.type === 'point') {
                    seq++;
                    data.element.animate({
                        opacity: {
                            begin: seq * delays,
                            dur: durations,
                            from: 0,
                            to: 1,
                            easing: 'ease'
                        }
                    })
                }
            });
            seq = 0
        },
        animateBarChart() {
            let seq = 0;
            let durations = 300;
            let delays = 40;
            this.chart.on('draw', (data) => {
                if (data.type === 'bar') {
                    seq++;
                    data.element.animate({
                        opacity: {
                            begin: seq * delays,
                            dur: durations,
                            from: 0,
                            to: 1,
                            easing: 'ease'
                        }
                    })
                }
            })
        },
        chartUpdate() {
            this.chart = this.$Chartist[this.chartType]('#' + this.chartId, this.chartData, this.chartOptions, this.responsiveOptions);
            this.$emit('chart-loaded', '#' + this.chartId);
        }
    },
    watch: {
        chartData: [{
            handler: 'chartUpdate',
            deep: true
        }]
    }
}
</script>
<style>
</style>