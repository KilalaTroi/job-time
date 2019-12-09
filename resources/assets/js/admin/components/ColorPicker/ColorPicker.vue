<template>
    <div>
        <div class="input-group color-picker" ref="colorpicker">
            <span class="input-group-append color-picker-container rounded-left overflow-hidden">
                <span class="current-color" :style="'background-color: ' + colorValue"></span>
            </span>
            <input type="text" class="form-control" v-model="colorValue" @focus="showPicker()" @input="updateFromInput"/>
        </div>
        <compact :value="colors" class="w-100 mt-2" @input="updateFromPicker" v-if="displayPicker"/>
    </div>
</template>
<script>
    import { Compact } from 'vue-color'

    export default {
        name: 'color-picker',
        components: {
            'compact': Compact
        },
        props: ['color'],
        data() {
            return {
                colors: {
                    hex: '#000000',
                },
                colorValue: '',
                displayPicker: true,
            }
        },
        mounted() {
            this.setColor(this.color || '#000000');
        },
        methods: {
            setColor(color) {
                this.updateColors(color);
                this.colorValue = color;
            },
            updateColors(color) {
                if(color.slice(0, 1) == '#') {
                    this.colors = {
                        hex: color
                    };
                }
                else if(color.slice(0, 4) == 'rgba') {
                    var rgba = color.replace(/^rgba?\(|\s+|\)$/g,'').split(','),
                        hex = '#' + ((1 << 24) + (parseInt(rgba[0]) << 16) + (parseInt(rgba[1]) << 8) + parseInt(rgba[2])).toString(16).slice(1);
                    this.colors = {
                        hex: hex,
                        a: rgba[3],
                    }
                }
            },
            updateFromInput() {
                this.updateColors(this.colorValue);
            },
            updateFromPicker(color) {
                this.colors = color;
                if(color.rgba.a == 1) {
                    this.colorValue = color.hex;
                }
                else {
                    this.colorValue = 'rgba(' + color.rgba.r + ', ' + color.rgba.g + ', ' + color.rgba.b + ', ' + color.rgba.a + ')';
                }
            },
        },
        watch: {
            colorValue(val) {
                if(val) {
                    this.updateColors(val);
                    this.$emit('input', val);
                }
            },
            color: function(newVal, oldVal) { // watch it
                this.setColor(newVal);
            }
        },
    }
</script>
<style lang="scss">
    .vc-chrome {
        position: absolute;
        top: 35px;
        right: 0;
        z-index: 9;
    }
    .current-color {
        display: inline-block;
        width: 40px;
        height: 40px;
        background-color: #000;
    }
</style>