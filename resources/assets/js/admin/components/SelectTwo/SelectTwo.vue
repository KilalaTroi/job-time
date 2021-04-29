<template>
  <select>
    <slot></slot>
  </select>
</template>

<script>
export default {
  name: "select-2",
  props: ["options", "value", "allowClear", "placeholder"],
  data() {
    return {
      params: {},
    };
  },
  mounted: function () {
    const vm = this;
    vm.params.data = this.options;
    if (vm.allowClear){
      vm.params.placeholder = '';
      vm.params.allowClear = vm.allowClear;
      if (vm.placeholder) vm.params.placeholder = vm.placeholder;
    }

    $(this.$el).select2(this.params).val(this.value).trigger("change").on("change", function () {
      vm.$emit("input", this.value);
    });
  },
  watch: {
    value: function (newValue, oldValue) {
      if (!(newValue == oldValue) && (newValue || newValue >= 0))
        $(this.$el).val(newValue).trigger("change");
    },
    options: function (options) {
      const vm = this
      vm.params.data = options;
      // update options
      $(this.$el).empty().select2(this.params);
      if (!$(".select2.no-disable-first-value").length)
        $('.select2 option[value="0"]').prop("disabled", true);
    },
  },
  destroyed: function () {
    $(this.$el).off().select2("destroy");
  },
};
</script>

<style lang="scss">
.select2-container {
  width: 100% !important;
}
.select2-container .select2-selection--single {
  height: 40px;
}
.select2-container--default
  .select2-selection--single
  .select2-selection__rendered {
  line-height: 40px;
}
.select2-container--default
  .select2-selection--single
  .select2-selection__arrow {
  height: 38px;
}
</style>