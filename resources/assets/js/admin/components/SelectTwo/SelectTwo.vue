<template>
  <select>
    <slot></slot>
  </select>
</template>
<script>
    export default {
        name: 'select-2',
        props: ['options', 'value'],
        mounted: function () {
            var vm = this;
            $(this.$el)
            // init select2
                .select2({ data: this.options })
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
                $(this.$el).empty().select2({ data: options });
                $('.select2 option[value="0"]').prop('disabled',true);
            }
        },
        destroyed: function () {
            $(this.$el).off().select2('destroy')
        }
    }
</script>
<style lang="scss">
  .select2-container {
    width: 100%!important;
  }
  .select2-container .select2-selection--single {
    height: 40px;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 40px;
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px;
  }
</style>