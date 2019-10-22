<template>
  <select>
    <slot></slot>
  </select>
</template>
<script>
    export default {
        name: 'select2-type',
        props: ['options', 'value'],
        mounted: function () {
            var vm = this;
            $(this.$el)
            // init select2
                .select2({
                    data: this.options,
                    templateResult: function (d) { return $(d.text); },
                    templateSelection: function (d) { return $(d.text); }
                })
                .val(this.value)
                .trigger('change')
                // emit event on change.
                .on('change', function () {
                    vm.$emit('input', this.value)
                })
        },
        watch: {
            value: function (value) {
                // update value
                $(this.$el)
                    .val(value)
                    .trigger('change')
            },
            options: function (options) {
                // update options
                $(this.$el).empty().select2({
                    data: options,
                    templateResult: function (d) { return $(d.text); },
                    templateSelection: function (d) { return $(d.text); }
                });
                $('.select2 option[value="0"]').prop('disabled',true);
            }
        },
        destroyed: function () {
            $(this.$el).off().select2('destroy')
        }
    }
</script>
<style lang="scss">
  .select2-selection__rendered, .select2-results__option {
    .type-color {
      vertical-align: middle;
      margin-right: 5px;
      margin-top: -3px;
      width: 20px;
    }
  }
</style>